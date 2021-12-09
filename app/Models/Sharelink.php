<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sharelink extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'image_id',
        'sender_id',
    ];
}
