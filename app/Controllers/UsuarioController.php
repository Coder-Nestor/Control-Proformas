<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Area;

class UsuarioController extends Controller
{
    public function index(): void
    {
        $this->view('usuarios/index', [
            'usuarios' => Usuario::allConRol(),
        ]);
    }

    public function create(): void
    {
        $this->view('usuarios/form', [
            'usuario' => null,
            'roles'   => Rol::all('nombre ASC'),
            'areas'   => Area::activas(),
        ]);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $errores = $this->validar();

        if ($errores) {
            $this->flash('error', implode(' ', $errores));
            $this->redirect('/usuarios/crear');
        }

        Usuario::insert([
            'nombre'        => $this->input('nombre'),
            'email'         => $this->input('email'),
            'password_hash' => password_hash($this->input('password'), PASSWORD_DEFAULT),
            'rol_id'        => (int) $this->input('rol_id'),
            'area'          => $this->input('area', null) ?: null,
            'activo'        => 1,
        ]);

        $this->flash('success', 'Usuario creado correctamente.');
        $this->redirect('/usuarios');
    }

    public function edit(array $params): void
    {
        $usuario = Usuario::find((int) $params['id']);
        if (!$usuario) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }

        $this->view('usuarios/form', [
            'usuario' => $usuario,
            'roles'   => Rol::all('nombre ASC'),
            'areas'   => Area::activas(),
        ]);
    }

    public function update(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];

        $data = [
            'nombre' => $this->input('nombre'),
            'email'  => $this->input('email'),
            'rol_id' => (int) $this->input('rol_id'),
            'area'   => $this->input('area', null) ?: null,
            'activo' => $this->input('activo', '1') === '1' ? 1 : 0,
        ];

        $password = $this->input('password', '');
        if (!empty($password)) {
            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        Usuario::update($id, $data);
        $this->flash('success', 'Usuario actualizado.');
        $this->redirect('/usuarios');
    }

    public function destroy(array $params): void
    {
        $this->verifyCsrf();
        Usuario::delete((int) $params['id']);
        $this->flash('success', 'Usuario eliminado.');
        $this->redirect('/usuarios');
    }

    private function validar(): array
    {
        $errores = [];
        $nombre = $this->input('nombre');
        $email  = $this->input('email');
        $password = $this->input('password');

        if (!$nombre) $errores[] = 'El nombre es obligatorio.';
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Correo inválido.';
        if ($email && Usuario::emailExiste($email)) $errores[] = 'Ese correo ya está registrado.';
        if (!$password || strlen($password) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres.';

        return $errores;
    }
}
