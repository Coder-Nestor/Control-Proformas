<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\EntregaFactura;
use App\Models\Factura;

class EntregaFacturaController extends Controller
{
    public function index(): void
    {
        $filtros = [
            'buscar' => $this->input('buscar', ''),
        ];

        $this->view('entregas/index', [
            'entregas' => EntregaFactura::allConDetalle($filtros),
            'filtros'  => $filtros,
        ]);
    }

    public function create(): void
    {
        $this->view('entregas/form', [
            'entrega'  => null,
            'facturas' => Factura::sinEntrega(),
        ]);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->collectFormData();
        $data['documento_pdf'] = $this->handleUpload('documento_pdf', 'entregas');
        $data['creado_por'] = Auth::id();

        $id = EntregaFactura::insert($data);

        $entregaCreada = EntregaFactura::findConDetalle($id);
        $descripcionCrear = $entregaCreada
            ? sprintf(
                'Creó el registro de entrega: OCE %s — Proforma %s — %s',
                $entregaCreada['n_oce_interna'] ?: '(sin número)',
                $entregaCreada['n_proforma'] ?: '(sin número)',
                $entregaCreada['proveedor_nombre'] ?? 'proveedor no especificado'
              )
            : 'Creó el registro de entrega';
        EntregaFactura::registrarHistorial($id, Auth::id(), $descripcionCrear);

        $this->flash('success', 'Entrega registrada correctamente.');
        $this->redirect('/entregas/' . $id);
    }

    public function show(array $params): void
    {
        $id = (int) $params['id'];
        $entrega = EntregaFactura::findConDetalle($id);

        if (!$entrega) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }

        $this->view('entregas/show', [
            'entrega'   => $entrega,
            'historial' => EntregaFactura::historialDe($id),
        ]);
    }

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $entrega = EntregaFactura::find($id);

        if (!$entrega) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }

        $this->view('entregas/form', [
            'entrega'  => $entrega,
            'facturas' => Factura::sinEntrega($entrega['factura_id']),
        ]);
    }

    public function update(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $actual = EntregaFactura::find($id);

        $data = $this->collectFormData();

        $eliminarPdf = $this->input('eliminar_pdf', '0') === '1';
        if ($eliminarPdf && empty($_FILES['documento_pdf']['name'])) {
            if (!empty($actual['documento_pdf'])) {
                @unlink(__DIR__ . '/../../public/uploads/entregas/' . $actual['documento_pdf']);
            }
            $data['documento_pdf'] = null;
        } else {
            $data['documento_pdf'] = $this->handleUpload('documento_pdf', 'entregas', $actual['documento_pdf'] ?? null);
        }

        EntregaFactura::update($id, $data);

        $entregaActualizada = EntregaFactura::findConDetalle($id);
        $descripcionActualizar = $entregaActualizada
            ? sprintf(
                'Actualizó los datos de la entrega: OCE %s — Proforma %s — %s',
                $entregaActualizada['n_oce_interna'] ?: '(sin número)',
                $entregaActualizada['n_proforma'] ?: '(sin número)',
                $entregaActualizada['proveedor_nombre'] ?? 'proveedor no especificado'
              )
            : 'Actualizó los datos de la entrega';
        EntregaFactura::registrarHistorial($id, Auth::id(), $descripcionActualizar);

        $this->flash('success', 'Entrega actualizada correctamente.');
        $this->redirect('/entregas/' . $id);
    }

    public function destroy(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];

        $entrega = EntregaFactura::findConDetalle($id);
        if ($entrega && !empty($entrega['documento_pdf'])) {
            @unlink(__DIR__ . '/../../public/uploads/entregas/' . $entrega['documento_pdf']);
        }

        $descripcion = $entrega
            ? sprintf(
                'Eliminó la entrega: OCE %s — Proforma %s — %s',
                $entrega['n_oce_interna'] ?: '(sin número)',
                $entrega['n_proforma'] ?: '(sin número)',
                $entrega['proveedor_nombre'] ?? 'proveedor no especificado'
              )
            : 'Eliminó la entrega';

        EntregaFactura::registrarHistorial($id, Auth::id(), $descripcion);
        EntregaFactura::delete($id);

        $this->flash('success', 'Entrega eliminada.');
        $this->redirect('/entregas');
    }

    private function collectFormData(): array
    {
        $campos = ['factura_id', 'fecha_entrega_dueno', 'fecha_solicitud_revision_pago', 'comentario'];

        $data = [];
        foreach ($campos as $campo) {
            $valor = $this->input($campo, null);
            $data[$campo] = ($valor === '' || $valor === null) ? null : $valor;
        }

        $data['factura_id'] = (int) $data['factura_id'];

        return $data;
    }
}
