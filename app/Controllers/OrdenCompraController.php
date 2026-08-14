<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\OrdenCompra;
use App\Models\Proforma;
use App\Models\Factura;  

class OrdenCompraController extends Controller
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

        $totalRegistros = OrdenCompra::contarConDetalle($filtros);
        $totalPaginas   = max(1, (int) ceil($totalRegistros / $porPagina));

        if ($paginaActual > $totalPaginas) {
            $paginaActual = $totalPaginas;
        }

        $offset = ($paginaActual - 1) * $porPagina;

        $this->view('ordenes/index', [
            'ordenes'    => OrdenCompra::allConDetalle($filtros, $porPagina, $offset),
            'estados'    => OrdenCompra::ESTADOS,
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
        $selectedProformaId = (int) $this->input('proforma_id', 0);

        $this->view('ordenes/form', [
            'oc'                  => null,
            'proformas'           => Proforma::sinOrdenDeCompra(),
            'estados'             => OrdenCompra::ESTADOS,
            'selectedProformaId'  => $selectedProformaId,
        ]);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->collectFormData();
        $data['documento_pdf'] = $this->handleUpload('documento_pdf', 'ordenes_compra');
        $data['creado_por'] = Auth::id();

        $id = OrdenCompra::insert($data);
        OrdenCompra::registrarHistorial($id, Auth::id(), 'Creó la orden de compra');

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

        $this->view('ordenes/show', [
            'oc'        => $oc,
            'factura'   => Factura::findPorOrdenCompra($id),
            'historial' => OrdenCompra::historialDe($id),
        ]);
    }

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $oc = OrdenCompra::find($id);

        if (!$oc) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }

        $this->view('ordenes/form', [
            'oc'        => $oc,
            'proformas' => Proforma::sinOrdenDeCompra($oc['proforma_id']),
            'estados'   => OrdenCompra::ESTADOS,
        ]);
    }

    public function update(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $actual = OrdenCompra::find($id);

        $data = $this->collectFormData();
        $data['documento_pdf'] = $this->handleUpload('documento_pdf', 'ordenes_compra', $actual['documento_pdf'] ?? null);

        OrdenCompra::update($id, $data);
        OrdenCompra::registrarHistorial($id, Auth::id(), 'Actualizó los datos de la orden de compra');

        $this->flash('success', 'Orden de compra actualizada correctamente.');
        $this->redirect('/ordenes/' . $id);
    }

    public function destroy(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];

        $oc = OrdenCompra::find($id);
        if ($oc && !empty($oc['documento_pdf'])) {
            @unlink(__DIR__ . '/../../public/uploads/ordenes_compra/' . $oc['documento_pdf']);
        }

        OrdenCompra::delete($id);

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
        if (!array_key_exists($data['estado'], OrdenCompra::ESTADOS)) {
            $data['estado'] = 'pendiente';
        }

        return $data;
    }
}