<?php

namespace App\Controllers;

use Core\Auth;
use Core\Controller;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }
        $this->viewOnly('auth/login', ['error' => flash_get('error')]);
    }

    public function login(): void
    {
        $this->verifyCsrf();

        $email = $this->input('email');
        $password = $this->input('password');

        if (!$email || !$password) {
            $this->flash('error', 'Ingresa correo y contraseña.');
            $this->redirect('/login');
        }

        if (Auth::attempt($email, $password)) {
            $intended = $_SESSION['intended'] ?? '/dashboard';
            unset($_SESSION['intended']);
            $this->redirect($intended);
        }

        $this->flash('error', 'Credenciales incorrectas o usuario inactivo.');
        $this->redirect('/login');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('/login');
    }
}
