<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\Factura;
use App\Models\OrdenCompra;
use App\Models\EntregaFactura;

class FacturaController extends Controller
{
    private const POR_PAGINA = 10;

    public function index(): void
    {
        $filtros = [
            'estado' => $this->input('estado', ''),
            'buscar' => $this->input('buscar', ''),
        ];

        $paginaActual = max(1, (int) $this->input('pagina', 1));
        $porPagina    = self::POR_PAGINA;

        $totalRegistros = Factura::contarConDetalle($filtros);
        $totalPaginas   = max(1, (int) ceil($totalRegistros / $porPagina));

        if ($paginaActual > $totalPaginas) {
            $paginaActual = $totalPaginas;
        }

        $offset = ($paginaActual - 1) * $porPagina;

        $this->view('facturas/index', [
            'facturas'   => Factura::allConDetalle($filtros, $porPagina, $offset),
            'estados'    => Factura::ESTADOS,
            'filtros'    => $filtros,
            'paginacion' => [
                'pagina_actual'   => $paginaActual,
                'total_paginas'   => $totalPaginas,
                'total_registros' => $totalRegistros,
                'por_pagina'      => $porPagina,
            ],
        ]);
    }

    public function create(): void
    {
        $this->view('facturas/form', [
            'factura'          => null,
            'ordenesCompra'    => OrdenCompra::sinFactura(),
            'estados'          => Factura::ESTADOS,
        ]);
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
            ? sprintf(
                'Creó la factura: N° %s — OCE %s — %s',
                $facturaCreada['n_factura'] ?: '(sin número)',
                $facturaCreada['n_oce_interna'] ?: '(sin número)',
                $facturaCreada['proveedor_nombre'] ?? 'proveedor no especificado'
              )
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

        $this->view('facturas/show', [
            'factura'   => $factura,
            'entrega'   => EntregaFactura::findPorFactura($id),
            'historial' => Factura::historialDe($id),
        ]);
    }

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $factura = Factura::find($id);

        if (!$factura) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }

        $this->view('facturas/form', [
            'factura'       => $factura,
            'ordenesCompra' => OrdenCompra::sinFactura($factura['orden_compra_id']),
            'estados'       => Factura::ESTADOS,
        ]);
    }

    public function update(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $actual = Factura::find($id);

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
            ? sprintf(
                'Actualizó los datos de la factura: N° %s — OCE %s — %s',
                $facturaActualizada['n_factura'] ?: '(sin número)',
                $facturaActualizada['n_oce_interna'] ?: '(sin número)',
                $facturaActualizada['proveedor_nombre'] ?? 'proveedor no especificado'
              )
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
        if ($factura && !empty($factura['documento_pdf'])) {
            @unlink(__DIR__ . '/../../public/uploads/facturas/' . $factura['documento_pdf']);
        }

        $descripcion = $factura
            ? sprintf(
                'Eliminó la factura: N° %s — OCE %s — %s',
                $factura['n_factura'] ?: '(sin número)',
                $factura['n_oce_interna'] ?: '(sin número)',
                $factura['proveedor_nombre'] ?? 'proveedor no especificado'
              )
            : 'Eliminó la factura';

        Factura::registrarHistorial($id, Auth::id(), $descripcion);
        Factura::delete($id);

        $this->flash('success', 'Factura eliminada.');
        $this->redirect('/facturas');
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
        if (!array_key_exists($data['estado'], Factura::ESTADOS)) {
            $data['estado'] = 'pendiente';
        }

        return $data;
    }
}