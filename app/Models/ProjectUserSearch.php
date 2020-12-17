<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectUserSearch extends Model
{
    use HasFactory;

    protected $fillable = [
        "pid",
        "uid",
        "is_public",
    ];

    protected $casts = [
        'pid' => 'integer',
        'uid' => 'integer',
        'is_public' => 'boolean',
    ];



}
