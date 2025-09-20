<?php

use App\Http\Controllers\CategorieController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

use function PHPUnit\Framework\callback;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::controller(CategorieController::class)->group(function() {
    Route::get('/categories', 'index')->name('categories');
    Route::get('/categorie/{id}', 'show')->name('categorie');
});
Route::controller(PostController::class)->group(callback: function(): void{
    Route::get('/posts','index')->name('posts');
    Route::get('/posts/mine','myPosts')->name('myPosts');
    Route::get('/post/formulairePoste','create')->name('formulairePoste');
    Route::post('/post/insertionPoste','store')->name('insertionPoste');
    Route::post('/post/suppressionProduit','destroy')->name('suppressionProduit');
    Route::get('/post/paiementPost/{id}', 'paiement')->name('paiementPost');
});

Route::controller(CommandeController::class)->group(function(): void{
    Route::get('/commande/checkout', 'checkout')->name('checkout');
    Route::get('/commande/success', 'success')->name('success');
    Route::get('/commande/cancel', 'cancel')->name('cancel');
    Route::get('/commande/paypalCheckout/{id}', 'paypalCheckout')->name('paypalCheckout');
    Route::get('/commande/paypalSuccess', 'paypalSuccess')->name('paypalSuccess');
    Route::get('/commande/paypalCancel', 'paypalCancel')->name('paypalCancel');
    Route::get('/subscription/createPaypalSub/{produit}', 'createSubscriptionPaypal')->name('createPaypalSub');



});

Route::controller(UserController::class)->group(function(): void{
    Route::get('/user/linkStripe', 'linkStripeAccount')->name('linkStripe');
    Route::get('/user/linkPaypal', 'linkPaypalAccount')->name('linkPaypal');
    Route::get('/user/linkStripe/success', 'linkStripeSuccess')->name('linkStripeSuccess');

    Route::get('/user/linkPaypal/success', 'linkPaypalSuccess')->name('linkPaypalSuccess');
});

require __DIR__.'/auth.php';
