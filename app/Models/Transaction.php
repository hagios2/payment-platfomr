<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'currency',
        'user_id',
        'charge_id',
        'description',
        'reference',
        'type',
        'status'
    ];
}
