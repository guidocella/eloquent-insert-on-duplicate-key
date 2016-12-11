<?php

namespace InsertOnDuplicateKey\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}
