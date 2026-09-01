<?php

namespace App\Controllers;

use Core\Controller;
use Core\Database;
use App\Models\Gestion;
use App\Models\Proforma;
use App\Models\Factura;
use App\Models\EntregaFactura;
use App\Models\Resumen;

class DashboardController extends Controller
{
    public function index(): void
    {
        $config = require __DIR__ . '/../../config/config.php';
        $umbralGestion = $config['app']['dias_alerta_gestion'];
        $umbralFactura = $config['app']['dias_alerta_factura'] ?? 8;

        // Solo se suman los trabajos cuya Gestión NO haya sido eliminada
        // (borrado lógico) — antes sumaba directo de "trabajos" sin cruzar
        // con "gestiones", así que un trabajo de una gestión ya eliminada
        // seguía contando para siempre en este total.
        $valorTotalCotizado = (float) (Database::connection()->query(
            "SELECT COALESCE(SUM(t.valor), 0)
             FROM trabajos t
             INNER JOIN gestiones g ON g.id = t.gestion_id
             WHERE g.eliminado_en IS NULL"
        )->fetchColumn());

        $sinAsignar         = Gestion::contarSinAsignar();
        $proformasSinOc     = count(Proforma::allConDetalle(['sin_oc' => '1']));
        $facturasSinEntrega = count(Factura::sinEntrega());

        $atrasadas         = Gestion::atrasadas($umbralGestion);
        $entregasAtrasadas = EntregaFactura::atrasadas($umbralFactura);

        $facturaPorEstado = Factura::contarPorEstado();
        $tendencia        = Gestion::tendenciaMensual();

        $chartData = [
            'facturaEstado' => [
                'labels' => array_values(Factura::ESTADOS),
                'values' => array_values($facturaPorEstado),
            ],
            'tendencia' => [
                'labels' => array_column($tendencia, 'mes'),
                'values' => array_map('intval', array_column($tendencia, 'total')),
            ],
        ];

        $this->view('dashboard/index', [
            'sinAsignar'         => $sinAsignar,
            'proformasSinOc'     => $proformasSinOc,
            'facturasSinEntrega' => $facturasSinEntrega,
            'valorTotalCotizado' => $valorTotalCotizado,
            'atrasadas'          => $atrasadas,
            'entregasAtrasadas'  => $entregasAtrasadas,
            'umbralGestion'      => $umbralGestion,
            'umbralFactura'      => $umbralFactura,
            'chartData'          => $chartData,
            'resumenProformas'   => Resumen::proformas(),
            'resumenFacturas'    => Resumen::facturas(),
        ]);
    }
}