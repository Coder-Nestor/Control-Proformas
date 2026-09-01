<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\OrdenCompra;
use App\Models\Proforma;
use App\Models\Factura;
use App\Models\EntregaFactura;

class OrdenCompraController extends Controller
{
    public function index(): void
    {
        $filtros = ['estado' => $this->input('estado', ''), 'buscar' => $this->input('buscar', '')];
        $this->view('ordenes/index', ['ordenes' => OrdenCompra::allConDetalle($filtros), 'estados' => OrdenCompra::ESTADOS, 'filtros' => $filtros]);
    }

    public function create(): void
    {
        $selectedProformaId = (int) $this->input('proforma_id', 0);
        $this->view('ordenes/form', ['oc' => null, 'proformas' => Proforma::sinOrdenDeCompra(), 'estados' => OrdenCompra::ESTADOS, 'selectedProformaId' => $selectedProformaId]);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->collectFormData();
        $data['documento_pdf'] = $this->handleUpload('documento_pdf', 'ordenes_compra');
        $data['creado_por'] = Auth::id();

        $id = OrdenCompra::insert($data);

        $ocCreada = OrdenCompra::findConDetalle($id);
        $descripcionCrear = $ocCreada
            ? sprintf('Creó la orden de compra: N° OCE %s — Proforma %s — %s', $ocCreada['n_oce_interna'] ?: '(sin número)', $ocCreada['n_proforma'] ?: '(sin número)', $ocCreada['proveedor_nombre'] ?? 'proveedor no especificado')
            : 'Creó la orden de compra';
        OrdenCompra::registrarHistorial($id, Auth::id(), $descripcionCrear);

        $this->flash('success', 'Orden de compra registrada correctamente.');
        $this->redirect('/ordenes/' . $id);
    }

    public function show(array $params): void
    {
        $id = (int) $params['id'];
        $oc = OrdenCompra::findConDetalle($id);
        if (!$oc) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }
        $this->view('ordenes/show', ['oc' => $oc, 'factura' => Factura::findPorOrdenCompra($id), 'historial' => OrdenCompra::historialDe($id)]);
    }

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $oc = OrdenCompra::findConDetalle($id);
        if (!$oc) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }
        $this->view('ordenes/form', ['oc' => $oc, 'proformas' => Proforma::sinOrdenDeCompra($oc['proforma_id']), 'estados' => OrdenCompra::ESTADOS]);
    }

    public function update(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $actual = OrdenCompra::find($id);
        $data = $this->collectFormData();

        $eliminarPdf = $this->input('eliminar_pdf', '0') === '1';
        if ($eliminarPdf && empty($_FILES['documento_pdf']['name'])) {
            if (!empty($actual['documento_pdf'])) {
                @unlink(__DIR__ . '/../../public/uploads/ordenes_compra/' . $actual['documento_pdf']);
            }
            $data['documento_pdf'] = null;
        } else {
            $data['documento_pdf'] = $this->handleUpload('documento_pdf', 'ordenes_compra', $actual['documento_pdf'] ?? null);
        }

        OrdenCompra::update($id, $data);

        $ocActualizada = OrdenCompra::findConDetalle($id);
        $descripcionActualizar = $ocActualizada
            ? sprintf('Actualizó los datos de la orden de compra: N° OCE %s — Proforma %s — %s', $ocActualizada['n_oce_interna'] ?: '(sin número)', $ocActualizada['n_proforma'] ?: '(sin número)', $ocActualizada['proveedor_nombre'] ?? 'proveedor no especificado')
            : 'Actualizó los datos de la orden de compra';
        OrdenCompra::registrarHistorial($id, Auth::id(), $descripcionActualizar);

        $this->flash('success', 'Orden de compra actualizada correctamente.');
        $this->redirect('/ordenes/' . $id);
    }

    public function destroy(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];

        $oc = OrdenCompra::findConDetalle($id);

        // Si esta OC ya tiene una factura marcada como "Correcta" (cerrada),
        // no se puede eliminar sin ser Administrador — eliminar la OC borraría
        // esa factura en cascada, saltándose el cierre que ya se le puso.
        $facturaVinculada = Factura::findPorOrdenCompra($id);
        if ($facturaVinculada && $facturaVinculada['estado'] === 'correcta' && !Auth::hasRole(['administrador'])) {
            $this->flash('error', 'Esta orden de compra tiene una factura marcada como "Correcta" y cerrada — no se puede eliminar.');
            $this->redirect('/ordenes/' . $id);
        }

        if ($oc && !empty($oc['documento_pdf'])) {
            @unlink(__DIR__ . '/../../public/uploads/ordenes_compra/' . $oc['documento_pdf']);
        }

        // Cascada manual: Factura -> Entrega
        if ($facturaVinculada) {
            $entrega = EntregaFactura::findPorFactura($facturaVinculada['id']);
            if ($entrega) {
                EntregaFactura::registrarHistorial($entrega['id'], Auth::id(), 'Eliminada automáticamente (cascada al eliminar la orden de compra)');
                EntregaFactura::softDelete($entrega['id'], Auth::id());
            }
            Factura::registrarHistorial($facturaVinculada['id'], Auth::id(), 'Eliminada automáticamente (cascada al eliminar la orden de compra)');
            Factura::softDelete($facturaVinculada['id'], Auth::id());
        }

        $descripcion = $oc
            ? sprintf('Eliminó la orden de compra: N° OCE %s — Proforma %s — %s', $oc['n_oce_interna'] ?: '(sin número)', $oc['n_proforma'] ?: '(sin número)', $oc['proveedor_nombre'] ?? 'proveedor no especificado')
            : 'Eliminó la orden de compra';

        OrdenCompra::registrarHistorial($id, Auth::id(), $descripcion);
        OrdenCompra::softDelete($id, Auth::id());

        $this->flash('success', 'Orden de compra eliminada.');
        $this->redirect('/ordenes');
    }

    private function collectFormData(): array
    {
        $campos = ['proforma_id', 'fecha_envio_oce', 'n_oce_interna', 'estado', 'comentario'];
        $data = [];
        foreach ($campos as $campo) {
            $valor = $this->input($campo, null);
            $data[$campo] = ($valor === '' || $valor === null) ? null : $valor;
        }
        $data['proforma_id'] = (int) $data['proforma_id'];
        if (!array_key_exists($data['estado'], OrdenCompra::ESTADOS)) $data['estado'] = 'pendiente';
        return $data;
    }
}