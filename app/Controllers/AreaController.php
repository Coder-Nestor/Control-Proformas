<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\Area;
use App\Models\Historial;

class AreaController extends Controller
{
    public function index(): void
    {
        $this->view('areas/index', [
            'areas' => Area::todas(),
        ]);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $nombre = trim((string) $this->input('nombre', ''));

        if (!$nombre) {
            $this->flash('error', 'El nombre del área es obligatorio.');
            $this->redirect('/areas');
        }

        if (Area::nombreExiste($nombre)) {
            $this->flash('error', 'Ya existe un área con ese nombre.');
            $this->redirect('/areas');
        }

        $id = Area::insert(['nombre' => $nombre, 'activo' => 1]);
        Historial::registrar('area', $id, Auth::id(), 'Creó el área: ' . $nombre);

        $this->flash('success', 'Área agregada correctamente.');
        $this->redirect('/areas');
    }

    public function update(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $nombre = trim((string) $this->input('nombre', ''));
        $activo = $this->input('activo', '1') === '1' ? 1 : 0;

        if (!$nombre) {
            $this->flash('error', 'El nombre del área es obligatorio.');
            $this->redirect('/areas');
        }

        if (Area::nombreExiste($nombre, $id)) {
            $this->flash('error', 'Ya existe otra área con ese nombre.');
            $this->redirect('/areas');
        }

        Area::update($id, [
            'nombre' => $nombre,
            'activo' => $activo,
        ]);

        $detalle = 'Actualizó el área: ' . $nombre . ($activo ? ' (Activa)' : ' (Inactiva)');
        Historial::registrar('area', $id, Auth::id(), $detalle);

        $this->flash('success', 'Área actualizada correctamente.');
        $this->redirect('/areas');
    }

    public function destroy(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $area = Area::find($id);

        Area::delete($id);

        $nombre = $area['nombre'] ?? "#$id";
        Historial::registrar('area', $id, Auth::id(), 'Eliminó el área: ' . $nombre);

        $this->flash('success', 'Área eliminada. Las proformas o usuarios que ya la usaban conservan el nombre tal como quedó registrado.');
        $this->redirect('/areas');
    }
}
