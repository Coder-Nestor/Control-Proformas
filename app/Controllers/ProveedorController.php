<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\Proveedor;
use App\Models\Historial;

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
        $nombre = trim((string) $this->input('nombre', ''));
        $habilitadoProforma = $this->input('habilitado_proforma', '0') === '1' ? 1 : 0;

        if ($nombre) {
            $id = Proveedor::insert(['nombre' => $nombre, 'activo' => 1, 'habilitado_proforma' => $habilitadoProforma]);
            $detalle = 'Creó el proveedor: ' . $nombre . ($habilitadoProforma ? ' — habilitado para Proforma' : ' — sin habilitar para Proforma');
            Historial::registrar('proveedor', $id, Auth::id(), $detalle);
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
        $nombre = trim((string) $this->input('nombre', ''));
        $activo = $this->input('activo', '1') === '1' ? 1 : 0;
        $habilitadoProforma = $this->input('habilitado_proforma', '0') === '1' ? 1 : 0;

        if (!$nombre) {
            $this->flash('error', 'El nombre del proveedor es obligatorio.');
            $this->redirect('/proveedores');
            return;
        }

        Proveedor::update($id, [
            'nombre' => $nombre,
            'activo' => $activo,
            'habilitado_proforma' => $habilitadoProforma,
        ]);

        $detalle = 'Actualizó el proveedor: ' . $nombre . ($activo ? ' (Activo)' : ' (Inactivo)')
            . ($habilitadoProforma ? ' — habilitado para Proforma' : ' — sin habilitar para Proforma');
        Historial::registrar('proveedor', $id, Auth::id(), $detalle);

        $this->flash('success', 'Proveedor actualizado.');
        $this->redirect('/proveedores');
    }

    public function destroy(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $proveedor = Proveedor::find($id);

        Proveedor::delete($id);

        $nombre = $proveedor['nombre'] ?? "#$id";
        Historial::registrar('proveedor', $id, Auth::id(), 'Eliminó el proveedor: ' . $nombre);

        $this->flash('success', 'Proveedor eliminado.');
        $this->redirect('/proveedores');
    }
}
