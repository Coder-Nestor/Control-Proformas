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
use App\Models\Documento;

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

        $data['creado_por'] = Auth::id();

        $id = Gestion::insert($data);
        $this->guardarDocumentos($this->handleMultipleUploads('documentos', 'gestiones'), 'gestion', $id, 'gestiones');
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
        $this->view('gestiones/show', ['gestion' => $gestion, 'documentos' => Documento::deEntidad('gestion', $id), 'trabajos' => Trabajo::deGestion($id), 'historial' => Gestion::historialDe($id)]);
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
            'documentos' => Documento::deEntidad('gestion', $id),
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
                'documentos' => Documento::deEntidad('gestion', $id),
                'trabajos' => $this->collectTrabajos(),
                'proveedores' => Proveedor::activos(),
                'proformas' => Proforma::paraSelect(),
                'areas' => Area::activas(),
            ]);
            return;
        }

        // Procesar eliminación de documentos marcados — primero se borra el
        // archivo físico de /uploads (necesita el registro ANTES de marcarlo
        // eliminado, para saber su nombre_archivo), y luego se hace el
        // borrado lógico en la base de datos.
        $eliminarDocs = $_POST['eliminar_documentos'] ?? [];
        if (is_array($eliminarDocs)) {
            foreach ($eliminarDocs as $docId) {
                $this->eliminarDocumentoIndividual((int) $docId, 'gestiones');
            }
        }

        Gestion::update($id, $data);
        $this->guardarDocumentos($this->handleMultipleUploads('documentos', 'gestiones'), 'gestion', $id, 'gestiones');
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
        $descripcion = $gestion
            ? sprintf('Eliminó la gestión: Cotización %s — %s', $gestion['n_cotizacion'] ?: '(sin número)', $gestion['proveedor_nombre'] ?? 'proveedor no especificado')
            : 'Eliminó la gestión';

        Gestion::registrarHistorial($id, Auth::id(), $descripcion);
        $this->eliminarDocumentosDeEntidad('gestion', $id, 'gestiones');
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

    /**
     * Borra físicamente de /uploads UN documento puntual (por su ID) y luego
     * lo marca como eliminado en la base de datos. El orden importa: hay que
     * buscar el registro ANTES de marcarlo eliminado, porque find() solo
     * encuentra registros activos — si se invirtiera el orden, ya no se
     * podría recuperar el nombre_archivo para borrar el archivo del disco.
     */
    private function eliminarDocumentoIndividual(int $docId, string $subdir): void
    {
        $doc = Documento::find($docId);
        if ($doc) {
            $ruta = __DIR__ . '/../../public/uploads/' . $subdir . '/' . $doc['nombre_archivo'];
            if (is_file($ruta)) {
                @unlink($ruta);
            }
        }
        Documento::softDelete($docId, Auth::id());
    }

    /**
     * Borra físicamente de /uploads TODOS los documentos activos de una
     * entidad (ej. al eliminar la gestión completa), y luego los marca como
     * eliminados en la base de datos — mismo orden que arriba, por la misma
     * razón: hay que leerlos mientras siguen activos.
     */
    private function eliminarDocumentosDeEntidad(string $tipoEntidad, int $idEntidad, string $subdir): void
    {
        $documentos = Documento::deEntidad($tipoEntidad, $idEntidad);
        foreach ($documentos as $doc) {
            $ruta = __DIR__ . '/../../public/uploads/' . $subdir . '/' . $doc['nombre_archivo'];
            if (is_file($ruta)) {
                @unlink($ruta);
            }
        }
        Documento::eliminarDeEntidad($tipoEntidad, $idEntidad, Auth::id());
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

    private function guardarDocumentos(array $archivos, string $tipoEntidad, int $idEntidad, string $subdir): void
    {
        foreach ($archivos as $archivo) {
            if (is_array($archivo)) {
                Documento::crearDelArchivo(
                    $tipoEntidad,
                    $idEntidad,
                    $archivo['nombre_archivo'],
                    $archivo['mime_type'],
                    (int) $archivo['tamano_bytes'],
                    Auth::id(),
                    $archivo['nombre_original'] ?? $archivo['nombre_archivo']
                );
            } else {
                $ruta = __DIR__ . '/../../public/uploads/' . $subdir . '/' . $archivo;
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = is_file($ruta) ? finfo_file($finfo, $ruta) : 'application/pdf';
                finfo_close($finfo);
                $size = is_file($ruta) ? filesize($ruta) : 0;
                Documento::crearDelArchivo($tipoEntidad, $idEntidad, $archivo, $mime, $size, Auth::id());
            }
        }
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