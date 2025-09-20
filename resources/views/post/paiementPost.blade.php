<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('paiement poste') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <h1>{{$poste->titre}}</h1>
                    <h2>{{$poste->description}}</h2>
                    <h3>{{$poste->prix}}</h3>

                    <a href="{{ route('checkout', ['id' => $poste->id_post, 'type' => 'one_time']) }}"
                        class="inline-flex items-center gap-3 px-4 py-2 rounded-lg shadow-md select-none transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#635BFF] bg-[#635BFF] hover:bg-[#554BDA] active:scale-95 text-white font-medium">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect x="2" y="5" width="20" height="3" rx="1.5"/>
                            <rect x="2" y="11" width="20" height="3" rx="1.5"/>
                            <rect x="2" y="17" width="20" height="3" rx="1.5"/>
                        </svg>
                        <span>Payer avec Stripe</span>
                    </a>

                    <a href="{{ route('checkout', ['id' => 1, 'type' => 'subscription']) }}"
                        class="inline-flex items-center gap-3 px-4 py-2 rounded-lg shadow-md select-none transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#635BFF] bg-[#635BFF] hover:bg-[#554BDA] active:scale-95 text-white font-medium">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect x="2" y="5" width="20" height="3" rx="1.5"/>
                            <rect x="2" y="11" width="20" height="3" rx="1.5"/>
                            <rect x="2" y="17" width="20" height="3" rx="1.5"/>
                        </svg>
                        <span>Subscribe avec Stripe</span>
                    </a>

                    <a href="{{ route('paypalCheckout', ['id' => $poste->id_post]) }}"
                        class="inline-flex items-center gap-3 px-4 py-2 rounded-lg shadow-md select-none transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#635BFF] bg-[#635BFF] hover:bg-[#554BDA] active:scale-95 text-white font-medium">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect x="2" y="5" width="20" height="3" rx="1.5"/>
                            <rect x="2" y="11" width="20" height="3" rx="1.5"/>
                            <rect x="2" y="17" width="20" height="3" rx="1.5"/>
                        </svg>
                        <span>Payer avec Paypal</span>
                    </a>

                    <a href="{{ route('createPaypalSub', ['produit' => 'pro plus']) }}"
                        class="inline-flex items-center gap-3 px-4 py-2 rounded-lg shadow-md select-none transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#635BFF] bg-[#635BFF] hover:bg-[#554BDA] active:scale-95 text-white font-medium">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect x="2" y="5" width="20" height="3" rx="1.5"/>
                            <rect x="2" y="11" width="20" height="3" rx="1.5"/>
                            <rect x="2" y="17" width="20" height="3" rx="1.5"/>
                        </svg>
                        <span>Subsribe avec Paypal</span>
                    </a>


                    <a href="{{ route('linkPaypal')}}"
                        class="inline-flex items-center gap-3 px-4 py-2 rounded-lg shadow-md select-none transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#635BFF] bg-[#635BFF] hover:bg-[#554BDA] active:scale-95 text-white font-medium">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect x="2" y="5" width="20" height="3" rx="1.5"/>
                            <rect x="2" y="11" width="20" height="3" rx="1.5"/>
                            <rect x="2" y="17" width="20" height="3" rx="1.5"/>
                        </svg>
                        <span>Link avec Paypal</span>
                    </a>

                    <a href="{{ route('linkStripe')}}"
                        class="inline-flex items-center gap-3 px-4 py-2 rounded-lg shadow-md select-none transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#635BFF] bg-[#635BFF] hover:bg-[#554BDA] active:scale-95 text-white font-medium">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <rect x="2" y="5" width="20" height="3" rx="1.5"/>
                            <rect x="2" y="11" width="20" height="3" rx="1.5"/>
                            <rect x="2" y="17" width="20" height="3" rx="1.5"/>
                        </svg>
                        <span>Link avec Stripe</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
