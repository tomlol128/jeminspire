<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\User;
use App\Models\Commande;
use Illuminate\Support\Facades\Log;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class CommandeController extends Controller
{
    private function typeToInt(string $type): int {
        return $type === 'subscription' ? 2 : 1;
    }

    public function checkout(Request $request)
    {
       \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $poste = Post::findOrFail($request['id']);
        $type = $request->get('type', 'one_time'); // 'one_time' ou 'subscription'
        $vendorId = $poste->id_user ?? '';
        $commissionPercent = 10; // 10% pour paiement unique seulement

        $total = 0; // variable pour stocker le total final

        if ($type === 'subscription') {
            $priceId = env('PRO_PLUS_ID_STRIPE');
            Log::info("priceId = $priceId");

            if (!$priceId) abort(404, "Plan Stripe non défini");

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'mode' => 'subscription',
                'line_items' => [[
                    'price' => $priceId,
                    'quantity' => 1,
                ]],
                'success_url' => route('success', [], true) . "?session_id={CHECKOUT_SESSION_ID}",
                'cancel_url'  => route('cancel', [], true),
            ]);

            // Pour un abonnement, on ne connait pas encore le subscription_id, il faudra le mettre via webhook
            $total = 0; // ou récupère le prix depuis l'objet Price si tu veux stocker le montant de l'abonnement
            $commission = 0;

        } else {
            $amountCents = intval(round($poste->prix * 100));
            $applicationFee = intval($amountCents * ($commissionPercent / 100));
            $vendeur = User::find($poste->id_user);

            $lineItems = [[
                'price_data' => [
                    'currency' => 'cad',
                    'product_data' => ['name' => $poste->titre],
                    'unit_amount' => $amountCents,
                ],
                'quantity' => 1,
            ]];

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'payment_intent_data' => [
                    'application_fee_amount' => $applicationFee,
                    'transfer_data' => [
                        'destination' => $vendeur->stripeAccount_id, // compte du vendeur
                    ],
                ],
                'success_url' => route('success', [], true) . "?session_id={CHECKOUT_SESSION_ID}",
                'cancel_url'  => route('cancel', [], true),
            ]);

            $total = $poste->prix;
            $commission = $applicationFee;
        }

        // Création de la commande en base
        $commande = Commande::create([
            'status'                 => 'unpaid',
            'total'                  => $total,
            'session_id'             => $session->id ?? '',
            'stripe_id'              => $request->user()->stripe_id ?? '',
            'stripe_subscription_id' => '', // laisser vide, sera mis à jour via webhook
            'paypal_id'              => '',
            'user_id'                => $request->user()->id ?? '',
            'vendor_id'              => $vendorId,
            'id_type'                => $type === 'subscription' ? 2 : 1,
            'commission'             => $commission,
        ]);

        return redirect($session->url);

    }

   public function success(Request $request)
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            return redirect()->route('profile.edit')->with('error', 'Session Stripe manquante');
        }

        $commande = Commande::where('session_id', $sessionId)->first();

        if (!$commande) {
            return redirect()->route('profile.edit')->with('error', 'Commande introuvable');
        }

        return view('commande.success', compact('commande'));
    }

    public function cancel()
    {
        return view('commande.cancel');
    }


   public function webhook()
    {
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\UnexpectedValueException $e) {
            // Payload invalide
            return response('', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Signature invalide
            return response('', 400);
        }

        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;

                $commande = Commande::where('session_id', $session->id)->first();

                if ($commande) {
                    // Pour paiement unique
                    if ($commande->id_type == 1) {
                        $amount = ($session->amount_total ?? 0) / 100; // convertir en dollars
                        $commande->update([
                            'status' => 'paid',
                            'total'  => $amount,
                        ]);
                    }

                    // Pour abonnement
                    if ($commande->id_type == 2) {
                        $subscriptionId = $session->subscription ?? null;
                        $commande->update([
                            'status' => 'paid', // ou 'active' si tu préfères attendre invoice.paid
                            'stripe_subscription_id' => $subscriptionId,
                            'total' => $session->amount_total ? $session->amount_total / 100 : 0,
                        ]);
                    }
                }
                Log::info("Webhook checkout.session.completed traité pour session {$session->id}");
                break;

            case 'invoice.paid':
                $invoice = $event->data->object;
                $subscriptionId = $invoice->subscription;

                $commande = Commande::where('stripe_subscription_id', $subscriptionId)->first();
                if ($commande) {
                    $amount = ($invoice->amount_paid ?? 0) / 100;
                    $commande->update([
                        'status' => 'paid',
                        'total'  => $amount,
                    ]);
                }
                Log::info("Webhook invoice.paid traité pour subscription $subscriptionId");
                break;

            case 'invoice.payment_failed':
                $invoice = $event->data->object;
                $subscriptionId = $invoice->subscription;

                $commande = Commande::where('stripe_subscription_id', $subscriptionId)->first();
                if ($commande) {
                    $commande->update(['status' => 'failed']);
                }
                Log::warning("Webhook invoice.payment_failed pour subscription $subscriptionId");
                break;

            default:
                Log::warning("Événement Stripe non géré : " . $event->type);
                break;
        }

        return response('Webhook reçu', 200);
    }


    public function paypalCheckout(Request $request)
    {
        $paypal = new PayPalClient;
        $paypal->setApiCredentials(config('paypal'));
        $token = $paypal->getAccessToken();
        $paypal->setAccessToken($token);

        $poste = Post::findOrFail($request['id']);
        $amount = $poste->prix ?? 0;

        $commande = Commande::create([
            'status'                 => 'pending',
            'total'                  => $amount,
            'session_id'             => '',
            'stripe_id'              => '',
            'stripe_subscription_id' => '',
            'paypal_id'              => '',
            'user_id'                => $request->user()->id ?? '',
            'vendor_id'              => $poste->id_user ?? '',
            'id_type'                   => $this->typeToInt("one_time"),
            'commission'             => 0,
        ]);

        $response = $paypal->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('paypalSuccess', ['commande' => $commande->id]),
                "cancel_url" => route('paypalCancel', ['commande' => $commande->id]),
            ],
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "CAD",
                        "value" => number_format($amount, 2, '.', '')
                    ]
                ]
            ]
        ]);

        if (isset($response['id'])) {
            $commande->update(['paypal_id' => $response['id']]);
        }

        foreach ($response['links'] ?? [] as $link) {
            if ($link['rel'] === 'approve') {
                return redirect()->away($link['href']);
            }
        }

        return redirect()->route('paypalCancel', ['commande' => $commande->id]);
    }

    public function paypalSuccess(Request $request)
    {
        $commande = Commande::find($request->commande);

        if (!$commande) {
            return redirect()->route('profile.edit')->with('error', 'Commande introuvable');
        }

        return view('commande.paypal.success', compact('commande'));
    }

    public function paypalCancel(Request $request)
    {
        $commande = Commande::find($request->commande);

        if (!$commande) {
            return redirect()->route('profile.edit')->with('error', 'Commande introuvable');
        }

        return view('commande.paypal.cancel', compact('commande'));
    }

    public function paypalWebhook(Request $request)
    {
        $payload = $request->all();
        Log::info("Webhook reçu : " . json_encode($payload));

        response()->json(['status' => 'success'], 200)->send();



        $event = $payload['event_type'] ?? null;
        $paypalId = $payload['resource']['id'] ?? null;

        switch ($event) {
            case 'CHECKOUT.ORDER.APPROVED':
                Commande::where('paypal_id', $paypalId)->update([
                    'status' => 'pending',
                    'updated_at' => now(),
                ]);
                Log::info("Commande $paypalId en pending");

                // Capturer le paiement
                $paypal = new \Srmklive\PayPal\Services\PayPal;
                $paypal->setApiCredentials(config('paypal'));
                $token = $paypal->getAccessToken();
                $paypal->setAccessToken($token);

                try {
                    $capture = $paypal->capturePaymentOrder($paypalId);

                    if ($capture['status'] === 'COMPLETED') {
                        $amount = $capture['purchase_units'][0]['payments']['captures'][0]['amount']['value'] ?? null;
                        Commande::where('paypal_id', $paypalId)->update([
                            'status' => 'paid',
                            'total'  => $amount,
                            'updated_at' => now(),
                        ]);
                        Log::info("✅ Commande $paypalId capturée automatiquement pour $amount");
                    } else {
                        Log::warning("Commande $paypalId non capturée : " . json_encode($capture));
                    }
                } catch (\Exception $e) {
                    Log::error("Erreur capture PayPal pour $paypalId : " . $e->getMessage());
                }

                break;

            case 'PAYMENT.CAPTURE.COMPLETED':
                $amount   = $payload['resource']['amount']['value'] ?? null;
                $currency = $payload['resource']['amount']['currency_code'] ?? null;

                Commande::where('paypal_id', $paypalId)->update([
                    'status' => 'paid',
                    'total'  => $amount,
                    'updated_at' => now(),
                ]);
                Log::info("Commande $paypalId payée : $amount $currency");
                break;

            case 'BILLING.SUBSCRIPTION.CREATED':
                Commande::where('paypal_id', $paypalId)->update([
                    'status' => 'pending',
                    'updated_at' => now(),
                ]);
                Log::info("Abonnement $paypalId créé (pending)");
                break;

            case 'BILLING.SUBSCRIPTION.ACTIVATED':
                Commande::where('paypal_id', $paypalId)->update([
                    'status' => 'active',
                    'updated_at' => now(),
                ]);
                Log::info("Abonnement $paypalId activé");
                break;

            case 'BILLING.SUBSCRIPTION.CANCELLED':
                Commande::where('paypal_id', $paypalId)->update([
                    'status' => 'cancelled',
                    'updated_at' => now(),
                ]);
                Log::info("Abonnement $paypalId annulé");
                break;

            case 'BILLING.SUBSCRIPTION.SUSPENDED':
                Commande::where('paypal_id', $paypalId)->update([
                    'status' => 'suspended',
                    'updated_at' => now(),
                ]);
                Log::info("Abonnement $paypalId suspendu");
                break;

            case 'BILLING.SUBSCRIPTION.PAYMENT.SUCCEEDED':
                $amount   = $payload['resource']['amount_with_breakdown']['value'] ?? null;
                $currency = $payload['resource']['amount_with_breakdown']['currency_code'] ?? null;

                Commande::where('paypal_id', $paypalId)->update([
                    'status' => 'paid',
                    'total'  => $amount,
                    'updated_at' => now(),
                ]);
                Log::info("Abonnement $paypalId : paiement réussi $amount $currency");
                break;

            case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
                Commande::where('paypal_id', $paypalId)->update([
                    'status' => 'failed',
                    'updated_at' => now(),
                ]);
                Log::warning("Abonnement $paypalId : paiement échoué");
                break;

            default:
                Log::warning("Événement PayPal non géré : $event");
                break;
        }

        return;
    }

    public function createSubscriptionPaypal(Request $request)
    {
        $paypal = new PayPalClient;
        $paypal->setApiCredentials(config('paypal'));
        $token = $paypal->getAccessToken();
        $paypal->setAccessToken($token);

        $planId = null;
        switch($request['produit']) {
            case 'pro plus':
                $planId = env('PRO_PLUS_PLAN_ID');
                break;
        }

        if (!$planId) {
            return redirect()->route('paypalCancel')->with('error', 'Plan non trouvé');
        }

        $planDetails = $paypal->showPlanDetails($planId);

        $price = $planDetails['billing_cycles'][0]['pricing_scheme']['fixed_price']['value'] ?? 0;
        $currency = $planDetails['billing_cycles'][0]['pricing_scheme']['fixed_price']['currency_code'] ?? 'CAD';

        $response = $paypal->createSubscription([
            "plan_id" => $planId,
            "application_context" => [
                "return_url" => route('paypalSuccess'),
                "cancel_url" => route('paypalCancel'),
            ],
        ]);

        $commande = Commande::create([
            'status'                 => 'pending',
            'total'                  => $price,
            'session_id'             => '',
            'stripe_id'              => '',
            'stripe_subscription_id' => '',
            'paypal_id'              => $response['id'] ?? '',
            'user_id'                => $request->user()->id ?? '',
            'vendor_id'              => '',
            'id_type'                   => $this->typeToInt('subscription'), // abonnement
            'commission'             => 0,
        ]);

        foreach ($response['links'] ?? [] as $link) {
            if ($link['rel'] === 'approve') {
                return redirect()->away($link['href']);
            }
        }

        return redirect()->route('paypalCancel')->with('error', 'Plan non trouvé');
    }
}
