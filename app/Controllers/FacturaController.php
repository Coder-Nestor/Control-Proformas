<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use App\Models\Factura;
use App\Models\OrdenCompra;
use App\Models\EntregaFactura;
use App\Models\Documento;

class FacturaController extends Controller
{
    public function index(): void
    {
        $filtros = ['estado' => $this->input('estado', ''), 'buscar' => $this->input('buscar', '')];
        $facturas = Factura::allConDetalle($filtros);
        $documentosPorFactura = Documento::deEntidades('factura', array_column($facturas, 'id'));
        $this->view('facturas/index', [
            'facturas' => $facturas,
            'documentosPorFactura' => $documentosPorFactura,
            'estados' => Factura::ESTADOS,
            'filtros' => $filtros
        ]);
    }

    public function create(): void
    {
        $this->view('facturas/form', ['factura' => null, 'ordenesCompra' => OrdenCompra::sinFactura(), 'estados' => Factura::ESTADOS]);
    }

    public function store(): void
    {
        $this->verifyCsrf();
        $data = $this->collectFormData();
        $data['creado_por'] = Auth::id();

        $id = Factura::insert($data);
        $this->guardarDocumentos($this->handleMultipleUploads('documentos', 'facturas'), 'factura', $id, 'facturas');

        $facturaCreada = Factura::findConDetalle($id);
        $descripcionCrear = $facturaCreada
            ? sprintf('Creó la factura: N° %s — OCE %s — %s', $facturaCreada['n_factura'] ?: '(sin número)', $facturaCreada['n_oce_interna'] ?: '(sin número)', $facturaCreada['proveedor_nombre'] ?? 'proveedor no especificado')
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
        $this->view('facturas/show', ['factura' => $factura, 'documentos' => Documento::deEntidad('factura', $id), 'entrega' => EntregaFactura::findPorFactura($id), 'historial' => Factura::historialDe($id)]);
    }

    public function edit(array $params): void
    {
        $id = (int) $params['id'];
        $factura = Factura::findConDetalle($id);
        if (!$factura) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }

        if ($this->estaCerradaParaEdicion($factura)) {
            $this->flash('error', 'Esta factura ya está marcada como "Correcta" y queda cerrada — solo un Administrador puede editarla.');
            $this->redirect('/facturas/' . $id);
        }

        $this->view('facturas/form', [
            'factura' => $factura,
            'documentos' => Documento::deEntidad('factura', $id),
            'ordenesCompra' => OrdenCompra::sinFactura($factura['orden_compra_id']),
            'estados' => Factura::ESTADOS
        ]);
    }

    public function update(array $params): void
    {
        $this->verifyCsrf();
        $id = (int) $params['id'];
        $actual = Factura::find($id);

        if (!$actual) {
            http_response_code(404);
            $this->view('errors/404_inline', []);
            return;
        }

        if ($this->estaCerradaParaEdicion($actual)) {
            $this->flash('error', 'Esta factura ya está marcada como "Correcta" y queda cerrada — solo un Administrador puede editarla.');
            $this->redirect('/facturas/' . $id);
        }

        $data = $this->collectFormData();

        // Procesar eliminación de documentos marcados — primero se borra el
        // archivo físico de /uploads (necesita el registro ANTES de marcarlo
        // eliminado, para saber su nombre_archivo), y luego se hace el
        // borrado lógico en la base de datos.
        $eliminarDocs = $_POST['eliminar_documentos'] ?? [];
        if (is_array($eliminarDocs)) {
            foreach ($eliminarDocs as $docId) {
                $this->eliminarDocumentoIndividual((int) $docId, 'facturas');
            }
        }

        Factura::update($id, $data);
        $this->guardarDocumentos($this->handleMultipleUploads('documentos', 'facturas'), 'factura', $id, 'facturas');

        $facturaActualizada = Factura::findConDetalle($id);
        $descripcionActualizar = $facturaActualizada
            ? sprintf('Actualizó los datos de la factura: N° %s — OCE %s — %s', $facturaActualizada['n_factura'] ?: '(sin número)', $facturaActualizada['n_oce_interna'] ?: '(sin número)', $facturaActualizada['proveedor_nombre'] ?? 'proveedor no especificado')
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

        if ($factura && $this->estaCerradaParaEdicion($factura)) {
            $this->flash('error', 'Esta factura ya está marcada como "Correcta" y queda cerrada — solo un Administrador puede eliminarla.');
            $this->redirect('/facturas/' . $id);
        }

        $entrega = EntregaFactura::findPorFactura($id);
        if ($entrega) {
            EntregaFactura::registrarHistorial($entrega['id'], Auth::id(), 'Eliminada automáticamente (cascada al eliminar la factura)');
            EntregaFactura::softDelete($entrega['id'], Auth::id());
        }

        $descripcion = $factura
            ? sprintf('Eliminó la factura: N° %s — OCE %s — %s', $factura['n_factura'] ?: '(sin número)', $factura['n_oce_interna'] ?: '(sin número)', $factura['proveedor_nombre'] ?? 'proveedor no especificado')
            : 'Eliminó la factura';

        Factura::registrarHistorial($id, Auth::id(), $descripcion);
        $this->eliminarDocumentosDeEntidad('factura', $id, 'facturas');
        Factura::softDelete($id, Auth::id());

        $this->flash('success', 'Factura eliminada.');
        $this->redirect('/facturas');
    }

    /**
     * Una factura marcada como "Correcta" queda completamente cerrada —
     * nadie que no sea Administrador puede editarla ni eliminarla, ni
     * siquiera para cambiarle el estado a Pendiente/Con problema.
     */
    private function estaCerradaParaEdicion(array $factura): bool
    {
        return ($factura['estado'] ?? null) === 'correcta' && !Auth::hasRole(['administrador']);
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
     * entidad (ej. al eliminar la factura completa), y luego los marca como
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
        $campos = ['orden_compra_id', 'n_factura', 'fecha_entrega_factura', 'estado', 'comentario'];
        $data = [];
        foreach ($campos as $campo) {
            $valor = $this->input($campo, null);
            $data[$campo] = ($valor === '' || $valor === null) ? null : $valor;
        }
        $data['orden_compra_id'] = (int) $data['orden_compra_id'];
        if (!array_key_exists($data['estado'], Factura::ESTADOS)) $data['estado'] = 'pendiente';
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