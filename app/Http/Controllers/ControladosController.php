<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AuditLog;

class ControladosController extends Controller
{
    public function index()
    {
        $recetas = DB::table('prescriptions')
            ->join('medications', 'prescriptions.medication_id', '=', 'medications.id')
            ->join('users', 'prescriptions.doctor_id', '=', 'users.id')
            ->leftJoin('triages', 'prescriptions.triage_id', '=', 'triages.id')
            ->where('prescriptions.status', 'Requiere Autorizacion')
            ->select('prescriptions.*', 'medications.name as med_name', 'users.name as doc_name', 'triages.patient_name as pat_name')
            ->orderBy('prescriptions.created_at', 'desc')
            ->get();

        return view('superadmin.controlados.index', compact('recetas'));
    }

    public function aprobar($id)
    {
        DB::table('prescriptions')->where('id', $id)->update(['status' => 'Pendiente', 'updated_at' => now()]);
        AuditLog::create(['user_id' => auth()->id(), 'user_name' => auth()->user()->name, 'user_role' => auth()->user()->role, 'action' => 'Controlado Autorizado', 'module' => 'SuperAdmin', 'ip_address' => request()->ip(), 'details' => 'Receta ID: ' . $id]);
        return back()->with('success', 'Receta autorizada. Enviada a Farmacia.');
    }

    public function rechazar($id)
    {
        DB::table('prescriptions')->where('id', $id)->update(['status' => 'Cancelada', 'updated_at' => now()]);
        AuditLog::create(['user_id' => auth()->id(), 'user_name' => auth()->user()->name, 'user_role' => auth()->user()->role, 'action' => 'Controlado Rechazado', 'module' => 'SuperAdmin', 'ip_address' => request()->ip(), 'details' => 'Receta ID: ' . $id]);
        return back()->with('error', 'Receta rechazada y cancelada.');
    }
}
