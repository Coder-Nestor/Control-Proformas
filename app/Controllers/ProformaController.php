<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\Proforma;
use App\Models\Proveedor;
use App\Models\Trabajo;
use App\Models\Gestion;
use App\Models\OrdenCompra;
use App\Models\Area;

class ProformaController extends Controller
{
    private const POR_PAGINA = 10;

    public function index(): void
    {
        $filtros = [
            'proveedor_id' => $this->input('proveedor_id', ''),
            'sin_oc'       => $this->input('sin_oc', ''),
            'buscar'       => $this->input('buscar', ''),
        ];

        $paginaActual = max(1, (int) $this->input('pagina', 1));
        $porPagina    = self::POR_PAGINA;

        $totalRegistros = Proforma::contarConDetalle($filtros);
        $totalPaginas   = max(1, (int) ceil($totalRegistros / $porPagina));

        if ($paginaActual > $totalPaginas) {
            $paginaActual = $totalPaginas;
        }

        $offset = ($paginaActual - 1) * $porPagina;

        $proformas = Proforma::allConDetalle($filtros, $porPagina, $offset);

        $this->view('proformas/index', [
            'proformas'   => $proformas,
            'proveedores' => Proveedor::activos(),
            'filtros'     => $filtros,
            'paginacion'  => [
                'pagina_actual'   => $paginaActual,
                'total_paginas'   => $totalPaginas,
                'total_registros' => $totalRegistros,
                'por_pagina'      => $porPagina,
            ],
        ]);
    }

    public function create(): void
    {
        $prefill = null;
        $trabajoId = $this->input('trabajo_id', null);

        if ($trabajoId !== null && $trabajoId !== '') {
            $trabajo = Trabajo::findConGestion((int) $trabajoId);
            if ($trabajo) {
                $prefill = [
                    'trabajo_id'   => $trabajo['id'],
                    'n_cotizacion' => $trabajo['n_cotizacion'],
                    'proveedor_id' => $trabajo['proveedor_id'],
                ];
            }
        }

        $this->view('proformas/form', [
            'proforma'    => null,
            'prefill'     => $prefill,
            'proveedores' => Proveedor::activos(),
            'areas'       => Area::activas(),
        ]);
    }

    /** Endpoint AJAX: busca trabajos por N° de Cotización, para el formulario de Proforma. */
    public function buscarTrabajosPorCotizacion(): void
    {
        $numero = $this->input('numero', '');
        $trabajos = [];

        if ($numero !== '') {
            $trabajos = Trabajo::porNumeroCotizacion($numero);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'trabajos' => $trabajos,
        ]);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->collectFormData();
        $data['documento_pdf'] = $this->handleUpload('documento_pdf', 'proformas');
        $data['creado_por'] = Auth::id();

        $id = Proforma::insert($data);
        Proforma::registrarHistorial($id, Auth::id(), 'Creó la proforma');

        $this->vincularTrabajo($id, $data);

        $this->flash('success', 'Proforma registrada correctamente.');
        $this->redirect('/proformas/' . $id);
    }

    public function show(array $params): void
    {
        $id = (int) $params['id'];
        $proforma = Proforma::findConDetalle($id);

        if (!$proforma) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }

        $this->view('proformas/show', [
            'proforma'  => $proforma,
            'trabajos'  => Trabajo::deProforma($id),
            'oc'        => OrdenCompra::findPorProforma($id),
            'historial' => Proforma::historialDe($id),
        ]);
    }

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $proforma = Proforma::find($id);

        if (!$proforma) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }

        // Si esta proforma tiene exactamente un trabajo vinculado (el caso normal,
        // ya sea de una cotización real o de una mensualidad escrita a mano),
        // se precargan sus datos para que el formulario no se vea vacío al editar.
        $trabajos = Trabajo::deProforma($id);
        if (count($trabajos) === 1) {
            $t = $trabajos[0];
            $proforma['trabajo']          = $t['descripcion'];
            $proforma['valor_cotizacion'] = $t['valor'];
            $proforma['trabajo_id']       = $t['id'];
            $proforma['n_cotizacion']     = $t['n_cotizacion'];
        }

        $this->view('proformas/form', [
            'proforma'    => $proforma,
            'proveedores' => Proveedor::activos(),
            'areas'       => Area::activas(),
        ]);
    }

    public function update(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $actual = Proforma::find($id);

        $data = $this->collectFormData();
        $data['documento_pdf'] = $this->handleUpload('documento_pdf', 'proformas', $actual['documento_pdf'] ?? null);

        Proforma::update($id, $data);
        Proforma::registrarHistorial($id, Auth::id(), 'Actualizó los datos de la proforma');

        $this->vincularTrabajo($id, $data, true);

        $this->flash('success', 'Proforma actualizada correctamente.');
        $this->redirect('/proformas/' . $id);
    }

    public function destroy(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];

        $proforma = Proforma::find($id);
        if ($proforma && !empty($proforma['documento_pdf'])) {
            @unlink(__DIR__ . '/../../public/uploads/proformas/' . $proforma['documento_pdf']);
        }

        // Los trabajos que apuntaban a esta proforma quedan "sin asignar" (ON DELETE SET NULL).
        // La OC asociada, si existe, se elimina en cascada (ON DELETE CASCADE).
        Proforma::delete($id);

        $this->flash('success', 'Proforma eliminada. Los trabajos asociados quedaron sin asignar.');
        $this->redirect('/proformas');
    }

    /**
     * Vincula un Trabajo a la Proforma recién creada/editada, en cualquiera
     * de los 2 flujos del formulario:
     *
     *  A) Con N° de Cotización: el usuario eligió un trabajo YA EXISTENTE
     *     (viene en $_POST['trabajo_id']) — solo hace falta asignarlo.
     *
     *  B) Mensualidad (sin cotización): el usuario escribió el trabajo a
     *     mano. Como no existe ninguna Gestión/Trabajo detrás, hay que
     *     CREARLOS aquí mismo para que la proforma sí tenga un trabajo real
     *     vinculado (antes esto no pasaba y por eso quedaba en "0 trabajos").
     */
    private function vincularTrabajo(int $proformaId, array $dataProforma, bool $esEdicion = false): void
    {
        $trabajoId = $this->input('trabajo_id', null);
        $nCotizacion = $this->input('n_cotizacion', null);
        $descripcion = $this->input('trabajo', null);
        $valor = $this->input('valor_cotizacion', null);
        $valor = ($valor === '' || $valor === null) ? null : $valor;

        // Caso A: ya eligió un trabajo existente desde la búsqueda por cotización.
        if ($trabajoId !== null && $trabajoId !== '') {
            if ($esEdicion) {
                Trabajo::desasignarPorProforma($proformaId);
            }
            Trabajo::asignarProforma((int) $trabajoId, $proformaId);
            return;
        }

        // Caso B: mensualidad — sin cotización, pero con una descripción escrita a mano.
        if (empty($nCotizacion) && !empty($descripcion)) {
            $trabajosActuales = $esEdicion ? Trabajo::deProforma($proformaId) : [];

            if (!empty($trabajosActuales)) {
                // Ya existía un trabajo "mensualidad" para esta proforma: se actualiza,
                // en vez de crear uno nuevo cada vez que se guarda.
                Trabajo::update($trabajosActuales[0]['id'], [
                    'descripcion' => $descripcion,
                    'valor'       => $valor,
                ]);
                return;
            }

            $gestionId = Gestion::insert([
                'proveedor_id' => $dataProforma['proveedor_id'] ?? null,
                'solicitado_por' => $dataProforma['solicitado_por'] ?? null,
                'n_cotizacion' => null,
                'comentario' => 'Gestión generada automáticamente para la proforma de mensualidad.',
                'creado_por' => Auth::id(),
            ]);
            Gestion::registrarHistorial($gestionId, Auth::id(), 'Creada automáticamente desde una proforma de mensualidad');

            Trabajo::reemplazarDeGestion($gestionId, [[
                'descripcion' => $descripcion,
                'valor'       => $valor,
                'proforma_id' => $proformaId,
            ]]);
        }
    }

    /**
     * Campos del encabezado de la Proforma. OJO: la tabla `proformas` NO tiene
     * columnas de n_cotizacion/trabajo/valor_cotizacion — esa información vive
     * en la tabla `trabajos`, vinculada por trabajo_id (ver vincularTrabajo()).
     */
    private function collectFormData(): array
    {
        $campos = [
            'proveedor_id', 'fecha_solicitud', 'solicitado_por',
            'n_proforma', 'valor_proforma', 'fecha_revision_proforma', 'comentario',
        ];

        $data = [];
        foreach ($campos as $campo) {
            $valor = $this->input($campo, null);
            $data[$campo] = ($valor === '' || $valor === null) ? null : $valor;
        }

        $data['proveedor_id'] = $data['proveedor_id'] !== null ? (int) $data['proveedor_id'] : null;

        return $data;
    }
}