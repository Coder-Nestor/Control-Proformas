<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\Gestion;
use App\Models\Proveedor;
use App\Models\Proforma;
use App\Models\Trabajo;
use App\Models\Area;
use App\Models\OrdenCompra;
use App\Models\Factura;
use App\Models\EntregaFactura;

class GestionController extends Controller
{
    private const POR_PAGINA = 15;

    public function index(): void
    {
        $filtros = [
            'proveedor_id' => $this->input('proveedor_id', ''),
            'sin_asignar'  => $this->input('sin_asignar', ''),
            'buscar'       => $this->input('buscar', ''),
        ];
        $paginaActual = max(1, (int) $this->input('pagina', 1));
        $porPagina = self::POR_PAGINA;
        $totalRegistros = Gestion::contarConDetalle($filtros);
        $totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
        if ($paginaActual > $totalPaginas) $paginaActual = $totalPaginas;
        $offset = ($paginaActual - 1) * $porPagina;
        $gestiones = Gestion::allConDetalle($filtros, $porPagina, $offset);

        $trabajosPorGestion = [];
        foreach ($gestiones as $g) {
            $trabajosPorGestion[$g['id']] = Trabajo::deGestion((int) $g['id']);
        }

        $this->view('gestiones/index', [
            'gestiones' => $gestiones,
            'trabajosPorGestion' => $trabajosPorGestion,
            'proveedores' => Proveedor::activos(),
            'proveedoresHabilitadosIds' => array_column(Proveedor::habilitadosParaProforma(), 'id'),
            'filtros' => $filtros,
            'paginacion' => ['pagina_actual' => $paginaActual, 'total_paginas' => $totalPaginas, 'total_registros' => $totalRegistros, 'por_pagina' => $porPagina],
        ]);
    }

    public function create(): void
    {
        $this->view('gestiones/form', [
            'gestion' => null,
            'trabajos' => [],
            'proveedores' => Proveedor::activos(),
            'proformas' => Proforma::paraSelect(),
            'areas' => Area::activas(),
        ]);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->collectFormData();

        if (!empty($data['n_cotizacion']) && Gestion::numeroCotizacionExiste($data['n_cotizacion'])) {
            $this->flash('error', 'Ese número de cotización ya está registrado en otra gestión. Elige uno distinto.');
            $this->view('gestiones/form', [
                'gestion' => $data,
                'trabajos' => $this->collectTrabajos(),
                'proveedores' => Proveedor::activos(),
                'proformas' => Proforma::paraSelect(),
                'areas' => Area::activas(),
            ]);
            return;
        }

        $data['documento_pdf'] = $this->handleUpload('documento_pdf', 'gestiones');
        $data['creado_por'] = Auth::id();

        $id = Gestion::insert($data);
        Trabajo::reemplazarDeGestion($id, $this->collectTrabajos());

        $gestionCreada = Gestion::findConDetalle($id);
        $descripcionCrear = $gestionCreada
            ? sprintf('Creó la gestión: Cotización %s — %s', $gestionCreada['n_cotizacion'] ?: '(sin número)', $gestionCreada['proveedor_nombre'] ?? 'proveedor no especificado')
            : 'Creó la gestión';
        Gestion::registrarHistorial($id, Auth::id(), $descripcionCrear);

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
        $this->view('gestiones/show', ['gestion' => $gestion, 'trabajos' => Trabajo::deGestion($id), 'historial' => Gestion::historialDe($id)]);
    }

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $gestion = Gestion::findConDetalle($id);
        if (!$gestion) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }
        $this->view('gestiones/form', [
            'gestion' => $gestion,
            'trabajos' => Trabajo::deGestion($id),
            'proveedores' => Proveedor::activos(),
            'proformas' => Proforma::paraSelect(),
            'areas' => Area::activas(),
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
                'gestion' => array_merge($actual ?? [], $data),
                'trabajos' => $this->collectTrabajos(),
                'proveedores' => Proveedor::activos(),
                'proformas' => Proforma::paraSelect(),
                'areas' => Area::activas(),
            ]);
            return;
        }

        $eliminarPdf = $this->input('eliminar_pdf', '0') === '1';
        if ($eliminarPdf && empty($_FILES['documento_pdf']['name'])) {
            if (!empty($actual['documento_pdf'])) {
                @unlink(__DIR__ . '/../../public/uploads/gestiones/' . $actual['documento_pdf']);
            }
            $data['documento_pdf'] = null;
        } else {
            $data['documento_pdf'] = $this->handleUpload('documento_pdf', 'gestiones', $actual['documento_pdf'] ?? null);
        }

        Gestion::update($id, $data);
        Trabajo::reemplazarDeGestion($id, $this->collectTrabajos());

        $gestionActualizada = Gestion::findConDetalle($id);
        $descripcionActualizar = $gestionActualizada
            ? sprintf('Actualizó los datos de la gestión: Cotización %s — %s', $gestionActualizada['n_cotizacion'] ?: '(sin número)', $gestionActualizada['proveedor_nombre'] ?? 'proveedor no especificado')
            : 'Actualizó los datos de la gestión';
        Gestion::registrarHistorial($id, Auth::id(), $descripcionActualizar);

        $this->flash('success', 'Gestión actualizada correctamente.');
        $this->redirect('/gestiones/' . $id);
    }

    public function destroy(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];

        if ($this->algunTrabajoLlegoAEntrega($id) && !Auth::hasRole(['administrador'])) {
            $this->flash('error', 'Esta gestión tiene al menos un trabajo (cotización o mensualidad) que ya llegó hasta Entrega de factura — solo un Administrador puede eliminarla.');
            $this->redirect('/gestiones/' . $id);
        }

        $gestion = Gestion::findConDetalle($id);
        if ($gestion && !empty($gestion['documento_pdf'])) {
            @unlink(__DIR__ . '/../../public/uploads/gestiones/' . $gestion['documento_pdf']);
        }

        $descripcion = $gestion
            ? sprintf('Eliminó la gestión: Cotización %s — %s', $gestion['n_cotizacion'] ?: '(sin número)', $gestion['proveedor_nombre'] ?? 'proveedor no especificado')
            : 'Eliminó la gestión';

        Gestion::registrarHistorial($id, Auth::id(), $descripcion);
        Gestion::softDelete($id, Auth::id());

        $this->flash('success', 'Gestión eliminada.');
        $this->redirect('/gestiones');
    }

    /**
     * Revisa si CUALQUIERA de los trabajos de esta gestión (sea de cotización
     * real o de mensualidad) ya llegó hasta tener una Entrega de factura
     * registrada — Proforma -> Orden de Compra -> Factura -> Entrega, todos
     * activos. Si es así, la gestión queda protegida: eliminarla dejaría
     * huérfano un historial que ya se completó hasta el final del proceso.
     */
    private function algunTrabajoLlegoAEntrega(int $gestionId): bool
    {
        $trabajos = Trabajo::deGestion($gestionId);
        foreach ($trabajos as $trabajo) {
            if (empty($trabajo['proforma_id'])) {
                continue;
            }
            $oc = OrdenCompra::findPorProforma((int) $trabajo['proforma_id']);
            if (!$oc) {
                continue;
            }
            $factura = Factura::findPorOrdenCompra((int) $oc['id']);
            if (!$factura) {
                continue;
            }
            if (EntregaFactura::findPorFactura((int) $factura['id'])) {
                return true;
            }
        }
        return false;
    }

    private function collectFormData(): array
    {
        $campos = ['proveedor_id', 'solicitado_por', 'aprobado_por', 'fecha_aprobacion_trabajo', 'fecha_finalizacion_trabajo', 'n_cotizacion', 'fecha_revision_cotizacion', 'comentario'];
        $data = [];
        foreach ($campos as $campo) {
            $valor = $this->input($campo, null);
            $data[$campo] = ($valor === '' || $valor === null) ? null : $valor;
        }
        $data['proveedor_id'] = $data['proveedor_id'] !== null ? (int) $data['proveedor_id'] : null;

        // En modo Mensualidad (sin N° de cotización) "Aprobado por" no aplica
        // — se ignora aunque llegue algo en la petición, sin depender de que
        // el formulario lo haya escondido correctamente.
        if (empty($data['n_cotizacion'])) {
            $data['aprobado_por'] = null;
        }

        return $data;
    }

    private function collectTrabajos(): array
    {
        $descripciones = $_POST['trabajo_descripcion'] ?? [];
        $valores = $_POST['trabajo_valor'] ?? [];
        $proformaIds = $_POST['trabajo_proforma_id'] ?? [];
        $trabajos = [];
        foreach ($descripciones as $i => $descripcion) {
            $descripcion = trim($descripcion);
            if ($descripcion === '') continue;
            $valor = trim($valores[$i] ?? '');
            $proformaId = trim($proformaIds[$i] ?? '');
            $trabajos[] = ['descripcion' => $descripcion, 'valor' => $valor !== '' ? $valor : null, 'proforma_id' => $proformaId !== '' ? (int) $proformaId : null];
        }
        return $trabajos;
    }
}