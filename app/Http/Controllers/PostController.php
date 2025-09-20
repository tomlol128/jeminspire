<?php

namespace App\Http\Controllers;

use App\Mail\ConfrimationPoste;
use App\Models\Categorie;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator as Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view("post/posts", ['postes'=> Post::all()]);
    }
    /**
     * Afficher les postes du user seulement
     */
    public function myPosts() {
    $user = Auth::user();
    $postes = $user->posts;

    return view('post/myPosts', ['postes' => $postes]);    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view("post/formulairePoste", ['categories'=> Categorie::all()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
        // Vous pouvez combiner plusieurs règles de validation à condition de les séparer par des "|". Les noms
        // clés de ce tableau associatif doivent correspondent aux termes inscrits dans les attributs "name" des
        // balises <input />, <select> et <textearea>.
        'titre' => 'required|max:100',
        'description' => 'required|max:1000',
        'categorie' => 'required|exists:categories,id_categorie',
        'prix' => 'required',
        ], [
        // Vous pouvez écrire un message d’erreur distinct par règle de validation fournie plus haut.
        'titre.required' => 'Veuillez inscrire une titre .',
        'titre.max' => 'Votre titre  ne peut pas dépasser 100 caractères.',
        'description.required' => 'Veuillez inscrire une description ou un commentaire.',
        'description.max' => 'Votre description ne peut pas dépasser 1000 caractères.',
        'categorie.required' => 'Veuillez sélectionner une catégorie.', // message pour categorie
        'categorie.exists' => 'La catégorie sélectionnée est invalide.',
        'prix.required' => 'Veuillez entrer un prix',
        ]);
        if ($validation->fails())
            return back()->withErrors($validation->errors())->withInput();

        $contenuFormulaire = $validation->validated();



        $poste = Post::create([
            'id_user' => Auth::id(),
            'id_categorie' => $contenuFormulaire['categorie'],
            'titre' => $contenuFormulaire['titre'],
            'description' => $contenuFormulaire['description'],
            'prix' => $contenuFormulaire['prix'],
        ]);

        //Mail::to($request->user())->send(new ConfrimationPoste($poste));

        return View('post/confirmationPoste');
    }
    public function paiement(int $id)
    {
        return view("post/paiementPost", ['poste'=> Post::find($id)]);
    }
    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
                // On peut utiliser la méthode "input()" sur une requête pour récupérer une valeur, peu
        // importe si cette valeur a été transmise en GET ou en POST.
        $id = $request->input('id_produit');
        // La méthode "destroy()" du modèle Produit.php supprime le produit en BD possédant l’ID
        // fourni. Elle retourne "true" si la suppression a réussi ou "false" si elle a échoué.
        if (Produit::destroy($id))
        return back()->with('succes', 'La suppression du produit a bien fonctionné.');
        // La méthode "back()" permet de retourner sur la page précédente (soit la page des
        // produits) et la méthode « with() » permet d’inscrire dans la session PHP une clé
        // ("succes" ou "erreur" dans ce cas-ci) suivie d’un message personnalisé.
        return back()->with('erreur', 'La suppression du produit n\'a pas fonctionné.');
    }
}
