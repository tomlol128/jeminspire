<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;
    protected $table = 'posts';
    protected $primaryKey = 'id_post';

    protected $fillable = [
        'id_user',
        'id_categorie',
        'titre',
        'description',
        'prix',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
