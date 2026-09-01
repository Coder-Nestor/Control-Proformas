<?php

namespace App\Models;

use Core\Model;

/**
 * Reconstruye la hoja "Resumen" del Excel original: une Cotización (Gestión)
 * -> Proforma -> Orden de Compra -> Factura en un par de tablas planas,
 * exactamente como en las tablas "Proformas" y "Facturas" del Excel.
 *
 * Nota: la columna que en el Excel se llama "Estado" en realidad contiene
 * el área/proveedor que solicitó la proforma (Finca, Logística, Ares Sun...),
 * así que aquí se muestra como "Solicitado por" para no confundirla con el
 * estado real del flujo (correcta/pendiente/con_problema).
 */
class Resumen extends Model
{
    /**
     * Una fila por cada Trabajo ya asignado a una Proforma. A propósito
     * exige que exista un Trabajo (INNER JOIN) — una proforma sin ningún
     * trabajo capturado no representa una cotización real que valga la
     * pena resumir aquí.
     */
    public static function proformas(): array
    {
        $stmt = self::db()->query(
            "SELECT DISTINCT g.n_cotizacion, p.id AS proforma_id, p.n_proforma, p.valor_proforma,
                    p.solicitado_por, p.comentario
             FROM trabajos t
             INNER JOIN gestiones g ON g.id = t.gestion_id
             INNER JOIN proformas p ON p.id = t.proforma_id
             WHERE g.eliminado_en IS NULL AND p.eliminado_en IS NULL
             ORDER BY p.id DESC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Una fila por cada Proforma que ya llegó hasta tener Factura emitida.
     *
     * A diferencia de proformas(), aquí el Trabajo/Cotización es solo
     * información OPCIONAL (LEFT JOIN) — la Factura debe aparecer siempre
     * que exista, sin importar si a su Proforma nunca se le vinculó un
     * Trabajo. Antes, al exigir el Trabajo como obligatorio, una Factura
     * completa podía desaparecer del resumen si su Proforma se creó sin
     * pasar por el buscador de cotización.
     *
     * El comentario que se muestra es el de la ENTREGA de esa factura (no
     * el de la factura en sí) — si todavía no tiene entrega registrada,
     * simplemente queda vacío.
     */
    public static function facturas(): array
    {
        $stmt = self::db()->query(
            "SELECT DISTINCT g.n_cotizacion, p.n_proforma, p.valor_proforma,
                    oc.n_oce_interna, f.id AS factura_id, f.n_factura, f.estado,
                    e.comentario
             FROM facturas f
             INNER JOIN ordenes_compra oc ON oc.id = f.orden_compra_id
             INNER JOIN proformas p ON p.id = oc.proforma_id
             LEFT JOIN trabajos t ON t.proforma_id = p.id
             LEFT JOIN gestiones g ON g.id = t.gestion_id AND g.eliminado_en IS NULL
             LEFT JOIN entregas_factura e ON e.factura_id = f.id AND e.eliminado_en IS NULL
             WHERE p.eliminado_en IS NULL AND oc.eliminado_en IS NULL AND f.eliminado_en IS NULL
             ORDER BY f.id DESC"
        );
        return $stmt->fetchAll();
    }
}