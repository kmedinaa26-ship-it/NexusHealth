<?php

namespace App\Http\Controllers;

use App\Models\Triage;
use App\Models\Bed;
use App\Models\NurseEvolution;
use App\Models\Hospitalization;
use App\Models\AuditLog;
use App\Models\MedicalAlert;
use App\Models\User;
use App\Models\Medication;
use App\Models\RestockRequest;
use App\Models\PatientMedication;
use Illuminate\Http\Request;

class NurseController extends Controller
{
    public function dashboard()
    {
        $criticalPatients = Triage::where('triage_level', 'Rojo')->whereIn('status', ['En Espera', 'En Atención'])->limit(20)->get();
        $hospitalized = Triage::where('status', 'Hospitalizado')->count();
        $bedsAvailable = Bed::where('status', 'Disponible')->count();
        $critical = Triage::where('triage_level', 'Rojo')->whereIn('status', ['En Espera', 'En Atención'])->count();
        $alerts = MedicalAlert::where('is_read', 0)->orderBy('created_at', 'desc')->take(5)->get();
        return view('enfermeria.dashboard', compact('criticalPatients', 'hospitalized', 'bedsAvailable', 'critical', 'alerts'));
    }

    public function triage()
    {
        $patients = Triage::orderBy('created_at', 'desc')->paginate(30);
        $colors = ['Rojo' => '#DC2626', 'Naranja' => '#EA580C', 'Amarillo' => '#F59E0B', 'Verde' => '#F97316', 'Azul' => '#3B82F6'];
        return view('enfermeria.triage', compact('patients', 'colors'));
    }

    public function signosVitales()
    {
        $patients = Triage::whereIn('status', ['En Atención', 'Hospitalizado'])->orderBy('created_at', 'desc')->paginate(30);
        $recentVitals = Triage::whereIn('status', ['En Atención', 'Hospitalizado'])->whereNotNull('vitals_fc')->orderBy('updated_at', 'desc')->take(10)->get();
        return view('enfermeria.signos', compact('patients', 'recentVitals'));
    }

    public function storeSignos(Request $request)
    {
        $request->validate(['triage_id' => 'required|exists:triages,id']);
        $triage = Triage::findOrFail($request->triage_id);
        $triage->update($request->only(['vitals_ta', 'vitals_fc', 'vitals_temp', 'vitals_spo2']));
        AuditLog::create(['user_id' => auth()->id(), 'user_name' => auth()->user()->name, 'user_role' => auth()->user()->role, 'action' => 'Signos Vitales', 'module' => 'Enfermería', 'ip_address' => $request->ip(), 'details' => 'Paciente: ' . $triage->patient_name]);
        return back()->with('success', 'Signos vitales registrados.');
    }

    public function hospitalizacion()
    {
        $activePatients = Triage::whereIn('status', ['En Atención', 'Hospitalizado'])->orderBy('created_at', 'desc')->paginate(30);
        $beds = Bed::where('status', 'Disponible')->orderBy('area')->orderBy('room_number')->get();
        $doctors = User::whereIn('role', ['Médico A', 'Médico B', 'Médico C'])->where('status', 'Activo')->get();
        $hospitalizations = Hospitalization::with('triage', 'bed')->orderBy('created_at', 'desc')->paginate(20);
        return view('enfermeria.hospitalizacion', compact('activePatients', 'beds', 'doctors', 'hospitalizations'));
    }

    public function storeHospitalization(Request $request)
    {
        $request->validate(['triage_id' => 'required|exists:triages,id', 'bed_id' => 'required|exists:beds,id', 'diagnosis' => 'required']);
        $triage = Triage::findOrFail($request->triage_id);
        $bed = Bed::findOrFail($request->bed_id);
        Hospitalization::create(['triage_id' => $request->triage_id, 'bed_id' => $request->bed_id, 'doctor_id' => $request->doctor_id ?? null, 'nurse_id' => auth()->id(), 'admission_date' => now(), 'diagnosis' => $request->diagnosis, 'status' => 'Activa']);
        $bed->update(['status' => 'Ocupada', 'patient_name' => $triage->patient_name, 'triage_level' => $triage->triage_level]);
        $triage->update(['status' => 'Hospitalizado']);
        AuditLog::create(['user_id' => auth()->id(), 'user_name' => auth()->user()->name, 'user_role' => auth()->user()->role, 'action' => 'Hospitalización', 'module' => 'Enfermería', 'ip_address' => $request->ip(), 'details' => $triage->patient_name]);
        return back()->with('success', 'Paciente hospitalizado.');
    }

    public function evolucion()
    {
        $patients = Triage::whereIn('status', ['En Atención', 'Hospitalizado'])->orderBy('created_at', 'desc')->paginate(30);
        $evolutions = NurseEvolution::orderBy('created_at', 'desc')->paginate(30);
        return view('enfermeria.evolucion', compact('patients', 'evolutions'));
    }

    public function storeEvolucion(Request $request)
    {
        $request->validate(['triage_id' => 'required|exists:triages,id', 'notes' => 'required']);
        $triage = Triage::findOrFail($request->triage_id);
        NurseEvolution::create(['triage_id' => $request->triage_id, 'nurse_id' => auth()->id(), 'notes' => $request->notes, 'vitals' => $request->vitals ?? null]);
        AuditLog::create(['user_id' => auth()->id(), 'user_name' => auth()->user()->name, 'user_role' => auth()->user()->role, 'action' => 'Nota de Evolución', 'module' => 'Enfermería', 'ip_address' => $request->ip(), 'details' => $triage->patient_name]);
        return back()->with('success', 'Nota de evolución registrada.');
    }

    public function documentacion()
    {
        $patients = Triage::whereIn('status', ['En Atención', 'Hospitalizado'])->orderBy('created_at', 'desc')->paginate(30);
        $evolutions = NurseEvolution::orderBy('created_at', 'desc')->take(20)->get();
        $vitals = Triage::whereIn('status', ['En Atención', 'Hospitalizado'])->whereNotNull('vitals_fc')->orderBy('updated_at', 'desc')->take(20)->get();
        $colors = ['Rojo' => '#DC2626', 'Naranja' => '#EA580C', 'Amarillo' => '#F59E0B', 'Verde' => '#F97316'];
        return view('enfermeria.documentacion', compact('patients', 'evolutions', 'vitals', 'colors'));
    }

    public function pacientes()
    {
        $patients = Triage::orderBy('created_at', 'desc')->paginate(30);
        $colors = ['Rojo' => '#DC2626', 'Naranja' => '#EA580C', 'Amarillo' => '#F59E0B', 'Verde' => '#F97316', 'Azul' => '#3B82F6'];
        $statusColors = ['En Espera' => '#F59E0B', 'En Atención' => '#EA580C', 'Hospitalizado' => '#DC2626', 'Alta' => '#F97316'];
        return view('enfermeria.pacientes', compact('patients', 'colors', 'statusColors'));
    }

    public function reasignarPaciente(Request $request, $id)
    {
        $triage = Triage::findOrFail($id);
        $triage->update($request->only(['status', 'bed_id']));
        AuditLog::create(['user_id' => auth()->id(), 'user_name' => auth()->user()->name, 'user_role' => auth()->user()->role, 'action' => 'Reasignar Paciente', 'module' => 'Enfermería', 'ip_address' => $request->ip(), 'details' => $triage->patient_name]);
        return back()->with('success', 'Paciente reasignado.');
    }

    public function alertas()
    {
        $alerts = MedicalAlert::orderBy('created_at', 'desc')->paginate(30);
        return view('enfermeria.alertas', compact('alerts'));
    }

    public function reportes()
    {
        $patients = Triage::whereIn("status", ["En Atención", "Hospitalizado"])->orderBy("created_at", "desc")->paginate(30);
        $evolutions = NurseEvolution::orderBy("created_at", "desc")->take(20)->get();
        return view("enfermeria.reportes", compact("patients", "evolutions"));
    }

    public function medicamentos()
    {
        $meds = Medication::where('enfermera_can_administer', true)->orderBy('name')->get();
        return view('enfermeria.medicamentos', compact('meds'));
    }

    public function solicitudesFarmacia()
    {
        $meds = RestockRequest::with('medication')->orderBy('created_at', 'desc')->paginate(30);
        return view('enfermeria.solicitudes', compact('meds'));
    }

    public function mapaCamas()
    {
        $beds = Bed::orderBy('area')->orderBy('floor')->orderBy('room_number')->orderBy('bed_number')->get();
        $grouped = $beds->groupBy('area');
        $stats = ['total' => $beds->count(), 'disponibles' => $beds->where('status', 'Disponible')->count(), 'ocupadas' => $beds->where('status', 'Ocupada')->count(), 'limpieza' => $beds->where('status', 'Limpieza')->count(), 'mantenimiento' => $beds->where('status', 'Mantenimiento')->count()];
        return view('enfermeria.mapa_camas', compact('grouped', 'stats'));
    }

    public function asignarCama(Request $request, $id)
    {
        $cama = Bed::findOrFail($id);
        if ($request->filled('triage_id')) {
            $triage = Triage::findOrFail($request->triage_id);
        } else {
            $request->validate(['new_patient_name' => 'required|string|max:255']);
            $triage = Triage::create(['patient_name' => $request->new_patient_name, 'triage_level' => $request->new_triage_level ?? 'Verde', 'status' => 'Hospitalizado', 'age' => $request->new_age ?? 30, 'symptoms' => 'Por determinar', 'chief_complaint' => 'Ingreso directo desde Mapa de Camas', 'created_by' => auth()->id()]);
        }
        $cama->status = 'Ocupada';
        $cama->patient_name = $triage->patient_name;
        $cama->triage_level = $triage->triage_level;
        if ($triage->triage_level == 'Rojo') { $cama->area = 'UCI'; } elseif ($triage->triage_level == 'Naranja') { $cama->area = 'Urgencias'; } else { $cama->area = 'Hospitalización'; }
        $cama->save();
        if ($triage->status != 'Hospitalizado') { $triage->update(['status' => 'Hospitalizado']); }
        AuditLog::create(['user_id' => auth()->id(), 'user_name' => auth()->user()->name, 'user_role' => auth()->user()->role, 'action' => 'Cama Asignada', 'module' => 'Enfermería - Mapa Camas', 'ip_address' => $request->ip(), 'details' => $triage->patient_name]);
        return back()->with('success', "Cama asignada a {$triage->patient_name}");
    }

    public function liberarCama($id)
    {
        $cama = Bed::findOrFail($id);
        $paciente = $cama->patient_name;
        $cama->status = 'Limpieza';
        $cama->patient_name = null;
        $cama->triage_level = null;
        $cama->save();
        AuditLog::create(['user_id' => auth()->id(), 'user_name' => auth()->user()->name, 'user_role' => auth()->user()->role, 'action' => 'Cama Liberada', 'module' => 'Enfermería - Mapa Camas', 'ip_address' => request()->ip(), 'details' => $paciente]);
        return back()->with('success', "Cama liberada.");
    }

    public function storeTriage(Request $request)
    {
        $request->validate(["patient_name" => "required", "triage_level" => "required", "symptoms" => "required"]);
        Triage::create(["patient_name" => $request->patient_name, "triage_level" => $request->triage_level, "status" => "En Espera", "age" => $request->age ?? 30, "symptoms" => $request->symptoms, "chief_complaint" => $request->chief_complaint ?? $request->symptoms, "created_by" => auth()->id()]);
        return back()->with("success", "Paciente registrado en triage.");
    }

    public function darAlta($id)
    {
        $triage = Triage::findOrFail($id);
        $triage->update(["status" => "Alta"]);
        if ($triage->bed_id) {
            $bed = Bed::find($triage->bed_id);
            if ($bed) { $bed->update(["status" => "Limpieza", "patient_name" => null, "triage_level" => null]); }
        }
        AuditLog::create(["user_id" => auth()->id(), "user_name" => auth()->user()->name, "user_role" => auth()->user()->role, "action" => "Alta Médica", "module" => "Enfermería", "ip_address" => request()->ip(), "details" => $triage->patient_name]);
        return back()->with("success", "Paciente dado de alta.");
    }

    public function markAlertRead($id)
    {
        $alert = MedicalAlert::findOrFail($id);
        $alert->update(["is_read" => 1]);
        return back()->with("success", "Alerta marcada como leída.");
    }


    public function pacientesParaCamas()
    {
        $pacientes = Triage::whereIn('status', ['En Espera', 'En Atención'])->whereNotNull('patient_name')->select('id', 'patient_name', 'triage_level')->orderBy('created_at', 'desc')->limit(50)->get();
        return response()->json($pacientes);
    }
}
