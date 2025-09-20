<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommandeController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::controller(CommandeController::class)->group(function(): void{
    Route::post('/webhook', 'webhook')->name('webhook');
    Route::post('/paypal/webhook', 'paypalWebhook')->name('paypalWebhook');
});
