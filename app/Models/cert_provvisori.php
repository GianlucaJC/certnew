<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cert_provvisori extends Model
{
    use HasFactory;

    protected $table = 'cert_provvisori';

    protected $fillable = [
        'stato',
        'perc_complete',
    ];
}