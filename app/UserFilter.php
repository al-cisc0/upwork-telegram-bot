<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserFilter extends Model
{
    protected $fillable = ['user_id', 'type', 'value'];

}
