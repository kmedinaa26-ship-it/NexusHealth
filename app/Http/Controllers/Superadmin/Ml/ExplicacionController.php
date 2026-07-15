<?php
namespace App\Http\Controllers\Superadmin\Ml;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExplicacionController extends Controller
{
    public function index()
    {
        // Mostrar lista de predicciones con link a su explicabilidad
        $predicciones = DB::table('predicciones_clinicas')
            ->where('estado', '!=', 'error')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('superadmin.ml.explicacion', compact('predicciones'));
    }

    public function show($id)
    {
        $variables = DB::table('explicacion_prediccion')
            ->where('prediccion_id', $id)
            ->orderByDesc('peso')
            ->get();

        $prediccion = DB::table('predicciones_clinicas')->where('id', $id)->first();

        return view('superadmin.ml.explicacion-show', compact('variables', 'prediccion'));
    }
}
