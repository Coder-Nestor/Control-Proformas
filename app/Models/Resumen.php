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
    /** Una fila por cada Trabajo ya asignado a una Proforma. */
    public static function proformas(): array
    {
        $stmt = self::db()->query(
            "SELECT DISTINCT g.n_cotizacion, p.id AS proforma_id, p.n_proforma, p.valor_proforma,
                    p.solicitado_por, p.comentario
             FROM trabajos t
             INNER JOIN gestiones g ON g.id = t.gestion_id
             INNER JOIN proformas p ON p.id = t.proforma_id
             ORDER BY p.id DESC"
        );
        return $stmt->fetchAll();
    }

    /** Una fila por cada Proforma que ya llegó hasta tener Factura emitida. */
    public static function facturas(): array
    {
        $stmt = self::db()->query(
            "SELECT DISTINCT g.n_cotizacion, p.n_proforma, p.valor_proforma,
                    oc.n_oce_interna, f.id AS factura_id, f.n_factura, f.estado, f.comentario
             FROM trabajos t
             INNER JOIN gestiones g ON g.id = t.gestion_id
             INNER JOIN proformas p ON p.id = t.proforma_id
             INNER JOIN ordenes_compra oc ON oc.proforma_id = p.id
             INNER JOIN facturas f ON f.orden_compra_id = oc.id
             ORDER BY f.id DESC"
        );
        return $stmt->fetchAll();
    }
}