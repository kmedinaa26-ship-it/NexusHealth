<?php
namespace App\Http\Controllers\Superadmin;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AlertasMlController extends Controller
{
    public function index()
    {
        $alertas = DB::table('alertas_ml')
            ->orderBy('created_at', 'desc')
            ->get();

        $noLeidas = DB::table('alertas_ml')->where('leida', 0)->count();

        return view('superadmin.alertas-ml', compact('alertas', 'noLeidas'));
    }
}
