<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Commande extends Model
{
    use HasFactory;
    protected $table = 'commandes';
    protected $primaryKey = 'id';

     protected $fillable = [
        'status',
        'total',
        'session_id',
        'stripe_id',
        'paypal_id',
        'stripe_subscription_id',
        'vendor_id',
        'commission',
        'id_type'
    ];

    public static function typeToInt(string $type): int {
        return $type === 'subscription' ? 1 : 0;
    }
}
