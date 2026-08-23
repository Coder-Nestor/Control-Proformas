<?php

namespace Core;

class Router
{
    private array $routes = [];

    public function load(array $routeTable): void
    {
        $this->routes = $routeTable;
    }

    public function dispatch(string $method, string $uri): void
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/');
        if ($uri === '') {
            $uri = '/';
        }

        foreach ($this->routes as [$routeMethod, $pattern, $handler, $roles]) {
            if ($routeMethod !== $method) {
                continue;
            }

            $regex = $this->patternToRegex($pattern);
            if (preg_match($regex, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Control de acceso
                if ($roles !== ['*']) {
                    if (!Auth::check()) {
                        $_SESSION['intended'] = $uri;
                        Response::redirect('/login');
                        return;
                    }

                    // $roles puede ser:
                    //  - array de slugs de rol (como siempre, ej. ['administrador','auditoria'])
                    //  - texto de permiso dinámico (ej. 'gestiones.ver'), validado
                    //    contra la tabla rol_permisos desde Roles y Permisos
                    //  - null: cualquier usuario logueado puede entrar (sin
                    //    restricción de rol ni permiso) — comportamiento original
                    if (is_array($roles)) {
                        $autorizado = Auth::hasRole($roles);
                    } elseif (is_string($roles)) {
                        $autorizado = Auth::can($roles);
                    } else {
                        $autorizado = true;
                    }

                    if (!$autorizado) {
                        http_response_code(403);
                        require __DIR__ . '/../app/Views/errors/403.php';
                        return;
                    }
                }

                [$controllerClass, $action] = $handler;
                $controller = new $controllerClass();
                $controller->$action($params);
                return;
            }
        }

        http_response_code(404);
        require __DIR__ . '/../app/Views/errors/404.php';
    }

    private function patternToRegex(string $pattern): string
    {
        // Convierte /solicitudes/{id}/editar  ->  #^/solicitudes/(?P<id>[^/]+)/editar$#
        $regex = preg_replace_callback('/\{([a-zA-Z_]+)\}/', function ($m) {
            return '(?P<' . $m[1] . '>[^/]+)';
        }, $pattern);

        return '#^' . $regex . '$#';
    }
}