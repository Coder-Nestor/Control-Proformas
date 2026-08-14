<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\Gestion;
use App\Models\Proveedor;
use App\Models\Proforma;
use App\Models\Trabajo;

class GestionController extends Controller
{
    private const POR_PAGINA = 10;

    public function index(): void
    {
        $filtros = [
            'proveedor_id' => $this->input('proveedor_id', ''),
            'sin_asignar'  => $this->input('sin_asignar', ''),
            'buscar'       => $this->input('buscar', ''),
        ];

        $paginaActual = max(1, (int) $this->input('pagina', 1));
        $porPagina    = self::POR_PAGINA;

        $totalRegistros = Gestion::contarConDetalle($filtros);
        $totalPaginas   = max(1, (int) ceil($totalRegistros / $porPagina));

        if ($paginaActual > $totalPaginas) {
            $paginaActual = $totalPaginas;
        }

        $offset = ($paginaActual - 1) * $porPagina;

        $gestiones = Gestion::allConDetalle($filtros, $porPagina, $offset);

        // Se cargan los trabajos de todas las gestiones listadas en una sola consulta
        // (evita N+1) y se agrupan por gestión para pintarlos en la tabla.
        $trabajosPorGestion = [];
        foreach ($gestiones as $g) {
            $trabajosPorGestion[$g['id']] = Trabajo::deGestion((int) $g['id']);
        }

        $this->view('gestiones/index', [
            'gestiones'          => $gestiones,
            'trabajosPorGestion' => $trabajosPorGestion,
            'proveedores'        => Proveedor::activos(),
            'filtros'            => $filtros,
            'paginacion'         => [
                'pagina_actual'   => $paginaActual,
                'total_paginas'   => $totalPaginas,
                'total_registros' => $totalRegistros,
                'por_pagina'      => $porPagina,
            ],
        ]);
    }

    public function create(): void
    {
        $this->view('gestiones/form', [
            'gestion'   => null,
            'trabajos'  => [],
            'proveedores' => Proveedor::activos(),
            'proformas'   => Proforma::paraSelect(),
        ]);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->collectFormData();

        if (!empty($data['n_cotizacion']) && Gestion::numeroCotizacionExiste($data['n_cotizacion'])) {
            $this->flash('error', 'Ese número de cotización ya está registrado en otra gestión. Elige uno distinto.');
            $this->view('gestiones/form', [
                'gestion'    => $data,
                'trabajos'   => $this->collectTrabajos(),
                'proveedores'=> Proveedor::activos(),
                'proformas'  => Proforma::paraSelect(),
            ]);
            return;
        }

        $data['documento_pdf'] = $this->handleUpload('documento_pdf', 'gestiones');
        $data['creado_por'] = Auth::id();

        $id = Gestion::insert($data);
        Trabajo::reemplazarDeGestion($id, $this->collectTrabajos());
        Gestion::registrarHistorial($id, Auth::id(), 'Creó la gestión');

        $this->flash('success', 'Gestión registrada correctamente.');
        $this->redirect('/gestiones/' . $id);
    }

    public function show(array $params): void
    {
        $id = (int) $params['id'];
        $gestion = Gestion::findConDetalle($id);

        if (!$gestion) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }

        $this->view('gestiones/show', [
            'gestion'   => $gestion,
            'trabajos'  => Trabajo::deGestion($id),
            'historial' => Gestion::historialDe($id),
        ]);
    }

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $gestion = Gestion::find($id);

        if (!$gestion) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }

        $this->view('gestiones/form', [
            'gestion'     => $gestion,
            'trabajos'    => Trabajo::deGestion($id),
            'proveedores' => Proveedor::activos(),
            'proformas'   => Proforma::paraSelect(),
        ]);
    }

    public function update(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $actual = Gestion::find($id);

        $data = $this->collectFormData();

        if (!empty($data['n_cotizacion']) && Gestion::numeroCotizacionExiste($data['n_cotizacion'], $id)) {
            $this->flash('error', 'Ese número de cotización ya está registrado en otra gestión. Elige uno distinto.');
            $this->view('gestiones/form', [
                'gestion'    => array_merge($actual ?? [], $data),
                'trabajos'   => $this->collectTrabajos(),
                'proveedores'=> Proveedor::activos(),
                'proformas'  => Proforma::paraSelect(),
            ]);
            return;
        }

        $data['documento_pdf'] = $this->handleUpload('documento_pdf', 'gestiones', $actual['documento_pdf'] ?? null);

        Gestion::update($id, $data);
        Trabajo::reemplazarDeGestion($id, $this->collectTrabajos());
        Gestion::registrarHistorial($id, Auth::id(), 'Actualizó los datos de la gestión');

        $this->flash('success', 'Gestión actualizada correctamente.');
        $this->redirect('/gestiones/' . $id);
    }

    public function destroy(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];

        $gestion = Gestion::find($id);
        if ($gestion && !empty($gestion['documento_pdf'])) {
            @unlink(__DIR__ . '/../../public/uploads/gestiones/' . $gestion['documento_pdf']);
        }

        // Los trabajos de esta gestión se eliminan en cascada (ON DELETE CASCADE).
        Gestion::delete($id);

        $this->flash('success', 'Gestión eliminada.');
        $this->redirect('/gestiones');
    }

    /** Campos del encabezado de la Gestión (la cotización en sí, sin los trabajos). */
    private function collectFormData(): array
    {
        $campos = [
            'proveedor_id', 'solicitado_por', 'aprobado_por',
            'fecha_aprobacion_trabajo', 'fecha_finalizacion_trabajo',
            'n_cotizacion', 'fecha_revision_cotizacion',
            'comentario',
        ];

        $data = [];
        foreach ($campos as $campo) {
            $valor = $this->input($campo, null);
            $data[$campo] = ($valor === '' || $valor === null) ? null : $valor;
        }

        $data['proveedor_id'] = $data['proveedor_id'] !== null ? (int) $data['proveedor_id'] : null;

        return $data;
    }

    /**
     * Recoge el arreglo de trabajos enviado por el formulario:
     *   trabajo_descripcion[] , trabajo_valor[] , trabajo_proforma_id[]
     * Los tres arreglos llegan alineados por índice (misma posición = mismo trabajo).
     */
    private function collectTrabajos(): array
    {
        $descripciones = $_POST['trabajo_descripcion'] ?? [];
        $valores       = $_POST['trabajo_valor'] ?? [];
        $proformaIds   = $_POST['trabajo_proforma_id'] ?? [];

        $trabajos = [];
        foreach ($descripciones as $i => $descripcion) {
            $descripcion = trim($descripcion);
            if ($descripcion === '') {
                continue;
            }

            $valor = trim($valores[$i] ?? '');
            $proformaId = trim($proformaIds[$i] ?? '');

            $trabajos[] = [
                'descripcion' => $descripcion,
                'valor'       => $valor !== '' ? $valor : null,
                'proforma_id' => $proformaId !== '' ? (int) $proformaId : null,
            ];
        }

        return $trabajos;
    }
}