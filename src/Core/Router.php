<?php

namespace App\Core;

use App\Controllers\LoginController;

class Router
{
    public function handleRequest()
    {
        $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if ($url === '/' || $url === '') {
            $controller = new LoginController();
            $controller->showLogin();
        } else {
            echo "404 Not Found";
        }
    }
}