<?php

namespace App\Controllers;

use App\Models\User;

class LoginController
{
    public function showLogin()
    {
        $error_message = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);
        require __DIR__ . '/../Views/Login.php';
    }

    /**
     * Procesa los datos del formulario de login.
     * Si las credenciales son válidas, establece las variables de sesión y devuelve el rol del usuario.
     * Si no, devuelve false.
     *
     * @return int|false El rol del usuario si el login es exitoso, de lo contrario false.
     */
    public function processLogin()
    {
        $username = $_POST['usuario'] ?? '';
        $password = $_POST['contrasenia'] ?? '';

        if (empty($username) || empty($password)) {
            $_SESSION['error'] = 'Por favor, introduce usuario y contraseña.';
            return false;
        }

        $userModel = new User();
        $user = $userModel->verifyCredentials($username, $password);

        if ($user) {
            $_SESSION['user_id'] = $user['id_usuario'];
            $_SESSION['user_role'] = $user['id_rol'];
            return $user['id_rol']; // Devuelve el rol para que el router rediriga en base a él.
        } else {
            $_SESSION['error'] = 'Usuario o contraseña incorrectos.';
            return false;
        }
    }
}