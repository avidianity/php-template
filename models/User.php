<?php

namespace Models;

class User extends Model
{
    protected $fillable = ['username', 'password'];
    protected $hidden = ['password'];
}
