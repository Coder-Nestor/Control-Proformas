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
            'proveedores' => Proveedor::allConEstadisticas(),
        ]);
    }

    public function buscarSimilares(): void
    {
        $nombre = (string) $this->input('nombre', '');
        $excluirId = $this->input('excluir_id', null);
        $excluirId = ($excluirId !== null && $excluirId !== '') ? (int) $excluirId : null;

        $similares = Proveedor::buscarSimilares($nombre, $excluirId);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'query' => $nombre,
            'total' => count($similares),
            'similares' => $similares,
        ]);
        exit;
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

    public function toggleProforma(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $proveedor = Proveedor::find($id);

        if (!$proveedor) {
            $this->flash('error', 'Proveedor no encontrado.');
            $this->redirect('/proveedores');
            return;
        }

        $nuevoValor = empty($proveedor['habilitado_proforma']) ? 1 : 0;
        Proveedor::update($id, ['habilitado_proforma' => $nuevoValor]);

        $estadoTexto = $nuevoValor ? 'habilitado para pasar a Proforma' : 'inhabilitado para pasar a Proforma (solo Gestiones)';
        Historial::registrar('proveedor', $id, Auth::id(), "Cambió condición de proforma del proveedor '{$proveedor['nombre']}': $estadoTexto");

        $this->flash('success', "Proveedor '{$proveedor['nombre']}' " . ($nuevoValor ? 'ahora PUEDE pasar a Proforma.' : 'ya NO puede pasar a Proforma.'));
        $this->redirect('/proveedores');
    }

    public function toggleEstado(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $proveedor = Proveedor::find($id);

        if (!$proveedor) {
            $this->flash('error', 'Proveedor no encontrado.');
            $this->redirect('/proveedores');
            return;
        }

        $nuevoActivo = empty($proveedor['activo']) ? 1 : 0;
        Proveedor::update($id, ['activo' => $nuevoActivo]);

        $estadoTexto = $nuevoActivo ? 'Activó' : 'Desactivó';
        Historial::registrar('proveedor', $id, Auth::id(), "$estadoTexto al proveedor '{$proveedor['nombre']}'");

        $this->flash('success', "Proveedor '{$proveedor['nombre']}' " . ($nuevoActivo ? 'activado.' : 'desactivado.'));
        $this->redirect('/proveedores');
    }

    public function destroy(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $proveedor = Proveedor::find($id);

        if (!$proveedor) {
            $this->flash('error', 'Proveedor no encontrado.');
            $this->redirect('/proveedores');
            return;
        }

        $gestiones = Proveedor::contarGestiones($id);
        $proformas = Proveedor::contarProformas($id);

        if ($gestiones > 0 || $proformas > 0) {
            $detalles = [];
            if ($gestiones > 0) $detalles[] = "$gestiones gestión(es)";
            if ($proformas > 0) $detalles[] = "$proformas proforma(s)";
            $this->flash('error', "No se puede eliminar el proveedor '{$proveedor['nombre']}' porque tiene " . implode(' y ', $detalles) . " asociadas. Puedes desactivarlo para que no sea utilizado.");
            $this->redirect('/proveedores');
            return;
        }

        Proveedor::delete($id);

        $nombre = $proveedor['nombre'] ?? "#$id";
        Historial::registrar('proveedor', $id, Auth::id(), 'Eliminó el proveedor: ' . $nombre);

        $this->flash('success', 'Proveedor eliminado correctamente.');
        $this->redirect('/proveedores');
    }
}
