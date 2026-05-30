<?php

namespace App\Http\Controllers;

use App\Models\Triage;
use App\Models\VitalSign;
use App\Models\Hospitalization;
use App\Models\MedicalAlert;
use App\Models\NurseEvolution;
use App\Models\Bed;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Medication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NurseController extends Controller
{
    public function dashboard()
    {
        $critical = Triage::where('triage_level', 'Rojo')->whereIn('status', ['En Espera', 'En Atención'])->count();
        $active = Triage::whereIn('status', ['En Espera', 'En Atención'])->count();
        $hospitalized = Triage::where('status', 'Hospitalizado')->count();
        $bedsAvailable = Bed::where('status', 'Disponible')->count();
        $alerts = MedicalAlert::where('is_read', false)->orderBy('created_at', 'desc')->take(5)->get();
        $criticalPatients = Triage::where('triage_level', 'Rojo')->whereIn('status', ['En Espera', 'En Atención'])->get();

        return view('enfermeria.dashboard', compact('critical', 'active', 'hospitalized', 'bedsAvailable', 'alerts', 'criticalPatients'));
    }

    public function triage()
    {
        $patients = Triage::whereIn('status', ['En Espera', 'En Atención'])->orderBy('created_at', 'desc')->get();
        $doctors = User::where('role', 'like', 'Médico%')->where('status', 1)->get();
        return view('enfermeria.triage', compact('patients', 'doctors'));
    }

    public function storeTriage(Request $request)
    {
        $request->validate([
            'patient_name' => 'required',
            'age' => 'required|integer',
            'triage_level' => 'required',
            'symptoms' => 'required',
        ]);

        $triage = Triage::create([
            'patient_name' => $request->patient_name,
            'age' => $request->age,
            'triage_level' => $request->triage_level,
            'symptoms' => $request->symptoms,
            'status' => 'En Espera',
            'nurse_id' => Auth::id(),
        ]);

        if ($request->triage_level === 'Rojo') {
            MedicalAlert::create([
                'triage_id' => $triage->id,
                'nurse_id' => Auth::id(),
                'type' => 'Triage Critico',
                'severity' => 'Critica',
                'message' => 'Paciente Triage ROJO requiere atencion inmediata',
            ]);
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'user_role' => Auth::user()->role,
            'action' => 'Triage Aplicado',
            'module' => 'Enfermeria',
            'ip_address' => $request->ip(),
            'details' => "Paciente: {$triage->patient_name} - Nivel: {$triage->triage_level}",
        ]);

        return back()->with('success', 'Triage registrado correctamente para ' . $triage->patient_name);
    }

    public function signosVitales()
    {
        $patients = Triage::whereIn('status', ['En Espera', 'En Atención', 'Hospitalizado'])->orderBy('created_at', 'desc')->get();
        $recentVitals = VitalSign::with('triage', 'nurse')->orderBy('created_at', 'desc')->take(20)->get();
        return view('enfermeria.signos', compact('patients', 'recentVitals'));
    }

    public function storeVitals(Request $request)
    {
        $request->validate([
            'triage_id' => 'required',
            'ta' => 'required',
            'fc' => 'required',
            'temp' => 'required',
            'spo2' => 'required',
        ]);

        $isCritical = false;
        $alertMessages = [];

        if ((int)$request->spo2 < 90) { $isCritical = true; $alertMessages[] = "SpO2 BAJO: {$request->spo2}%"; }
        if ((int)$request->fc > 120 || (int)$request->fc < 50) { $isCritical = true; $alertMessages[] = "FC ANORMAL: {$request->fc} lpm"; }
        if ((float)$request->temp > 39.0) { $isCritical = true; $alertMessages[] = "FIEBRE ALTA: {$request->temp}C"; }

        VitalSign::create([
            'triage_id' => $request->triage_id,
            'nurse_id' => Auth::id(),
            'ta' => $request->ta,
            'fc' => $request->fc,
            'temp' => $request->temp,
            'spo2' => $request->spo2,
            'fr' => $request->fr,
            'glucose' => $request->glucose,
            'pain_scale' => $request->pain_scale,
            'notes' => $request->notes,
            'is_critical' => $isCritical,
        ]);

        $triage = Triage::find($request->triage_id);
        $triage->update([
            'vitals_ta' => $request->ta,
            'vitals_fc' => $request->fc,
            'vitals_temp' => $request->temp,
            'vitals_spo2' => $request->spo2,
        ]);

        if ($isCritical) {
            foreach ($alertMessages as $msg) {
                MedicalAlert::create([
                    'triage_id' => $request->triage_id,
                    'nurse_id' => Auth::id(),
                    'type' => 'Signos Vitales Criticos',
                    'severity' => 'Critica',
                    'message' => $msg,
                ]);
            }
        }

        $msg = 'Signos vitales registrados para ' . $triage->patient_name;
        if ($isCritical) { $msg .= ' - ALERTA CRITICA GENERADA'; }
        return back()->with('success', $msg);
    }

    public function pacientes()
    {
        $patients = Triage::with('vitalSigns')->orderBy('created_at', 'desc')->get();
        return view('enfermeria.pacientes', compact('patients'));
    }

    public function enviarA(Request $request, $id)
    {
        $request->validate(['destino' => 'required']);
        $triage = Triage::find($id);
        if (!$triage) { return back()->with('error', 'Paciente no encontrado'); }

        $areaAnterior = $triage->assigned_area;
        $triage->update([
            'assigned_area' => $request->destino,
            'status' => 'En Atención',
        ]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'user_role' => Auth::user()->role,
            'action' => 'Paciente Enviado',
            'module' => 'Enfermeria',
            'ip_address' => $request->ip(),
            'details' => "{$triage->patient_name} enviado a {$request->destino}" . ($areaAnterior ? " (antes en {$areaAnterior})" : ''),
        ]);

        return back()->with('success', "{$triage->patient_name} fue enviado a {$request->destino} correctamente");
    }

    public function reasignarPaciente(Request $request, $id)
    {
        $request->validate(['destino' => 'required']);
        $triage = Triage::find($id);
        if (!$triage) { return back()->with('error', 'Paciente no encontrado'); }

        $areaAnterior = $triage->assigned_area;
        $triage->update(['assigned_area' => $request->destino]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'user_role' => Auth::user()->role,
            'action' => 'Paciente Reasignado',
            'module' => 'Enfermeria',
            'ip_address' => $request->ip(),
            'details' => "{$triage->patient_name} reasignado de {$areaAnterior} a {$request->destino}",
        ]);

        return back()->with('success', "{$triage->patient_name} reasignado de {$areaAnterior} a {$request->destino}");
    }

    public function darAlta(Request $request, $id)
    {
        $triage = Triage::find($id);
        if (!$triage) { return back()->with('error', 'Paciente no encontrado'); }

        $triage->update(['status' => 'Dado de Alta']);

        // Liberar cama si estaba hospitalizado
        $hosp = Hospitalization::where('triage_id', $id)->where('status', 'Activa')->first();
        if ($hosp) {
            Bed::find($hosp->bed_id)->update(['status' => 'Disponible']);
            $hosp->update(['status' => 'Alta', 'discharge_date' => now()]);
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'user_role' => Auth::user()->role,
            'action' => 'Paciente Dado de Alta',
            'module' => 'Enfermeria',
            'ip_address' => $request->ip(),
            'details' => "{$triage->patient_name} dado de alta",
        ]);

        return back()->with('success', "{$triage->patient_name} fue dado de alta correctamente");
    }

    public function hospitalizacion()
    {
        $hospitalizations = Hospitalization::with('triage', 'bed', 'doctor', 'nurse')->orderBy('admission_date', 'desc')->get();
        $beds = Bed::orderBy('floor')->orderBy('room_number')->get();
        $doctors = User::where('role', 'like', 'Médico%')->where('status', 1)->get();
        $activePatients = Triage::whereIn('status', ['En Atención', 'Hospitalizado'])->get();
        return view('enfermeria.hospitalizacion', compact('hospitalizations', 'beds', 'doctors', 'activePatients'));
    }

    public function storeHospitalization(Request $request)
    {
        $request->validate(['triage_id' => 'required', 'bed_id' => 'required', 'doctor_id' => 'required']);

        Hospitalization::create([
            'triage_id' => $request->triage_id,
            'bed_id' => $request->bed_id,
            'doctor_id' => $request->doctor_id,
            'nurse_id' => Auth::id(),
            'admission_date' => now(),
            'diagnosis' => $request->diagnosis,
            'status' => 'Activa',
        ]);

        Bed::find($request->bed_id)->update(['status' => 'Ocupada']);
        Triage::find($request->triage_id)->update(['status' => 'Hospitalizado']);

        return back()->with('success', 'Paciente hospitalizado correctamente');
    }

    public function evolucion()
    {
        $patients = Triage::whereIn('status', ['En Atención', 'Hospitalizado'])->orderBy('created_at', 'desc')->get();
        $evolutions = NurseEvolution::with('triage', 'nurse')->orderBy('created_at', 'desc')->take(30)->get();
        return view('enfermeria.evolucion', compact('patients', 'evolutions'));
    }

    public function storeEvolution(Request $request)
    {
        $request->validate(['triage_id' => 'required', 'notes' => 'required']);

        NurseEvolution::create([
            'triage_id' => $request->triage_id,
            'nurse_id' => Auth::id(),
            'notes' => $request->notes,
            'priority' => $request->priority ?? 'Normal',
            'alert_doctor' => $request->has('alert_doctor'),
        ]);

        if ($request->has('alert_doctor')) {
            MedicalAlert::create([
                'triage_id' => $request->triage_id,
                'nurse_id' => Auth::id(),
                'type' => 'Evolucion - Alerta Medico',
                'severity' => $request->priority === 'Urgente' ? 'Alta' : 'Media',
                'message' => 'Enfermeria solicita revision: ' . substr($request->notes, 0, 100),
            ]);
        }

        return back()->with('success', 'Nota de evolucion registrada');
    }

    public function alertas()
    {
        $alerts = MedicalAlert::with('triage', 'nurse')->orderBy('is_read')->orderBy('created_at', 'desc')->get();
        return view('enfermeria.alertas', compact('alerts'));
    }

    public function markAlertRead($id)
    {
        MedicalAlert::find($id)->update(['is_read' => true, 'read_at' => now()]);
        return back()->with('success', 'Alerta marcada como leida');
    }

    public function medicamentos()
    {
        $meds = Medication::where('enfermera_can_administer', true)->orWhere('required_level', 'Enfermera')->orderBy('name')->get();
        return view('enfermeria.medicamentos', compact('meds'));
    }

    public function documentacion()
    {
        $patients = Triage::whereIn('status', ['En Atención', 'Hospitalizado'])->orderBy('created_at', 'desc')->get();
        return view('enfermeria.documentacion', compact('patients'));
    }

    public function solicitudesFarmacia()
    {
        $meds = Medication::orderBy('name')->get();
        return view('enfermeria.solicitudes', compact('meds'));
    }

    public function reportes()
    {
        return view('enfermeria.reportes');
    }
}
