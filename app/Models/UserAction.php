<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAction extends Model
{
    use HasFactory;

    const AC_SAVE = 1;
    const AC_DELETE = 2;
    const AC_NEXT = 3;

    protected $fillable = [
        'id',
        'user_id',
        'action_type'
    ];
}
