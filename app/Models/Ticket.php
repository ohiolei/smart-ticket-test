<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
     use HasFactory, HasUlids;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'subject',
        'body',
        'status',
        'category',
        'explanation',
        'confidence',
        'note'
    ];

    protected $casts = [
        'confidence' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
