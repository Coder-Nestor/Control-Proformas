<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Rol;

/**
 * Esta pantalla es la única del sistema que se queda restringida
 * directamente a Core\Auth::hasRole(['administrador']) en las rutas
 * (config/routes.php) en vez de pasar por el sistema de permisos
 * dinámico — así, aunque alguien desconfigure los permisos por error,
 * SIEMPRE va a poder entrar aquí un Administrador a corregirlo.
 */
class RolController extends Controller
{
    public function index(): void
    {
        $roles = Rol::todos();
        foreach ($roles as &$rol) {
            $rol['total_usuarios'] = Rol::contarUsuarios((int) $rol['id']);
            $rol['total_permisos'] = count(Rol::permisosDe((int) $rol['id']));
        }
        unset($rol);

        $this->view('roles/index', ['roles' => $roles]);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $nombre = $this->input('nombre');
        $slug = $this->input('slug');

        if (!$nombre || !$slug) {
            $this->flash('error', 'El nombre y el identificador del rol son obligatorios.');
            $this->redirect('/roles');
        }

        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '_', $slug)));

        if (Rol::slugExiste($slug)) {
            $this->flash('error', 'Ya existe un rol con ese identificador. Elige uno distinto.');
            $this->redirect('/roles');
        }

        $id = Rol::insert(['nombre' => $nombre, 'slug' => $slug]);

        $this->flash('success', 'Rol creado correctamente. Ahora asígnale sus permisos.');
        $this->redirect('/roles/' . $id . '/permisos');
    }

    /** Editar el nombre para mostrar de un rol (el identificador/slug no se toca). */
    public function update(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $rol = Rol::find($id);

        if (!$rol) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }

        $nombre = trim((string) $this->input('nombre', ''));
        if (!$nombre) {
            $this->flash('error', 'El nombre del rol es obligatorio.');
            $this->redirect('/roles');
        }

        Rol::update($id, ['nombre' => $nombre]);

        $this->flash('success', 'Rol actualizado correctamente.');
        $this->redirect('/roles');
    }

    /** Pantalla de la matriz de permisos (checkboxes) de un rol. */
    public function permisos(array $params): void
    {
        $id = (int) $params['id'];
        $rol = Rol::find($id);

        if (!$rol) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }

        $this->view('roles/permisos', [
            'rol'               => $rol,
            'catalogoPorModulo' => Rol::catalogoPermisosPorModulo(),
            'permisosActuales'  => Rol::permisosDe($id),
        ]);
    }

    public function guardarPermisos(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $rol = Rol::find($id);

        if (!$rol) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }

        $permisoIds = $_POST['permisos'] ?? [];
        Rol::guardarPermisos($id, $permisoIds);

        $this->flash('success', 'Permisos de "' . $rol['nombre'] . '" actualizados correctamente.');
        $this->redirect('/roles');
    }

    public function destroy(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $rol = Rol::find($id);

        if (!$rol) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }

        if (in_array($rol['slug'], ['administrador'], true)) {
            $this->flash('error', 'El rol "Administrador" no se puede eliminar — el sistema siempre necesita al menos uno.');
            $this->redirect('/roles');
        }

        $totalUsuarios = Rol::contarUsuarios($id);
        if ($totalUsuarios > 0) {
            $this->flash('error', 'No se puede eliminar: hay ' . $totalUsuarios . ' usuario(s) con este rol asignado. Reasígnalos primero a otro rol.');
            $this->redirect('/roles');
        }

        Rol::delete($id);
        $this->flash('success', 'Rol eliminado correctamente.');
        $this->redirect('/roles');
    }
}