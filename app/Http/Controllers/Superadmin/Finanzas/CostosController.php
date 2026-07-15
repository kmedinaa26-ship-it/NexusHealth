<?php
namespace App\Http\Controllers\Superadmin\Finanzas;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CostosController extends Controller
{
    public function index()
    {
        $costos = DB::table('costos_evento')
            ->leftJoin('users', 'costos_evento.registrado_por', '=', 'users.id')
            ->select('costos_evento.*', 'users.name as doctor_name')
            ->orderBy('costos_evento.created_at', 'desc')
            ->get();

        $total = $costos->sum('costo_total');
        $porTipo = $costos->groupBy('tipo')->map(fn($g) => $g->sum('costo_total'));

        return view('superadmin.finanzas.costos', compact('costos', 'total', 'porTipo'));
    }
}
