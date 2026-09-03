<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\OrdenCompra;
use App\Models\Proforma;
use App\Models\Factura;
use App\Models\EntregaFactura;
use App\Models\Documento;

class OrdenCompraController extends Controller
{
    public function index(): void
    {
        $filtros = ['estado' => $this->input('estado', ''), 'buscar' => $this->input('buscar', '')];
        $this->view('ordenes/index', ['ordenes' => OrdenCompra::allConDetalle($filtros), 'estados' => OrdenCompra::ESTADOS, 'filtros' => $filtros]);
    }

    public function create(): void
    {
        $selectedProformaId = (int) $this->input('proforma_id', 0);
        $this->view('ordenes/form', ['oc' => null, 'proformas' => Proforma::sinOrdenDeCompra(), 'estados' => OrdenCompra::ESTADOS, 'selectedProformaId' => $selectedProformaId]);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->collectFormData();
        $data['creado_por'] = Auth::id();

        $id = OrdenCompra::insert($data);
        $this->guardarDocumentos($this->handleMultipleUploads('documentos', 'ordenes_compra'), 'orden_compra', $id, 'ordenes_compra');

        $ocCreada = OrdenCompra::findConDetalle($id);
        $descripcionCrear = $ocCreada
            ? sprintf('Creó la orden de compra: N° OCE %s — Proforma %s — %s', $ocCreada['n_oce_interna'] ?: '(sin número)', $ocCreada['n_proforma'] ?: '(sin número)', $ocCreada['proveedor_nombre'] ?? 'proveedor no especificado')
            : 'Creó la orden de compra';
        OrdenCompra::registrarHistorial($id, Auth::id(), $descripcionCrear);

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
        $this->view('ordenes/show', ['oc' => $oc, 'documentos' => Documento::deEntidad('orden_compra', $id), 'factura' => Factura::findPorOrdenCompra($id), 'historial' => OrdenCompra::historialDe($id)]);
    }

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $oc = OrdenCompra::findConDetalle($id);
        if (!$oc) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }
        $this->view('ordenes/form', [
            'oc' => $oc,
            'documentos' => Documento::deEntidad('orden_compra', $id),
            'proformas' => Proforma::sinOrdenDeCompra($oc['proforma_id']),
            'estados' => OrdenCompra::ESTADOS
        ]);
    }

    public function update(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $actual = OrdenCompra::find($id);
        $data = $this->collectFormData();

        // Procesar eliminación de documentos marcados — primero se borra el
        // archivo físico de /uploads (necesita el registro ANTES de marcarlo
        // eliminado, para saber su nombre_archivo), y luego se hace el
        // borrado lógico en la base de datos.
        $eliminarDocs = $_POST['eliminar_documentos'] ?? [];
        if (is_array($eliminarDocs)) {
            foreach ($eliminarDocs as $docId) {
                $this->eliminarDocumentoIndividual((int) $docId, 'ordenes_compra');
            }
        }

        OrdenCompra::update($id, $data);
        $this->guardarDocumentos($this->handleMultipleUploads('documentos', 'ordenes_compra'), 'orden_compra', $id, 'ordenes_compra');

        $ocActualizada = OrdenCompra::findConDetalle($id);
        $descripcionActualizar = $ocActualizada
            ? sprintf('Actualizó los datos de la orden de compra: N° OCE %s — Proforma %s — %s', $ocActualizada['n_oce_interna'] ?: '(sin número)', $ocActualizada['n_proforma'] ?: '(sin número)', $ocActualizada['proveedor_nombre'] ?? 'proveedor no especificado')
            : 'Actualizó los datos de la orden de compra';
        OrdenCompra::registrarHistorial($id, Auth::id(), $descripcionActualizar);

        $this->flash('success', 'Orden de compra actualizada correctamente.');
        $this->redirect('/ordenes/' . $id);
    }

    public function destroy(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];

        $oc = OrdenCompra::findConDetalle($id);

        // Si esta OC ya tiene una factura marcada como "Correcta" (cerrada),
        // no se puede eliminar sin ser Administrador — eliminar la OC borraría
        // esa factura en cascada, saltándose el cierre que ya se le puso.
        $facturaVinculada = Factura::findPorOrdenCompra($id);
        if ($facturaVinculada && $facturaVinculada['estado'] === 'correcta' && !Auth::hasRole(['administrador'])) {
            $this->flash('error', 'Esta orden de compra tiene una factura marcada como "Correcta" y cerrada — no se puede eliminar.');
            $this->redirect('/ordenes/' . $id);
        }

        // Cascada manual: Factura -> Entrega
        if ($facturaVinculada) {
            $entrega = EntregaFactura::findPorFactura($facturaVinculada['id']);
            if ($entrega) {
                EntregaFactura::registrarHistorial($entrega['id'], Auth::id(), 'Eliminada automáticamente (cascada al eliminar la orden de compra)');
                EntregaFactura::softDelete($entrega['id'], Auth::id());
            }
            Factura::registrarHistorial($facturaVinculada['id'], Auth::id(), 'Eliminada automáticamente (cascada al eliminar la orden de compra)');
            Factura::softDelete($facturaVinculada['id'], Auth::id());
        }

        $descripcion = $oc
            ? sprintf('Eliminó la orden de compra: N° OCE %s — Proforma %s — %s', $oc['n_oce_interna'] ?: '(sin número)', $oc['n_proforma'] ?: '(sin número)', $oc['proveedor_nombre'] ?? 'proveedor no especificado')
            : 'Eliminó la orden de compra';

        OrdenCompra::registrarHistorial($id, Auth::id(), $descripcion);
        $this->eliminarDocumentosDeEntidad('orden_compra', $id, 'ordenes_compra');
        OrdenCompra::softDelete($id, Auth::id());

        $this->flash('success', 'Orden de compra eliminada.');
        $this->redirect('/ordenes');
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
     * entidad (ej. al eliminar la orden de compra completa), y luego los
     * marca como eliminados en la base de datos — mismo orden que arriba,
     * por la misma razón: hay que leerlos mientras siguen activos.
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
        $campos = ['proforma_id', 'fecha_envio_oce', 'n_oce_interna', 'estado', 'comentario'];
        $data = [];
        foreach ($campos as $campo) {
            $valor = $this->input($campo, null);
            $data[$campo] = ($valor === '' || $valor === null) ? null : $valor;
        }
        $data['proforma_id'] = (int) $data['proforma_id'];
        if (!array_key_exists($data['estado'], OrdenCompra::ESTADOS)) $data['estado'] = 'pendiente';
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
}