<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\Factura;
use App\Models\OrdenCompra;
use App\Models\EntregaFactura;

class FacturaController extends Controller
{
    public function index(): void
    {
        $filtros = ['estado' => $this->input('estado', ''), 'buscar' => $this->input('buscar', '')];
        $this->view('facturas/index', ['facturas' => Factura::allConDetalle($filtros), 'estados' => Factura::ESTADOS, 'filtros' => $filtros]);
    }

    public function create(): void
    {
        $this->view('facturas/form', ['factura' => null, 'ordenesCompra' => OrdenCompra::sinFactura(), 'estados' => Factura::ESTADOS]);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->collectFormData();
        $data['documento_pdf'] = $this->handleUpload('documento_pdf', 'facturas');
        $data['creado_por'] = Auth::id();

        $id = Factura::insert($data);

        $facturaCreada = Factura::findConDetalle($id);
        $descripcionCrear = $facturaCreada
            ? sprintf('Creó la factura: N° %s — OCE %s — %s', $facturaCreada['n_factura'] ?: '(sin número)', $facturaCreada['n_oce_interna'] ?: '(sin número)', $facturaCreada['proveedor_nombre'] ?? 'proveedor no especificado')
            : 'Creó la factura';
        Factura::registrarHistorial($id, Auth::id(), $descripcionCrear);

        $this->flash('success', 'Factura registrada correctamente.');
        $this->redirect('/facturas/' . $id);
    }

    public function show(array $params): void
    {
        $id = (int) $params['id'];
        $factura = Factura::findConDetalle($id);
        if (!$factura) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }
        $this->view('facturas/show', ['factura' => $factura, 'entrega' => EntregaFactura::findPorFactura($id), 'historial' => Factura::historialDe($id)]);
    }

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $factura = Factura::findConDetalle($id);
        if (!$factura) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }

        if ($this->estaCerradaParaEdicion($factura)) {
            $this->flash('error', 'Esta factura ya está marcada como "Correcta" y queda cerrada — solo un Administrador puede editarla.');
            $this->redirect('/facturas/' . $id);
        }

        $this->view('facturas/form', ['factura' => $factura, 'ordenesCompra' => OrdenCompra::sinFactura($factura['orden_compra_id']), 'estados' => Factura::ESTADOS]);
    }

    public function update(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $actual = Factura::find($id);

        if (!$actual) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }

        if ($this->estaCerradaParaEdicion($actual)) {
            $this->flash('error', 'Esta factura ya está marcada como "Correcta" y queda cerrada — solo un Administrador puede editarla.');
            $this->redirect('/facturas/' . $id);
        }

        $data = $this->collectFormData();

        $eliminarPdf = $this->input('eliminar_pdf', '0') === '1';
        if ($eliminarPdf && empty($_FILES['documento_pdf']['name'])) {
            if (!empty($actual['documento_pdf'])) {
                @unlink(__DIR__ . '/../../public/uploads/facturas/' . $actual['documento_pdf']);
            }
            $data['documento_pdf'] = null;
        } else {
            $data['documento_pdf'] = $this->handleUpload('documento_pdf', 'facturas', $actual['documento_pdf'] ?? null);
        }

        Factura::update($id, $data);

        $facturaActualizada = Factura::findConDetalle($id);
        $descripcionActualizar = $facturaActualizada
            ? sprintf('Actualizó los datos de la factura: N° %s — OCE %s — %s', $facturaActualizada['n_factura'] ?: '(sin número)', $facturaActualizada['n_oce_interna'] ?: '(sin número)', $facturaActualizada['proveedor_nombre'] ?? 'proveedor no especificado')
            : 'Actualizó los datos de la factura';
        Factura::registrarHistorial($id, Auth::id(), $descripcionActualizar);

        $this->flash('success', 'Factura actualizada correctamente.');
        $this->redirect('/facturas/' . $id);
    }

    public function destroy(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];

        $factura = Factura::findConDetalle($id);

        if ($factura && $this->estaCerradaParaEdicion($factura)) {
            $this->flash('error', 'Esta factura ya está marcada como "Correcta" y queda cerrada — solo un Administrador puede eliminarla.');
            $this->redirect('/facturas/' . $id);
        }

        if ($factura && !empty($factura['documento_pdf'])) {
            @unlink(__DIR__ . '/../../public/uploads/facturas/' . $factura['documento_pdf']);
        }

        $entrega = EntregaFactura::findPorFactura($id);
        if ($entrega) {
            EntregaFactura::registrarHistorial($entrega['id'], Auth::id(), 'Eliminada automáticamente (cascada al eliminar la factura)');
            EntregaFactura::softDelete($entrega['id'], Auth::id());
        }

        $descripcion = $factura
            ? sprintf('Eliminó la factura: N° %s — OCE %s — %s', $factura['n_factura'] ?: '(sin número)', $factura['n_oce_interna'] ?: '(sin número)', $factura['proveedor_nombre'] ?? 'proveedor no especificado')
            : 'Eliminó la factura';

        Factura::registrarHistorial($id, Auth::id(), $descripcion);
        Factura::softDelete($id, Auth::id());

        $this->flash('success', 'Factura eliminada.');
        $this->redirect('/facturas');
    }

    /**
     * Una factura marcada como "Correcta" queda completamente cerrada —
     * nadie que no sea Administrador puede editarla ni eliminarla, ni
     * siquiera para cambiarle el estado a Pendiente/Con problema.
     */
    private function estaCerradaParaEdicion(array $factura): bool
    {
        return ($factura['estado'] ?? null) === 'correcta' && !Auth::hasRole(['administrador']);
    }

    private function collectFormData(): array
    {
        $campos = ['orden_compra_id', 'n_factura', 'fecha_entrega_factura', 'estado', 'comentario'];
        $data = [];
        foreach ($campos as $campo) {
            $valor = $this->input($campo, null);
            $data[$campo] = ($valor === '' || $valor === null) ? null : $valor;
        }
        $data['orden_compra_id'] = (int) $data['orden_compra_id'];
        if (!array_key_exists($data['estado'], Factura::ESTADOS)) $data['estado'] = 'pendiente';
        return $data;
    }
}