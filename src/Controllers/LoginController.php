<?php

namespace App\Controllers;

class LoginController
{
    public function showLogin()
    {
        require __DIR__ . '/../Views/Login.php';
    }
}