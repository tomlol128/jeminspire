<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string("status");
            $table->float("total",6,2);
            $table->string("session_id");
            $table->string("stripe_id");
            $table->string("paypal_id");
            $table->string("stripe_subscription_id");
            $table->string("vendor_id");
            $table->integer("commission");
            $table->bigInteger('id_type')->unsigned();

            $table->foreign('id_type')->references('id')->on('typepaiements');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};
