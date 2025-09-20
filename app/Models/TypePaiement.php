<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TypePaiement extends Model
{
    use HasFactory;
    protected $table = 'typepaiements';
    protected $primaryKey = 'id';

     protected $fillable = [
        'description',
    ];
}
