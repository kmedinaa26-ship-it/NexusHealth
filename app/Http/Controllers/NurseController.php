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
        $criticalPatients = Triage::where('triage_level', 'Rojo')->whereIn('status', ['En Espera', 'En Atención'])->take(20)->get();

        return view('enfermeria.dashboard', compact('critical', 'active', 'hospitalized', 'bedsAvailable', 'alerts', 'criticalPatients'));
    }

    public function triage()
    {
        $patients = Triage::whereIn('status', ['En Espera', 'En Atención'])->orderBy('created_at', 'desc')->paginate(30);
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
            'assigned_area' => 'Urgencias',
            'assigned_doctor' => $request->doctor_id ?? 1,
        ]);

        if ($request->triage_level === 'Rojo') {
            MedicalAlert::create([
                'triage_id' => $triage->id,
                'nurse_id' => Auth::id(),
                'type' => 'Crítico',
                'category' => 'Triage',
                'message' => 'Paciente Triage ROJO requiere atención inmediata',
                'severity' => 'Crítica',
                'triggered_by' => Auth::id(),
            ]);
        }

        return back()->with('success', 'Triage registrado correctamente para ' . $triage->patient_name);
    }

    public function signosVitales()
    {
        $patients = Triage::whereIn('status', ['En Espera', 'En Atención', 'Hospitalizado'])->orderBy('created_at', 'desc')->paginate(30);
        $recentVitals = VitalSign::with('triage')->orderBy('created_at', 'desc')->take(20)->get();
        return view('enfermeria.signos', compact('patients', 'recentVitals'));
    }

    public function storeVitals(Request $request)
    {
        $request->validate([
            'triage_id' => 'required',
            'temperature' => 'required',
            'heart_rate' => 'required',
            'blood_pressure' => 'required',
            'oxygen_saturation' => 'required',
        ]);

        $isCritical = false;
        $alertMessages = [];

        if ((int)$request->oxygen_saturation < 90) { $isCritical = true; $alertMessages[] = "SpO2 BAJO: {$request->oxygen_saturation}%"; }
        if ((int)$request->heart_rate > 120 || (int)$request->heart_rate < 50) { $isCritical = true; $alertMessages[] = "FC ANORMAL: {$request->heart_rate} lpm"; }
        if ((float)$request->temperature > 39.0) { $isCritical = true; $alertMessages[] = "FIEBRE ALTA: {$request->temperature}°C"; }

        VitalSign::create([
            'triage_id' => $request->triage_id,
            'patient_name' => Triage::find($request->triage_id)?->patient_name ?? '',
            'recorded_by' => Auth::id(),
            'temperature' => $request->temperature,
            'heart_rate' => $request->heart_rate,
            'blood_pressure' => $request->blood_pressure,
            'oxygen_saturation' => $request->oxygen_saturation,
            'respiratory_rate' => $request->respiratory_rate,
            'glucose' => $request->glucose,
            'weight' => $request->weight,
            'height' => $request->height,
            'is_critical' => $isCritical,
        ]);

        $triage = Triage::find($request->triage_id);
        if ($triage) {
            $triage->update([
                'vitals_ta' => $request->blood_pressure,
                'vitals_fc' => (string)$request->heart_rate,
                'vitals_temp' => (string)$request->temperature,
                'vitals_spo2' => (string)$request->oxygen_saturation,
            ]);
        }

        if ($isCritical) {
            foreach ($alertMessages as $msg) {
                MedicalAlert::create([
                    'triage_id' => $request->triage_id,
                    'patient_name' => $triage?->patient_name ?? '',
                    'type' => 'Crítico',
                    'category' => 'Signos Vitales',
                    'message' => $msg,
                    'severity' => 'Crítica',
                    'triggered_by' => Auth::id(),
                ]);
            }
        }

        $msg = 'Signos vitales registrados para ' . ($triage?->patient_name ?? 'paciente');
        if ($isCritical) { $msg .= ' - ALERTA CRÍTICA GENERADA'; }
        return back()->with('success', $msg);
    }

    public function pacientes()
    {
        $patients = Triage::orderBy('created_at', 'desc')->paginate(30);
        return view('enfermeria.pacientes', compact('patients'));
    }

    public function enviarA(Request $request, $id)
    {
        $request->validate(['destino' => 'required']);
        $triage = Triage::find($id);
        if (!$triage) { return back()->with('error', 'Paciente no encontrado'); }

        $triage->update([
            'assigned_area' => $request->destino,
            'status' => 'En Atención',
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

        return back()->with('success', "{$triage->patient_name} reasignado de {$areaAnterior} a {$request->destino}");
    }

    public function darAlta(Request $request, $id)
    {
        $triage = Triage::find($id);
        if (!$triage) { return back()->with('error', 'Paciente no encontrado'); }

        $triage->update([
            'status' => 'Dado de Alta',
            'discharge_date' => now(),
        ]);

        $hosp = Hospitalization::where('triage_id', $id)->where('status', 'Activa')->first();
        if ($hosp) {
            if ($hosp->bed_id) {
                Bed::find($hosp->bed_id)?->update(['status' => 'Disponible']);
            }
            $hosp->update(['status' => 'Alta', 'discharge_date' => now()]);
        }

        return back()->with('success', "{$triage->patient_name} fue dado de alta correctamente");
    }

    public function hospitalizacion()
    {
        $hospitalizations = Hospitalization::with('triage', 'bed', 'doctor')->orderBy('admission_date', 'desc')->paginate(30);
        $beds = Bed::orderBy('floor')->orderBy('room_number')->get();
        $doctors = User::where('role', 'like', 'Médico%')->where('status', 1)->get();
        $activePatients = Triage::whereIn('status', ['En Atención', 'Hospitalizado'])->take(50)->get();
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

        Bed::find($request->bed_id)?->update(['status' => 'Ocupada']);
        Triage::find($request->triage_id)?->update(['status' => 'Hospitalizado']);

        return back()->with('success', 'Paciente hospitalizado correctamente');
    }

    public function evolucion()
    {
        $patients = Triage::whereIn('status', ['En Atención', 'Hospitalizado'])->orderBy('created_at', 'desc')->paginate(30);
        $evolutions = NurseEvolution::with('triage')->orderBy('created_at', 'desc')->take(30)->get();
        return view('enfermeria.evolucion', compact('patients', 'evolutions'));
    }

    public function storeEvolution(Request $request)
    {
        $request->validate(['triage_id' => 'required', 'observation' => 'required']);

        $triage = Triage::find($request->triage_id);

        NurseEvolution::create([
            'triage_id' => $request->triage_id,
            'patient_name' => $triage?->patient_name ?? '',
            'nurse_id' => Auth::id(),
            'observation' => $request->observation,
            'intervention' => $request->intervention,
            'response' => $request->response,
            'priority' => $request->priority ?? 'Normal',
        ]);

        if ($request->priority === 'Urgente' || $request->priority === 'Crítica') {
            MedicalAlert::create([
                'triage_id' => $request->triage_id,
                'patient_name' => $triage?->patient_name ?? '',
                'type' => 'Crítico',
                'category' => 'Hospitalización',
                'message' => 'Enfermería solicita revisión: ' . substr($request->observation, 0, 100),
                'severity' => $request->priority === 'Crítica' ? 'Crítica' : 'Alta',
                'triggered_by' => Auth::id(),
            ]);
        }

        return back()->with('success', 'Nota de evolución registrada');
    }

    public function alertas()
    {
        $alerts = MedicalAlert::with('triage')->orderBy('is_read')->orderBy('created_at', 'desc')->paginate(30);
        return view('enfermeria.alertas', compact('alerts'));
    }

    public function markAlertRead($id)
    {
        MedicalAlert::find($id)?->update(['is_read' => true]);
        return back()->with('success', 'Alerta marcada como leída');
    }

    public function medicamentos()
    {
        $meds = Medication::where('enfermera_can_administer', true)->orWhere('required_level', 'Enfermera')->orderBy('name')->get();
        return view('enfermeria.medicamentos', compact('meds'));
    }

    public function documentacion()
    {
        $patients = Triage::whereIn('status', ['En Atención', 'Hospitalizado'])->orderBy('created_at', 'desc')->paginate(30);
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
