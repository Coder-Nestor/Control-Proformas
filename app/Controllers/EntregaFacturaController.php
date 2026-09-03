<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\EntregaFactura;
use App\Models\Factura;
use App\Models\Documento;

class EntregaFacturaController extends Controller
{
    public function index(): void
    {
        $filtros = ['buscar' => $this->input('buscar', '')];
        $this->view('entregas/index', ['entregas' => EntregaFactura::allConDetalle($filtros), 'filtros' => $filtros]);
    }

    public function create(): void
    {
        $this->view('entregas/form', ['entrega' => null, 'facturas' => Factura::sinEntrega()]);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->collectFormData();
        $data['creado_por'] = Auth::id();

        $id = EntregaFactura::insert($data);
        $this->guardarDocumentos($this->handleMultipleUploads('documentos', 'entregas'), 'entrega_factura', $id, 'entregas');

        $entregaCreada = EntregaFactura::findConDetalle($id);
        $descripcionCrear = $entregaCreada
            ? sprintf('Creó el registro de entrega: Factura %s — OCE %s — Proforma %s — %s', $entregaCreada['n_factura'] ?: '(sin número)', $entregaCreada['n_oce_interna'] ?: '(sin número)', $entregaCreada['n_proforma'] ?: '(sin número)', $entregaCreada['proveedor_nombre'] ?? 'proveedor no especificado')
            : 'Creó el registro de entrega';
        EntregaFactura::registrarHistorial($id, Auth::id(), $descripcionCrear);

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
        $this->view('entregas/show', ['entrega' => $entrega, 'documentos' => Documento::deEntidad('entrega_factura', $id), 'historial' => EntregaFactura::historialDe($id)]);
    }

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $entrega = EntregaFactura::findConDetalle($id);
        if (!$entrega) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }
        $this->view('entregas/form', [
            'entrega' => $entrega,
            'documentos' => Documento::deEntidad('entrega_factura', $id),
            'facturas' => Factura::sinEntrega($entrega['factura_id'])
        ]);
    }

    public function update(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $actual = EntregaFactura::find($id);
        $data = $this->collectFormData();

        // Procesar eliminación de documentos marcados — primero se borra el
        // archivo físico de /uploads (necesita el registro ANTES de marcarlo
        // eliminado, para saber su nombre_archivo), y luego se hace el
        // borrado lógico en la base de datos.
        $eliminarDocs = $_POST['eliminar_documentos'] ?? [];
        if (is_array($eliminarDocs)) {
            foreach ($eliminarDocs as $docId) {
                $this->eliminarDocumentoIndividual((int) $docId, 'entregas');
            }
        }

        EntregaFactura::update($id, $data);
        $this->guardarDocumentos($this->handleMultipleUploads('documentos', 'entregas'), 'entrega_factura', $id, 'entregas');

        $entregaActualizada = EntregaFactura::findConDetalle($id);
        $descripcionActualizar = $entregaActualizada
            ? sprintf('Actualizó los datos de la entrega: Factura %s — OCE %s — Proforma %s — %s', $entregaActualizada['n_factura'] ?: '(sin número)', $entregaActualizada['n_oce_interna'] ?: '(sin número)', $entregaActualizada['n_proforma'] ?: '(sin número)', $entregaActualizada['proveedor_nombre'] ?? 'proveedor no especificado')
            : 'Actualizó los datos de la entrega';
        EntregaFactura::registrarHistorial($id, Auth::id(), $descripcionActualizar);

        $this->flash('success', 'Entrega actualizada correctamente.');
        $this->redirect('/entregas/' . $id);
    }

    public function destroy(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];

        $entrega = EntregaFactura::findConDetalle($id);
        $descripcion = $entrega
            ? sprintf('Eliminó la entrega: Factura %s — OCE %s — Proforma %s — %s', $entrega['n_factura'] ?: '(sin número)', $entrega['n_oce_interna'] ?: '(sin número)', $entrega['n_proforma'] ?: '(sin número)', $entrega['proveedor_nombre'] ?? 'proveedor no especificado')
            : 'Eliminó la entrega';

        EntregaFactura::registrarHistorial($id, Auth::id(), $descripcion);
        $this->eliminarDocumentosDeEntidad('entrega_factura', $id, 'entregas');
        EntregaFactura::softDelete($id, Auth::id());

        $this->flash('success', 'Entrega eliminada.');
        $this->redirect('/entregas');
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
     * entidad (ej. al eliminar la entrega completa), y luego los marca como
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
        $campos = ['factura_id', 'fecha_entrega_dueno', 'fecha_solicitud_revision_pago', 'comentario'];
        $data = [];
        foreach ($campos as $campo) {
            $valor = $this->input($campo, null);
            $data[$campo] = ($valor === '' || $valor === null) ? null : $valor;
        }
        $data['factura_id'] = (int) $data['factura_id'];
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