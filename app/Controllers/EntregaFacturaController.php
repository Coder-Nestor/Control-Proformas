<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\EntregaFactura;
use App\Models\Factura;

class EntregaFacturaController extends Controller
{
    private const POR_PAGINA = 10;

    public function index(): void
    {
        $filtros = [
            'buscar' => $this->input('buscar', ''),
        ];

        $paginaActual = max(1, (int) $this->input('pagina', 1));
        $porPagina    = self::POR_PAGINA;

        $totalRegistros = EntregaFactura::contarConDetalle($filtros);
        $totalPaginas   = max(1, (int) ceil($totalRegistros / $porPagina));

        if ($paginaActual > $totalPaginas) {
            $paginaActual = $totalPaginas;
        }

        $offset = ($paginaActual - 1) * $porPagina;

        $this->view('entregas/index', [
            'entregas'   => EntregaFactura::allConDetalle($filtros, $porPagina, $offset),
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
        EntregaFactura::registrarHistorial($id, Auth::id(), 'Creó el registro de entrega');

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
        $data['documento_pdf'] = $this->handleUpload('documento_pdf', 'entregas', $actual['documento_pdf'] ?? null);

        EntregaFactura::update($id, $data);
        EntregaFactura::registrarHistorial($id, Auth::id(), 'Actualizó los datos de la entrega');

        $this->flash('success', 'Entrega actualizada correctamente.');
        $this->redirect('/entregas/' . $id);
    }

    public function destroy(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];

        $entrega = EntregaFactura::find($id);
        if ($entrega && !empty($entrega['documento_pdf'])) {
            @unlink(__DIR__ . '/../../public/uploads/entregas/' . $entrega['documento_pdf']);
        }

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