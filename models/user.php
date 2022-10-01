<?php

namespace models;
use core\data\Model;

class User extends Model
{
    public string $firstname;
    public string $lastname;
    public ?string $email;
}