<?php

namespace Core;

use App\Models\Usuario;

class Auth
{
    public static function attempt(string $email, string $password): bool
    {
        $usuario = Usuario::findByEmail($email);

        if (!$usuario || !$usuario['activo']) {
            return false;
        }

        if (!password_verify($password, $usuario['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user_id']   = $usuario['id'];
        $_SESSION['user_name'] = $usuario['nombre'];
        $_SESSION['user_role'] = $usuario['rol_slug'];
        $_SESSION['user_area'] = $usuario['area'];

        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function name(): string
    {
        return $_SESSION['user_name'] ?? '';
    }

    public static function role(): ?string
    {
        return $_SESSION['user_role'] ?? null;
    }

    public static function area(): ?string
    {
        return $_SESSION['user_area'] ?? null;
    }

    /** @param string[] $roles */
    public static function hasRole(array $roles): bool
    {
        return in_array(self::role(), $roles, true);
    }
}
