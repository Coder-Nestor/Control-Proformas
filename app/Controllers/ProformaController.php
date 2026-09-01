<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\Proforma;
use App\Models\Proveedor;
use App\Models\Trabajo;
use App\Models\Gestion;
use App\Models\OrdenCompra;
use App\Models\Factura;
use App\Models\EntregaFactura;
use App\Models\Area;

class ProformaController extends Controller
{
    private const POR_PAGINA = 15;

    public function index(): void
    {
        $filtros = ['proveedor_id' => $this->input('proveedor_id', ''), 'sin_oc' => $this->input('sin_oc', ''), 'buscar' => $this->input('buscar', '')];
        $paginaActual = max(1, (int) $this->input('pagina', 1));
        $porPagina = self::POR_PAGINA;
        $totalRegistros = Proforma::contarConDetalle($filtros);
        $totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
        if ($paginaActual > $totalPaginas) $paginaActual = $totalPaginas;
        $offset = ($paginaActual - 1) * $porPagina;
        $proformas = Proforma::allConDetalle($filtros, $porPagina, $offset);

        $this->view('proformas/index', [
            'proformas' => $proformas, 'proveedores' => Proveedor::activos(), 'filtros' => $filtros,
            'paginacion' => ['pagina_actual' => $paginaActual, 'total_paginas' => $totalPaginas, 'total_registros' => $totalRegistros, 'por_pagina' => $porPagina],
        ]);
    }

    public function create(): void
    {
        $prefill = null;
        $trabajoId = $this->input('trabajo_id', null);
        if ($trabajoId !== null && $trabajoId !== '') {
            $trabajo = Trabajo::findConGestion((int) $trabajoId);
            if ($trabajo) {
                // El proveedor de esa Gestión debe estar habilitado para pasar
                // a Proforma — si no lo está, se corta aquí mismo y se manda
                // de vuelta a la Gestión con una explicación.
                if (!Proveedor::estaHabilitadoParaProforma((int) $trabajo['proveedor_id'])) {
                    $this->flash('error', 'El proveedor de esta gestión no está habilitado para pasar a Proforma. Solo los proveedores seleccionados pueden continuar el proceso.');
                    $this->redirect('/gestiones/' . $trabajo['gestion_id']);
                }
                $prefill = [
                    'trabajo_id' => $trabajo['id'],
                    'n_cotizacion' => $trabajo['n_cotizacion'],
                    'proveedor_id' => $trabajo['proveedor_id'],
                    'solicitado_por' => $trabajo['solicitado_por'] ?? null,
                    // Descripción y valor: para que una mensualidad traída desde
                    // una Gestión no obligue a volver a escribir lo mismo dos veces.
                    'trabajo' => $trabajo['descripcion'] ?? null,
                    'valor_cotizacion' => $trabajo['valor'] ?? null,
                    // Para una mensualidad, el "valor de proforma" es prácticamente
                    // el mismo monto del trabajo — se precarga para no hacer que
                    // el usuario lo vuelva a escribir.
                    'valor_proforma' => $trabajo['valor'] ?? null,
                ];
            }
        }
        $this->view('proformas/form', [
            'proforma' => null,
            'prefill' => $prefill,
            'proveedores' => Proveedor::habilitadosParaProforma(),
            'areas' => Area::activas(),
            'cotizaciones' => Gestion::cotizacionesDisponibles(),
        ]);
    }

    public function buscarTrabajosPorCotizacion(): void
    {
        $numero = $this->input('numero', '');
        $trabajos = $numero !== '' ? Trabajo::porNumeroCotizacion($numero) : [];
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'trabajos' => $trabajos]);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->collectFormData();

        if (!Proveedor::estaHabilitadoParaProforma((int) $data['proveedor_id'])) {
            $this->flash('error', 'El proveedor de esta gestión no está habilitado para pasar a Proforma. Solo los proveedores seleccionados pueden continuar el proceso.');
            $this->view('proformas/form', [
                'proforma' => $data,
                'prefill' => null,
                'proveedores' => Proveedor::habilitadosParaProforma(),
                'areas' => Area::activas(),
                'cotizaciones' => Gestion::cotizacionesDisponibles(),
            ]);
            return;
        }

        $data['documento_pdf'] = $this->handleUpload('documento_pdf', 'proformas');
        $data['creado_por'] = Auth::id();

        $id = Proforma::insert($data);

        $proformaCreada = Proforma::findConDetalle($id);
        $descripcionCrear = $proformaCreada
            ? sprintf('Creó la proforma: %s — %s', $proformaCreada['n_proforma'] ?: '(sin número)', $proformaCreada['proveedor_nombre'] ?? 'proveedor no especificado')
            : 'Creó la proforma';
        Proforma::registrarHistorial($id, Auth::id(), $descripcionCrear);

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
        $this->view('proformas/show', ['proforma' => $proforma, 'trabajos' => Trabajo::deProforma($id), 'oc' => OrdenCompra::findPorProforma($id), 'historial' => Proforma::historialDe($id)]);
    }

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $proforma = Proforma::findConDetalle($id);
        if (!$proforma) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }
        $trabajos = Trabajo::deProforma($id);
        if (!empty($trabajos)) {
            $t = $trabajos[0];
            $proforma['trabajo'] = $proforma['trabajo'] ?: $t['descripcion'];
            $proforma['valor_cotizacion'] = $proforma['valor_cotizacion'] ?: $t['valor'];
            $proforma['trabajo_id'] = $t['id'];
            $proforma['n_cotizacion'] = $proforma['n_cotizacion'] ?: $t['n_cotizacion'];
        }

        $proveedoresDisponibles = Proveedor::habilitadosParaProforma();
        // Si esta proforma ya tenía un proveedor de ANTES de esta regla (y
        // ese proveedor ya no está habilitado), se agrega igual al desplegable
        // para no dejar el campo en blanco — solo se bloquea CAMBIAR a otro
        // proveedor no habilitado, no dejar el que ya tenía.
        if (!empty($proforma['proveedor_id']) && !Proveedor::estaHabilitadoParaProforma((int) $proforma['proveedor_id'])) {
            $proveedorActual = Proveedor::find((int) $proforma['proveedor_id']);
            if ($proveedorActual) {
                $proveedoresDisponibles[] = $proveedorActual;
            }
        }

        $this->view('proformas/form', [
            'proforma' => $proforma,
            'proveedores' => $proveedoresDisponibles,
            'areas' => Area::activas(),
            'cotizaciones' => Gestion::cotizacionesDisponibles($id),
        ]);
    }

    public function update(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $actual = Proforma::find($id);
        $data = $this->collectFormData();

        // Solo se bloquea si de verdad está CAMBIANDO el proveedor a uno no
        // habilitado — si deja el mismo que ya tenía (aunque ya no esté
        // habilitado), lo dejamos editar los demás campos sin problema.
        $proveedorCambio = (int) $data['proveedor_id'] !== (int) ($actual['proveedor_id'] ?? 0);
        if ($proveedorCambio && !Proveedor::estaHabilitadoParaProforma((int) $data['proveedor_id'])) {
            $this->flash('error', 'El proveedor de esta gestión no está habilitado para pasar a Proforma. Solo los proveedores seleccionados pueden continuar el proceso.');
            $this->view('proformas/form', [
                'proforma' => array_merge($actual ?? [], $data, ['id' => $id]),
                'proveedores' => Proveedor::habilitadosParaProforma(),
                'areas' => Area::activas(),
                'cotizaciones' => Gestion::cotizacionesDisponibles($id),
            ]);
            return;
        }

        $eliminarPdf = $this->input('eliminar_pdf', '0') === '1';
        if ($eliminarPdf && empty($_FILES['documento_pdf']['name'])) {
            if (!empty($actual['documento_pdf'])) {
                @unlink(__DIR__ . '/../../public/uploads/proformas/' . $actual['documento_pdf']);
            }
            $data['documento_pdf'] = null;
        } else {
            $data['documento_pdf'] = $this->handleUpload('documento_pdf', 'proformas', $actual['documento_pdf'] ?? null);
        }

        Proforma::update($id, $data);

        $proformaActualizada = Proforma::findConDetalle($id);
        $descripcionActualizar = $proformaActualizada
            ? sprintf('Actualizó los datos de la proforma: %s — %s', $proformaActualizada['n_proforma'] ?: '(sin número)', $proformaActualizada['proveedor_nombre'] ?? 'proveedor no especificado')
            : 'Actualizó los datos de la proforma';
        Proforma::registrarHistorial($id, Auth::id(), $descripcionActualizar);

        $this->vincularTrabajo($id, $data, true);

        $this->flash('success', 'Proforma actualizada correctamente.');
        $this->redirect('/proformas/' . $id);
    }

    public function destroy(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];

        $proforma = Proforma::findConDetalle($id);
        if ($proforma && !empty($proforma['documento_pdf'])) {
            @unlink(__DIR__ . '/../../public/uploads/proformas/' . $proforma['documento_pdf']);
        }

        // Cascada manual: OC -> Factura -> Entrega
        $oc = OrdenCompra::findPorProforma($id);
        if ($oc) {
            $factura = Factura::findPorOrdenCompra($oc['id']);
            if ($factura) {
                $entrega = EntregaFactura::findPorFactura($factura['id']);
                if ($entrega) {
                    EntregaFactura::registrarHistorial($entrega['id'], Auth::id(), 'Eliminada automáticamente (cascada al eliminar la proforma)');
                    EntregaFactura::softDelete($entrega['id'], Auth::id());
                }
                Factura::registrarHistorial($factura['id'], Auth::id(), 'Eliminada automáticamente (cascada al eliminar la proforma)');
                Factura::softDelete($factura['id'], Auth::id());
            }
            OrdenCompra::registrarHistorial($oc['id'], Auth::id(), 'Eliminada automáticamente (cascada al eliminar la proforma)');
            OrdenCompra::softDelete($oc['id'], Auth::id());
        }

        $descripcion = $proforma
            ? sprintf('Eliminó la proforma: %s — %s', $proforma['n_proforma'] ?: '(sin número)', $proforma['proveedor_nombre'] ?? 'proveedor no especificado')
            : 'Eliminó la proforma';

        Trabajo::desasignarPorProforma($id);
        Proforma::registrarHistorial($id, Auth::id(), $descripcion);
        Proforma::softDelete($id, Auth::id());

        $this->flash('success', 'Proforma eliminada. Los trabajos asociados quedaron sin asignar.');
        $this->redirect('/proformas');
    }

    private function vincularTrabajo(int $proformaId, array $dataProforma, bool $esEdicion = false): void
    {
        $trabajoId = $this->input('trabajo_id', null);
        $nCotizacion = $this->input('n_cotizacion', null);
        $descripcion = $this->input('trabajo', null) ?: $this->input('trabajo_manual', null);
        $valor = $this->input('valor_cotizacion', null);
        $valor = ($valor === '' || $valor === null) ? null : $valor;

        if ($valor === null && !empty($dataProforma['valor_proforma'])) {
            $valor = $dataProforma['valor_proforma'];
        }

        if ($trabajoId !== null && $trabajoId !== '') {
            if ($esEdicion) Trabajo::desasignarPorProforma($proformaId);
            Trabajo::asignarProforma((int) $trabajoId, $proformaId);

            $trabajo = Trabajo::findConGestion((int) $trabajoId);
            if ($trabajo) {
                Proforma::update($proformaId, [
                    'n_cotizacion'     => $trabajo['n_cotizacion'] ?? $nCotizacion,
                    'trabajo'          => $trabajo['descripcion'] ?? $descripcion,
                    'valor_cotizacion' => $trabajo['valor'] ?? $valor,
                ]);
            }
            return;
        }

        if (empty($nCotizacion) && !empty($descripcion)) {
            $trabajosActuales = $esEdicion ? Trabajo::deProforma($proformaId) : [];
            if (!empty($trabajosActuales)) {
                Trabajo::update($trabajosActuales[0]['id'], ['descripcion' => $descripcion, 'valor' => $valor]);
                Proforma::update($proformaId, [
                    'n_cotizacion'     => null,
                    'trabajo'          => $descripcion,
                    'valor_cotizacion' => $valor,
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
            Trabajo::reemplazarDeGestion($gestionId, [['descripcion' => $descripcion, 'valor' => $valor, 'proforma_id' => $proformaId]]);
            Proforma::update($proformaId, [
                'n_cotizacion'     => null,
                'trabajo'          => $descripcion,
                'valor_cotizacion' => $valor,
            ]);
        }
    }

    private function collectFormData(): array
    {
        $campos = ['proveedor_id', 'fecha_solicitud', 'solicitado_por', 'n_cotizacion', 'trabajo', 'valor_cotizacion', 'n_proforma', 'valor_proforma', 'fecha_revision_proforma', 'comentario'];
        $data = [];
        foreach ($campos as $campo) {
            $valor = $this->input($campo, null);
            $data[$campo] = ($valor === '' || $valor === null) ? null : $valor;
        }
        $data['proveedor_id'] = $data['proveedor_id'] !== null ? (int) $data['proveedor_id'] : null;

        // Si se envió trabajo_manual en vez de trabajo
        if (empty($data['trabajo']) && !empty($this->input('trabajo_manual', ''))) {
            $data['trabajo'] = $this->input('trabajo_manual');
        }

        // Si se seleccionó un trabajo_id, sincronizar automáticamente
        $trabajoId = $this->input('trabajo_id', null);
        if (!empty($trabajoId)) {
            $trabajo = Trabajo::findConGestion((int) $trabajoId);
            if ($trabajo) {
                $data['trabajo'] = $trabajo['descripcion'];
                $data['valor_cotizacion'] = $trabajo['valor'];
                $data['n_cotizacion'] = $trabajo['n_cotizacion'];
                if (empty($data['proveedor_id']) && !empty($trabajo['proveedor_id'])) {
                    $data['proveedor_id'] = (int) $trabajo['proveedor_id'];
                }
            }
        }

        return $data;
    }
}