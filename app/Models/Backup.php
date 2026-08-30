<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'status', 'file_path', 'file_size', 'metadata', 'started_at', 'completed_at'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
