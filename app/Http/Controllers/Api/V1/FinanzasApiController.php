<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EstadoResultadosMensual;
use App\Models\GastoMensualCategoria;
use App\Models\VentasClienteMensual;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class FinanzasApiController extends Controller
{
    /**
     * GET /api/finanzas/estado-resultados
     * ?from=2023-01-01&to=2024-12-31
     */
    public function estadoResultados(Request $request)
    {
        $from = $request->query('from'); // opcional
        $to   = $request->query('to');   // opcional

        $query = EstadoResultadosMensual::query();

        if ($from) {
            $query->where('mes', '>=', $from);
        }
        if ($to) {
            $query->where('mes', '<=', $to);
        }

        $data = $query->orderBy('mes')->get();

        return response()->json($data);
    }

    /**
     * GET /api/finanzas/estado-resultados/{year}/{month}
     */
    public function estadoResultadosMes($year, $month)
    {
        $mes = Carbon::createFromDate($year, $month, 1)->format('Y-m-01');

        $row = EstadoResultadosMensual::where('mes', $mes)->firstOrFail();

        return response()->json($row);
    }

    public function estadoResultadosAnual(Request $request)
    {
        $fromYear = $request->query('from_year'); // opcional
        $toYear   = $request->query('to_year');   // opcional

        $query = DB::table('vw_estado_resultados_anual');

        if ($fromYear) {
            $query->where('anio', '>=', (int) $fromYear);
        }
        if ($toYear) {
            $query->where('anio', '<=', (int) $toYear);
        }

        $data = $query->orderBy('anio')->get();

        return response()->json($data);
    }

    /**
     * GET /api/finanzas/ventas-clientes
     * ?year=2024&limit=10
     */
    public function ventasClientes(Request $request)
    {
        $year  = $request->query('year', date('Y'));
        $limit = (int) $request->query('limit', 10);

        $inicio = $year . '-01-01';
        $fin    = $year . '-12-31';

        $rows = VentasClienteMensual::select(
            'id_cliente',
            'nombre_cliente',
            DB::raw('SUM(ventas) AS ventas_anio'),
            DB::raw('SUM(utilidad_bruta) AS utilidad_bruta_anio')
        )
            ->whereBetween('mes', [$inicio, $fin])
            ->groupBy('id_cliente', 'nombre_cliente')
            ->orderByDesc('ventas_anio')
            ->limit($limit)
            ->get();

        return response()->json([
            'year'   => (int) $year,
            'limit'  => $limit,
            'data'   => $rows,
        ]);
    }

    /**
     * GET /api/finanzas/gastos-categoria
     * ?year=2024&month=3
     */
    public function gastosCategoria(Request $request)
    {
        $year  = $request->query('year');
        $month = $request->query('month');

        if (!$year || !$month) {
            return response()->json([
                'error' => 'Parámetros year y month son requeridos (ej: ?year=2024&month=3)'
            ], 400);
        }

        $mes = Carbon::createFromDate($year, $month, 1)->format('Y-m-01');

        $rows = GastoMensualCategoria::where('mes', $mes)
            ->orderByDesc('total_gasto')
            ->get();

        return response()->json([
            'mes'  => $mes,
            'data' => $rows,
        ]);
    }

    /**
     * GET /api/finanzas/cartera-resumen
     * ?fecha_corte=2024-12-31
     */
    public function carteraResumen(Request $request)
    {
        $fechaCorte = $request->query('fecha_corte', date('Y-m-d'));

        $rows = DB::select("
            WITH pagos_por_factura AS (
                SELECT id_factura, SUM(monto_pagado) AS total_pagado
                FROM pagos_clientes
                GROUP BY id_factura
            ),
            saldos AS (
                SELECT
                    f.id_factura,
                    f.total - COALESCE(p.total_pagado, 0) AS saldo_pendiente,
                    (?::date - f.fecha_vencimiento) AS dias_atraso
                FROM facturas_venta f
                LEFT JOIN pagos_por_factura p ON p.id_factura = f.id_factura
            ),
            clasificado AS (
                SELECT
                    saldo_pendiente,
                    CASE
                        WHEN saldo_pendiente <= 0 THEN 'SIN SALDO'
                        WHEN dias_atraso < 0 THEN 'NO VENCIDO'
                        WHEN dias_atraso BETWEEN 0 AND 30 THEN '0-30 días'
                        WHEN dias_atraso BETWEEN 31 AND 60 THEN '31-60 días'
                        WHEN dias_atraso BETWEEN 61 AND 90 THEN '61-90 días'
                        ELSE '+90 días'
                    END AS tramo
                FROM saldos
                WHERE saldo_pendiente > 0
            )
            SELECT tramo, SUM(saldo_pendiente) AS saldo_total
            FROM clasificado
            GROUP BY tramo
            ORDER BY tramo;
        ", [$fechaCorte]);

        return response()->json([
            'fecha_corte' => $fechaCorte,
            'data'        => $rows,
        ]);
    }

    /**
     * GET /api/finanzas/cartera-detalle
     * ?fecha_corte=2024-12-31&tramo=31-60 días&id_cliente=5
     */
    public function carteraDetalle(Request $request)
    {
        $fechaCorte = $request->query('fecha_corte', date('Y-m-d'));
        $tramo      = $request->query('tramo');       // opcional
        $idCliente  = $request->query('id_cliente');  // opcional

        // Armamos el CTE base
        $sql = "
            WITH pagos_por_factura AS (
                SELECT id_factura, SUM(monto_pagado) AS total_pagado
                FROM pagos_clientes
                GROUP BY id_factura
            ),
            saldos AS (
                SELECT
                    f.id_factura,
                    f.id_cliente,
                    f.folio,
                    f.fecha_emision,
                    f.fecha_vencimiento,
                    f.total,
                    COALESCE(p.total_pagado, 0) AS total_pagado,
                    f.total - COALESCE(p.total_pagado, 0) AS saldo_pendiente,
                    (?::date - f.fecha_vencimiento) AS dias_atraso
                FROM facturas_venta f
                LEFT JOIN pagos_por_factura p ON p.id_factura = f.id_factura
            )
            SELECT
                s.id_factura,
                s.id_cliente,
                c.nombre_cliente,
                s.folio,
                s.fecha_emision,
                s.fecha_vencimiento,
                s.total,
                s.total_pagado,
                s.saldo_pendiente,
                s.dias_atraso,
                CASE
                    WHEN s.saldo_pendiente <= 0 THEN 'SIN SALDO'
                    WHEN s.dias_atraso < 0 THEN 'NO VENCIDO'
                    WHEN s.dias_atraso BETWEEN 0 AND 30 THEN '0-30 días'
                    WHEN s.dias_atraso BETWEEN 31 AND 60 THEN '31-60 días'
                    WHEN s.dias_atraso BETWEEN 61 AND 90 THEN '61-90 días'
                    ELSE '+90 días'
                END AS tramo
            FROM saldos s
            JOIN clientes c ON c.id_cliente = s.id_cliente
            WHERE s.saldo_pendiente > 0
        ";

        $bindings = [$fechaCorte];

        // Filtros opcionales
        if ($tramo) {
            $sql .= " AND CASE
                        WHEN s.saldo_pendiente <= 0 THEN 'SIN SALDO'
                        WHEN s.dias_atraso < 0 THEN 'NO VENCIDO'
                        WHEN s.dias_atraso BETWEEN 0 AND 30 THEN '0-30 días'
                        WHEN s.dias_atraso BETWEEN 31 AND 60 THEN '31-60 días'
                        WHEN s.dias_atraso BETWEEN 61 AND 90 THEN '61-90 días'
                        ELSE '+90 días'
                    END = ? ";
            $bindings[] = $tramo;
        }

        if ($idCliente) {
            $sql .= " AND s.id_cliente = ? ";
            $bindings[] = $idCliente;
        }

        $sql .= " ORDER BY s.dias_atraso DESC, s.fecha_emision;";

        $rows = DB::select($sql, $bindings);

        return response()->json([
            'fecha_corte' => $fechaCorte,
            'tramo'       => $tramo,
            'id_cliente'  => $idCliente,
            'data'        => $rows,
        ]);
    }

    public function kpiCarteraResumen(Request $request)
    {
        $fechaCorte = $request->query('fecha_corte', date('Y-m-d'));

        $row = DB::selectOne("
        WITH params AS (
            SELECT ?::date AS fecha_corte
        ),
        pagos_por_factura AS (
            SELECT id_factura, SUM(monto_pagado) AS total_pagado
            FROM pagos_clientes
            GROUP BY id_factura
        ),
        saldos AS (
            SELECT
                f.id_factura,
                f.total,
                f.fecha_emision,
                f.fecha_vencimiento,
                f.total - COALESCE(p.total_pagado, 0) AS saldo_pendiente,
                (SELECT fecha_corte FROM params) - f.fecha_vencimiento AS dias_atraso
            FROM facturas_venta f
            LEFT JOIN pagos_por_factura p ON p.id_factura = f.id_factura
        ),
        cartera AS (
            SELECT
                SUM(CASE WHEN saldo_pendiente > 0 THEN saldo_pendiente ELSE 0 END) AS cartera_total,
                SUM(CASE WHEN saldo_pendiente > 0 AND dias_atraso >= 0 THEN saldo_pendiente ELSE 0 END) AS cartera_vencida
            FROM saldos
        ),
        ventas_mes AS (
            SELECT
                date_trunc('month', (SELECT fecha_corte FROM params))::date AS mes_ref,
                SUM(total) AS ventas_mes
            FROM facturas_venta
            WHERE date_trunc('month', fecha_emision) = date_trunc('month', (SELECT fecha_corte FROM params))
        )
        SELECT
            cartera_total,
            cartera_vencida,
            ROUND(
                CASE WHEN cartera_total > 0 THEN cartera_vencida * 100.0 / cartera_total ELSE 0 END
            , 2) AS pct_cartera_vencida_sobre_total,
            ventas_mes,
            ROUND(
                CASE WHEN ventas_mes > 0 THEN cartera_vencida * 100.0 / ventas_mes ELSE 0 END
            , 2) AS pct_cartera_vencida_sobre_ventas_mes
        FROM cartera, ventas_mes;
    ", [$fechaCorte]);

        return response()->json([
            'fecha_corte' => $fechaCorte,
            'data' => $row,
        ]);
    }
}
