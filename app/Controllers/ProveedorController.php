<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Proveedor;

class ProveedorController extends Controller
{
    public function index(): void
    {
        $this->view('proveedores/index', [
            'proveedores' => Proveedor::all('nombre ASC'),
        ]);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $nombre = $this->input('nombre');

        if ($nombre) {
            Proveedor::insert(['nombre' => $nombre, 'activo' => 1]);
            $this->flash('success', 'Proveedor agregado.');
        } else {
            $this->flash('error', 'El nombre del proveedor es obligatorio.');
        }

        $this->redirect('/proveedores');
    }

    public function update(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];

        Proveedor::update($id, [
            'nombre' => $this->input('nombre'),
            'activo' => $this->input('activo', '1') === '1' ? 1 : 0,
        ]);

        $this->flash('success', 'Proveedor actualizado.');
        $this->redirect('/proveedores');
    }

    public function destroy(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        Proveedor::delete($id);

        $this->flash('success', 'Proveedor eliminado.');
        $this->redirect('/proveedores');
    }
}
