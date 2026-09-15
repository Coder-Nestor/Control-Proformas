<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\Usuario;
use App\Models\Rol;
use App\Models\Area;
use App\Models\Historial;

class UsuarioController extends Controller
{
    public function index(): void
    {
        $this->view('usuarios/index', [
            'usuarios' => Usuario::allConRol($this->esAdministrador()),
        ]);
    }

    public function create(): void
    {
        $esAdministrador = $this->esAdministrador();
        $roles = Rol::all('nombre ASC');

        if (!$esAdministrador) {
            $roles = array_values(array_filter($roles, static fn (array $rol): bool => (int) $rol['id'] !== 1));
        }

        $this->view('usuarios/form', [
            'usuario'             => null,
            'roles'               => $roles,
            'areas'               => Area::activas(),
            'esAdministrador'     => $esAdministrador,
            'rolAdministradorBloqueado' => false,
        ]);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $rolId = (int) $this->input('rol_id');

        if (!$this->esAdministrador() && $rolId === 1) {
            $this->flash('error', 'No tienes permiso para crear usuarios con el rol Administrador.');
            $this->redirect('/usuarios/crear');
            return;
        }

        $errores = $this->validar();

        if ($errores) {
            $this->flash('error', implode(' ', $errores));
            $this->redirect('/usuarios/crear');
        }

        $nombre = trim((string) $this->input('nombre'));
        $email  = trim((string) $this->input('email'));

        $id = Usuario::insert([
            'nombre'        => $nombre,
            'email'         => $email,
            'password_hash' => password_hash($this->input('password'), PASSWORD_DEFAULT),
            'rol_id'        => $rolId,
            'area'          => $this->input('area', null) ?: null,
            'activo'        => 1,
        ]);

        Historial::registrar('usuario', $id, Auth::id(), 'Creó el usuario: ' . $nombre . ' (' . $email . ')');

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

        $esAdministrador = $this->esAdministrador();
        $rolAdministradorBloqueado = !$esAdministrador && (int) $usuario['rol_id'] === 1;
        $roles = Rol::all('nombre ASC');

        if (!$esAdministrador && !$rolAdministradorBloqueado) {
            $roles = array_values(array_filter($roles, static fn (array $rol): bool => (int) $rol['id'] !== 1));
        }

        $this->view('usuarios/form', [
            'usuario'                    => $usuario,
            'roles'                      => $roles,
            'areas'                      => Area::activas(),
            'esAdministrador'            => $esAdministrador,
            'rolAdministradorBloqueado' => $rolAdministradorBloqueado,
        ]);
    }

    public function update(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $usuarioAnt = Usuario::find($id);

        if (!$usuarioAnt) {
            $this->flash('error', 'Usuario no encontrado.');
            $this->redirect('/usuarios');
            return;
        }

        $nombre = trim((string) $this->input('nombre'));
        $email  = trim((string) $this->input('email'));
        $rolId  = (int) $this->input('rol_id');

        if (!$this->esAdministrador() && $rolId === 1) {
            $this->flash('error', 'No tienes permiso para asignar el rol Administrador.');
            $this->redirect('/usuarios/' . $id . '/editar');
            return;
        }

        $activo = $this->input('activo', (string) $usuarioAnt['activo']) === '1' ? 1 : 0;
        if (!Auth::can('usuarios.activar')) {
            $activo = (int) $usuarioAnt['activo'];
        }

        $data = [
            'nombre' => $nombre,
            'email'  => $email,
            'rol_id' => $rolId,
            'area'   => $this->input('area', null) ?: null,
            'activo' => $activo,
        ];

        $password = $this->input('password', '');
        if (!empty($password)) {
            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        Usuario::update($id, $data);

        $cambios = [];
        if ($usuarioAnt['nombre'] !== $data['nombre']) $cambios[] = 'nombre';
        if ($usuarioAnt['email'] !== $data['email']) $cambios[] = 'correo';
        if ((int)$usuarioAnt['rol_id'] !== (int)$data['rol_id']) $cambios[] = 'rol';
        if (($usuarioAnt['area'] ?? '') !== ($data['area'] ?? '')) $cambios[] = 'área';
        if ((int)$usuarioAnt['activo'] !== (int)$data['activo']) $cambios[] = $data['activo'] ? 'activó' : 'desactivó';
        if (!empty($password)) $cambios[] = 'contraseña';

        $detalle = 'Actualizó el usuario: ' . $data['nombre'] . ' (' . $data['email'] . ')';
        if (!empty($cambios)) {
            $detalle .= ' [' . implode(', ', $cambios) . ']';
        }
        Historial::registrar('usuario', $id, Auth::id(), $detalle);

        $this->flash('success', 'Usuario actualizado.');
        $this->redirect('/usuarios');
    }

    public function toggleEstado(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $usuario = Usuario::find($id);

        if (!$usuario) {
            $this->flash('error', 'Usuario no encontrado.');
            $this->redirect('/usuarios');
            return;
        }

        if ($id === Auth::id() && (int)$usuario['activo'] === 1) {
            $this->flash('error', 'No puedes desactivar tu propia cuenta de usuario.');
            $this->redirect('/usuarios');
            return;
        }

        $nuevoEstado = $usuario['activo'] ? 0 : 1;
        Usuario::update($id, ['activo' => $nuevoEstado]);

        $accion = $nuevoEstado ? 'Activó al usuario: ' : 'Desactivó al usuario: ';
        Historial::registrar('usuario', $id, Auth::id(), $accion . $usuario['nombre'] . ' (' . $usuario['email'] . ')');

        $this->flash('success', $nuevoEstado ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.');
        $this->redirect('/usuarios');
    }

    public function destroy(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $usuario = Usuario::find($id);

        if ($id === Auth::id()) {
            $this->flash('error', 'No puedes eliminar tu propia cuenta de usuario.');
            $this->redirect('/usuarios');
            return;
        }

        Usuario::delete($id);

        $info = $usuario ? $usuario['nombre'] . ' (' . $usuario['email'] . ')' : "#$id";
        Historial::registrar('usuario', $id, Auth::id(), 'Eliminó el usuario: ' . $info);

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

    private function esAdministrador(): bool
    {
        $usuario = Usuario::find(Auth::id());
        return $usuario && (int) $usuario['rol_id'] === 1;
    }
}
