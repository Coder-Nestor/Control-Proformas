<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Area;

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
        $nombre = $this->input('nombre');

        if (!$nombre) {
            $this->flash('error', 'El nombre del área es obligatorio.');
            $this->redirect('/areas');
        }

        if (Area::nombreExiste($nombre)) {
            $this->flash('error', 'Ya existe un área con ese nombre.');
            $this->redirect('/areas');
        }

        Area::insert(['nombre' => $nombre, 'activo' => 1]);
        $this->flash('success', 'Área agregada correctamente.');
        $this->redirect('/areas');
    }

    public function update(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $nombre = $this->input('nombre');

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
            'activo' => $this->input('activo', '1') === '1' ? 1 : 0,
        ]);

        $this->flash('success', 'Área actualizada correctamente.');
        $this->redirect('/areas');
    }

    public function destroy(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];

        Area::delete($id);

        $this->flash('success', 'Área eliminada. Las proformas o usuarios que ya la usaban conservan el nombre tal como quedó registrado.');
        $this->redirect('/areas');
    }
}
