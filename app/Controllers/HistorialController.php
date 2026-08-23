<?php

namespace App\Controllers;

use Core\Controller;
use App\Models\Historial;
use App\Models\Usuario;

class HistorialController extends Controller
{
    private const POR_PAGINA = 10;

    public function index(): void
    {
        $filtros = [
            'entidad'     => $this->input('entidad', ''),
            'usuario_id'  => $this->input('usuario_id', ''),
            'buscar'      => $this->input('buscar', ''),
            'fecha_desde' => $this->input('fecha_desde', ''),
            'fecha_hasta' => $this->input('fecha_hasta', ''),
        ];

        $paginaActual = max(1, (int) $this->input('pagina', 1));
        $porPagina    = self::POR_PAGINA;

        $totalRegistros = Historial::contarConDetalle($filtros);
        $totalPaginas   = max(1, (int) ceil($totalRegistros / $porPagina));

        if ($paginaActual > $totalPaginas) {
            $paginaActual = $totalPaginas;
        }

        $offset = ($paginaActual - 1) * $porPagina;

        $this->view('historial/index', [
            'registros'  => Historial::allConDetalle($filtros, $porPagina, $offset),
            'entidades'  => Historial::ENTIDADES,
            'rutas'      => Historial::RUTAS,
            'usuarios'   => Usuario::all('nombre ASC'),
            'filtros'    => $filtros,
            'paginacion' => [
                'pagina_actual'   => $paginaActual,
                'total_paginas'   => $totalPaginas,
                'total_registros' => $totalRegistros,
                'por_pagina'      => $porPagina,
            ],
        ]);
    }
}
