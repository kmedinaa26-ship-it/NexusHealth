<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Triage;
use App\Models\Medication;
use App\Models\Bed;
use App\Models\Hospitalization;
use App\Models\NurseEvolution;
use App\Models\MedicalAlert;
use App\Models\VitalSign;
use App\Models\User;

class DoctorController extends Controller
{
    public function dashboard()
    {
        if (request()->has('cambiar')) {
            session()->forget(['doctor_profile', 'doctor_name']);
            return view('medico.seleccion');
        }
        $role = session('doctor_profile', null);
        if (!$role) return view('medico.seleccion');
        return $this->dashboardForRole($role);
    }

    private function dashboardForRole($role)
    {
        $isA = $role === 'Médico A';
        $isB = $role === 'Médico B';
        $isC = $role === 'Médico C';
        $doctorName = session('doctor_name', 'Médico');
        $uid = Auth::id();

        $misPacientes = Triage::where('assigned_doctor', $uid)->whereIn('status', ['En Atención', 'Hospitalizado'])->count();
        $criticos = Triage::where('triage_level', 'Rojo')->whereIn('status', ['En Espera', 'En Atención'])->count();
        $hospitalizados = Triage::where('status', 'Hospitalizado')->count();
        $recetasPendientes = \DB::table('prescriptions')->where('doctor_id', $uid)->where('status', 'Pendiente')->count();
        $camasDisponibles = Bed::where('status', 'Disponible')->count();
        $camasOcupadas = Bed::where('status', 'Ocupada')->count();
        $stockBajo = Medication::where('stock', '<', 10)->count();
        $serviciosPendientes = \DB::table('service_requests')->where('doctor_id', $uid)->where('status', 'Pendiente')->count();

        $alerts = MedicalAlert::where('is_read', 0)->orderBy('created_at', 'desc')->limit(5)->get();
        $recentVitals = VitalSign::orderBy('created_at', 'desc')->limit(6)->get();
        $misPacientesLista = Triage::where('assigned_doctor', $uid)->whereIn('status', ['En Atención', 'Hospitalizado'])->orderBy('triage_level', 'asc')->limit(8)->get();

        return view('medico.dashboard', compact(
            'role', 'isA', 'isB', 'isC', 'doctorName',
            'misPacientes', 'criticos', 'hospitalizados', 'recetasPendientes',
            'camasDisponibles', 'camasOcupadas', 'stockBajo', 'serviciosPendientes',
            'alerts', 'recentVitals', 'misPacientesLista'
        ));
    }

    public function seleccionar()
    {
        session()->forget(['doctor_profile', 'doctor_name']);
        return view('medico.seleccion');
    }

    public function seleccionarPerfil(Request $request)
    {
        $pin = $request->pin;
        $profileMap = ['1111' => 'Médico A', '2222' => 'Médico B', '3333' => 'Médico C'];
        $nameMap = ['1111' => 'Dr. Kenia Medina', '2222' => 'Dr. SF Gilkey', '3333' => 'Dr. KM Azuara'];

        if (isset($profileMap[$pin])) {
            session(['doctor_profile' => $profileMap[$pin], 'doctor_name' => $nameMap[$pin]]);
            return redirect()->route('medico.dashboard');
        }
        return back()->with('error', 'PIN incorrecto. Intenta de nuevo.');
    }

    public function validarPin(Request $request)
    {
        $pin = $request->pin;
        if (in_array($pin, ['1111', '2222', '3333'])) {
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }

    // PACIENTES
    public function pacientes()
    {
        $role = session('doctor_profile', 'Médico C');
        $uid = Auth::id();
        $isA = $role === 'Médico A';
        $medicos = $isA ? User::whereIn('role', ['Médico A', 'Médico B', 'Médico C'])->where('status', 1)->get() : collect();

        if ($isA) {
            $pacientes = Triage::whereIn('status', ['En Espera', 'En Atención', 'Hospitalizado'])->orderBy('triage_level', 'asc')->get();
        } else {
            $pacientes = Triage::where('assigned_doctor', $uid)->whereIn('status', ['En Atención', 'Hospitalizado'])->orderBy('triage_level', 'asc')->get();
        }
        return view('medico.pacientes', compact('pacientes', 'role', 'isA', 'medicos'));
    }

    public function registrarPaciente()
    {
        $role = session('doctor_profile', 'Médico C');
        if ($role === 'Médico C') return redirect()->route('medico.dashboard')->with('error', 'Sin acceso');
        $medicos = User::whereIn('role', ['Médico A', 'Médico B', 'Médico C'])->where('status', 1)->get();
        return view('medico.registrar-paciente', compact('role', 'medicos'));
    }

    public function storeNuevoPaciente(Request $request)
    {
        $role = session('doctor_profile', 'Médico C');
        if ($role === 'Médico C') return redirect()->route('medico.dashboard')->with('error', 'Sin acceso');

        $request->validate([
            'patient_name' => 'required',
            'age' => 'required|numeric',
            'gender' => 'required',
            'chief_complaint' => 'required',
            'triage_level' => 'required',
        ]);

        $triage = Triage::create([
            'patient_name' => $request->patient_name,
            'age' => $request->age,
            'gender' => $request->gender,
            'chief_complaint' => $request->chief_complaint,
            'symptoms' => $request->symptoms,
            'allergies' => $request->allergies,
            'triage_level' => $request->triage_level,
            'status' => 'En Espera',
            'assigned_doctor' => $request->assigned_doctor ?? Auth::id(),
            'blood_type' => $request->blood_type,
            'insurance' => $request->insurance,
            'emergency_contact' => $request->emergency_contact,
            'diagnostico' => $request->diagnostico,
            'cie10' => $request->cie10,
            'doctor_notes' => $request->doctor_notes,
        ]);

        if ($request->triage_level === 'Rojo') {
            MedicalAlert::create([
                'type' => 'Paciente Crítico',
                'message' => 'Nuevo paciente TRIAGE ROJO: ' . $request->patient_name . ' - ' . $request->chief_complaint,
                'severity' => 'Crítica',
                'triage_id' => $triage->id,
                'nurse_id' => Auth::id(),
                'is_read' => false,
            ]);
        }

        return redirect()->route('medico.pacientes')->with('success', 'Paciente registrado: ' . $request->patient_name);
    }

    public function editarPaciente($id)
    {
        $role = session('doctor_profile', 'Médico C');
        $paciente = Triage::findOrFail($id);
        $medicos = User::whereIn('role', ['Médico A', 'Médico B', 'Médico C'])->where('status', 1)->get();
        $hosp = Hospitalization::where('triage_id', $id)->where('status', 'Activa')->first();
        return view('medico.editar-paciente', compact('paciente', 'role', 'medicos', 'hosp'));
    }

    public function actualizarPaciente(Request $request, $id)
    {
        $p = Triage::findOrFail($id);
        $p->update($request->only(['patient_name', 'age', 'gender', 'chief_complaint', 'diagnostico', 'cie10', 'tratamiento', 'doctor_notes', 'status']));
        return redirect()->route('medico.pacientes')->with('success', 'Paciente actualizado');
    }

    public function asignarPaciente(Request $request, $id)
    {
        Triage::findOrFail($id)->update(['assigned_doctor' => $request->doctor_id, 'status' => 'En Atención']);
        return back()->with('success', 'Paciente asignado');
    }

    public function darAlta(Request $request, $id)
    {
        $p = Triage::findOrFail($id);
        $p->update([
            'status' => 'Alta',
            'discharge_date' => now(),
            'discharge_type' => 'Alta Hospitalaria',
            'discharge_doctor_id' => Auth::id(),
            'discharge_notes' => $request->discharge_notes ?? 'Alta médica',
        ]);
        $hosp = Hospitalization::where('triage_id', $id)->where('status', 'Activa')->first();
        if ($hosp) {
            $hosp->update(['status' => 'Alta', 'discharge_date' => now()]);
            Bed::find($hosp->bed_id)?->update(['status' => 'Disponible']);
        }
        return back()->with('success', 'Paciente dado de alta');
    }

    // CONSULTA
    public function consulta()
    {
        $role = session('doctor_profile', 'Médico C');
        $uid = Auth::id();
        $pacientes = Triage::where('assigned_doctor', $uid)->whereIn('status', ['En Atención', 'En Espera'])->orderBy('created_at', 'desc')->get();
        return view('medico.consulta', compact('pacientes', 'role'));
    }

    // DIAGNÓSTICOS
    public function diagnosticos()
    {
        $role = session('doctor_profile', 'Médico C');
        $uid = Auth::id();
        $pacientes = Triage::where('assigned_doctor', $uid)->whereIn('status', ['En Atención', 'Hospitalizado'])->orderBy('created_at', 'desc')->get();
        return view('medico.diagnosticos', compact('pacientes', 'role'));
    }

    public function storeDiagnostico(Request $request)
    {
        $p = Triage::findOrFail($request->triage_id);
        $p->update(['diagnostico' => $request->diagnostico, 'cie10' => $request->cie10, 'doctor_notes' => $request->doctor_notes]);
        return back()->with('success', 'Diagnóstico guardado');
    }

    // RECETAS
    public function recetas()
    {
        $role = session('doctor_profile', 'Médico C');
        $uid = Auth::id();
        $isC = $role === 'Médico C';
        $isA = $role === 'Médico A';

        $medQuery = Medication::orderBy('name');
        if ($isC) $medQuery->where('required_level', 'C');
        elseif (!$isA) $medQuery->where('required_level', '!=', 'A');
        $medicamentos = $medQuery->get();

        $pacientes = Triage::where('assigned_doctor', $uid)->whereIn('status', ['En Atención', 'Hospitalizado'])->get();
        $misRecetas = \DB::table('prescriptions')->where('doctor_id', $uid)->orderBy('created_at', 'desc')->take(15)->get();

        return view('medico.recetas', compact('medicamentos', 'pacientes', 'misRecetas', 'role', 'isC', 'isA'));
    }

    public function storeReceta(Request $request)
    {
        $role = session('doctor_profile', 'Médico C');
        $med = Medication::find($request->medication_id);
        if ($role === 'Médico C' && $med && $med->required_level !== 'C') {
            return back()->with('error', 'No puedes recetar este medicamento');
        }
        if ($role === 'Médico B' && $med && $med->required_level === 'A') {
            return back()->with('error', 'Solo Médico A puede recetar controlados');
        }

        \DB::table('prescriptions')->insert([
            'triage_id' => $request->triage_id,
            'medication_id' => $request->medication_id,
            'doctor_id' => Auth::id(),
            'dosis' => $request->dosis,
            'frecuencia' => $request->frecuencia,
            'duracion' => $request->duracion,
            'indicaciones' => $request->indicaciones,
            'status' => 'Pendiente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return back()->with('success', 'Receta enviada a Farmacia');
    }

    public function cancelarReceta($id)
    {
        \DB::table('prescriptions')->where('id', $id)->update(['status' => 'Cancelada', 'updated_at' => now()]);
        return back()->with('success', 'Receta cancelada');
    }

    // SIGNOS VITALES
    public function signos()
    {
        $role = session('doctor_profile', 'Médico C');
        $vitals = VitalSign::orderBy('created_at', 'desc')->take(25)->get();
        return view('medico.signos', compact('vitals', 'role'));
    }

    // ESTUDIOS
    public function estudios()
    {
        $role = session('doctor_profile', 'Médico C');
        $uid = Auth::id();
        $pacientes = Triage::where('assigned_doctor', $uid)->whereIn('status', ['En Atención', 'Hospitalizado'])->get();
        $estudios = \DB::table('medical_studies')->where('doctor_id', $uid)->orderBy('created_at', 'desc')->take(20)->get();
        return view('medico.estudios', compact('pacientes', 'estudios', 'role'));
    }

    public function storeEstudio(Request $request)
    {
        \DB::table('medical_studies')->insert([
            'triage_id' => $request->triage_id, 'doctor_id' => Auth::id(),
            'tipo' => $request->tipo, 'prioridad' => $request->prioridad,
            'notas' => $request->notas, 'status' => 'Solicitado',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return back()->with('success', 'Estudio solicitado');
    }

    public function resultadoEstudio(Request $request, $id)
    {
        \DB::table('medical_studies')->where('id', $id)->update([
            'status' => 'Completado', 'notas' => $request->resultado,
            'updated_at' => now(),
        ]);
        return back()->with('success', 'Resultado registrado');
    }

    public function eliminarEstudio($id)
    {
        \DB::table('medical_studies')->where('id', $id)->delete();
        return back()->with('success', 'Estudio eliminado');
    }

    // TRATAMIENTOS
    public function tratamientos()
    {
        if (session('doctor_profile') !== 'Médico A') return redirect()->route('medico.dashboard')->with('error', 'Solo Médico A');
        $uid = Auth::id();
        $pacientes = Triage::where('assigned_doctor', $uid)->whereIn('status', ['En Atención', 'Hospitalizado'])->get();
        return view('medico.tratamientos', compact('pacientes'));
    }

    // HOSPITALIZACIÓN
    public function hospitalizacion()
    {
        $role = session('doctor_profile', 'Médico C');
        if ($role === 'Médico C') return redirect()->route('medico.dashboard')->with('error', 'Sin acceso');
        $hospitalizados = Hospitalization::where('status', 'Activa')->orderBy('admission_date', 'desc')->get();
        $camas = Bed::orderBy('floor', 'asc')->orderBy('room_number', 'asc')->get();
        $pacientes = Triage::whereIn('status', ['En Atención', 'En Espera'])->get();
        return view('medico.hospitalizacion', compact('hospitalizados', 'camas', 'pacientes', 'role'));
    }

    public function storeHospitalizacion(Request $request)
    {
        Hospitalization::create([
            'triage_id' => $request->triage_id, 'bed_id' => $request->bed_id,
            'admission_date' => now(), 'diagnostico' => $request->diagnostico,
            'status' => 'Activa',
        ]);
        Bed::find($request->bed_id)?->update(['status' => 'Ocupada']);
        Triage::find($request->triage_id)?->update(['status' => 'Hospitalizado']);
        return back()->with('success', 'Paciente hospitalizado');
    }

    public function altaHospitalizacion($id)
    {
        $hosp = Hospitalization::findOrFail($id);
        $hosp->update(['status' => 'Alta', 'discharge_date' => now()]);
        Bed::find($hosp->bed_id)?->update(['status' => 'Disponible']);
        Triage::find($hosp->triage_id)?->update(['status' => 'Alta', 'discharge_date' => now(), 'discharge_type' => 'Alta', 'discharge_doctor_id' => Auth::id()]);
        return back()->with('success', 'Paciente dado de alta');
    }

    // CAMAS
    public function camas()
    {
        $role = session('doctor_profile', 'Médico C');
        if ($role === 'Médico C') return redirect()->route('medico.dashboard')->with('error', 'Sin acceso');
        $camas = Bed::orderBy('floor', 'asc')->orderBy('room_number', 'asc')->get();
        $hospitalizados = Hospitalization::where('status', 'Activa')->get();
        return view('medico.camas', compact('camas', 'hospitalizados', 'role'));
    }

    // SERVICIOS
    public function servicios()
    {
        $role = session('doctor_profile', 'Médico C');
        if ($role === 'Médico C') return redirect()->route('medico.dashboard')->with('error', 'Sin acceso');
        $uid = Auth::id();
        $pacientes = Triage::where('assigned_doctor', $uid)->whereIn('status', ['En Atención', 'Hospitalizado'])->get();
        $servicios = \DB::table('service_requests')->where('doctor_id', $uid)->orderBy('created_at', 'desc')->take(20)->get();
        return view('medico.servicios', compact('pacientes', 'servicios', 'role'));
    }

    public function storeServicio(Request $request)
    {
        \DB::table('service_requests')->insert([
            'triage_id' => $request->triage_id, 'doctor_id' => Auth::id(),
            'tipo' => $request->tipo, 'descripcion' => $request->descripcion,
            'prioridad' => $request->prioridad, 'status' => 'Pendiente',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        return back()->with('success', 'Servicio solicitado');
    }

    public function cancelarServicio($id)
    {
        \DB::table('service_requests')->where('id', $id)->update(['status' => 'Cancelado', 'updated_at' => now()]);
        return back()->with('success', 'Servicio cancelado');
    }

    // FARMACIA STOCK
    public function farmaciaStock()
    {
        $role = session('doctor_profile', 'Médico C');
        if ($role === 'Médico C') return redirect()->route('medico.dashboard')->with('error', 'Sin acceso');
        $medicamentos = Medication::orderBy('name')->get();
        return view('medico.farmacia-stock', compact('medicamentos', 'role'));
    }

    // INSUMOS
    public function insumos()
    {
        $role = session('doctor_profile', 'Médico C');
        if ($role !== 'Médico A') return redirect()->route('medico.dashboard')->with('error', 'Solo Médico A');
        $insumos = Medication::where('category', 'Insumo')->orWhere('category', 'Material')->orderBy('name')->get();
        return view('medico.insumos', compact('insumos', 'role'));
    }

    // EVOLUCIÓN
    public function evolucion()
    {
        $role = session('doctor_profile', 'Médico C');
        $uid = Auth::id();
        $pacientes = Triage::where('assigned_doctor', $uid)->whereIn('status', ['En Atención', 'Hospitalizado'])->get();
        $evolutions = NurseEvolution::orderBy('created_at', 'desc')->take(30)->get();
        return view('medico.evolucion', compact('pacientes', 'evolutions', 'role'));
    }

    public function storeEvolucion(Request $request)
    {
        NurseEvolution::create([
            'triage_id' => $request->triage_id, 'nurse_id' => Auth::id(),
            'notes' => $request->notes, 'priority' => $request->priority ?? 'Normal',
        ]);
        return back()->with('success', 'Nota de evolución guardada');
    }

    public function actualizarEvolucion(Request $request, $id)
    {
        NurseEvolution::findOrFail($id)->update(['notes' => $request->notes, 'priority' => $request->priority]);
        return back()->with('success', 'Nota actualizada');
    }

    public function eliminarEvolucion($id)
    {
        NurseEvolution::findOrFail($id)->delete();
        return back()->with('success', 'Nota eliminada');
    }

    // DEFUNCIONES
    public function defunciones()
    {
        $role = session('doctor_profile', 'Médico C');
        if ($role !== 'Médico A') return redirect()->route('medico.dashboard')->with('error', 'Solo Médico A');
        $defunciones = \DB::table('patient_deaths')->orderBy('death_time', 'desc')->get();
        $totalMes = \DB::table('patient_deaths')->whereMonth('death_time', now()->month)->count();
        $causas = \DB::table('patient_deaths')->select('cause_of_death', \DB::raw('count(*) as total'))->groupBy('cause_of_death')->orderBy('total', 'desc')->limit(5)->get();
        return view('medico.defunciones', compact('defunciones', 'totalMes', 'causas', 'role'));
    }

    public function registrarDefuncion(Request $request, $id)
    {
        if (session('doctor_profile') !== 'Médico A') return back()->with('error', 'Solo Médico A');
        $request->validate(['cause_of_death' => 'required']);
        $p = Triage::findOrFail($id);
        $certNum = 'DEF-' . date('Y') . '-' . str_pad(\DB::table('patient_deaths')->count() + 1, 4, '0', STR_PAD_LEFT);

        \DB::table('patient_deaths')->insert([
            'triage_id' => $id, 'doctor_id' => Auth::id(),
            'bed_id' => $request->bed_id ?? null,
            'death_time' => now(), 'cause_of_death' => $request->cause_of_death,
            'immediate_cause' => $request->immediate_cause,
            'clinical_summary' => $request->clinical_summary,
            'autopsy_required' => $request->has('autopsy_required'),
            'death_certificate_number' => $certNum,
            'notified_family' => $request->notified_family ?? 'No',
            'notes' => $request->notes,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $p->update([
            'status' => 'Defunción', 'discharge_date' => now(),
            'discharge_type' => 'Defunción', 'discharge_doctor_id' => Auth::id(),
        ]);

        $hosp = Hospitalization::where('triage_id', $id)->where('status', 'Activa')->first();
        if ($hosp) {
            $hosp->update(['status' => 'Defunción', 'discharge_date' => now()]);
            Bed::find($hosp->bed_id)?->update(['status' => 'Disponible']);
        }

        return redirect()->route('medico.defunciones')->with('success', 'Defunción registrada. Certificado: ' . $certNum);
    }

    public function verDefuncion($id)
    {
        $role = session('doctor_profile', 'Médico C');
        if ($role !== 'Médico A') return redirect()->route('medico.dashboard')->with('error', 'Solo Médico A');
        $defuncion = \DB::table('patient_deaths')->where('id', $id)->first();
        $paciente = Triage::find($defuncion->triage_id);
        $doctor = User::find($defuncion->doctor_id);
        return view('medico.ver-defuncion', compact('defuncion', 'paciente', 'doctor', 'role'));
    }

    public function certificadoDefuncion($id)
    {
        $defuncion = \DB::table('patient_deaths')->where('id', $id)->first();
        $paciente = Triage::find($defuncion->triage_id);
        $doctor = User::find($defuncion->doctor_id);
        return view('medico.certificado-defuncion', compact('defuncion', 'paciente', 'doctor'));
    }

    public function derivarPaciente(Request $request, $id)
    {
        if (session('doctor_profile') !== 'Médico A') return back()->with('error', 'Solo Médico A');
        $request->validate(['hospital_destino' => 'required', 'motivo' => 'required']);
        \DB::table('derivations')->insert([
            'triage_id' => $id, 'doctor_id' => Auth::id(),
            'hospital_destino' => $request->hospital_destino, 'motivo' => $request->motivo,
            'status' => 'Pendiente', 'created_at' => now(), 'updated_at' => now(),
        ]);
        Triage::find($id)?->update(['status' => 'Derivado', 'discharge_date' => now(), 'discharge_type' => 'Derivación', 'discharge_doctor_id' => Auth::id()]);
        return redirect()->route('medico.pacientes')->with('success', 'Paciente derivado');
    }

    // UCI
    public function uci()
    {
        if (session('doctor_profile') !== 'Médico A') return redirect()->route('medico.dashboard')->with('error', 'Solo Médico A');
        $criticalPatients = Triage::where('triage_level', 'Rojo')->whereIn('status', ['En Atención', 'Hospitalizado'])->get();
        $uciBeds = Bed::where('type', 'UCI')->get();
        return view('medico.uci', compact('criticalPatients', 'uciBeds'));
    }

    public function quirofano()
    {
        if (session('doctor_profile') !== 'Médico A') return redirect()->route('medico.dashboard')->with('error', 'Solo Médico A');
        $scheduled = Hospitalization::where('status', 'Activa')->whereHas('bed', fn($q) => $q->where('type', 'Quirófano'))->get();
        return view('medico.quirofano', compact('scheduled'));
    }

    public function controlados()
    {
        if (session('doctor_profile') !== 'Médico A') return redirect()->route('medico.dashboard')->with('error', 'Solo Médico A');
        $meds = Medication::where('required_level', 'A')->orderBy('name')->get();
        return view('medico.controlados', compact('meds'));
    }

    public function iaMedica()
    {
        if (session('doctor_profile') !== 'Médico A') return redirect()->route('medico.dashboard')->with('error', 'Solo Médico A');
        return view('medico.ia-medica', ['role' => 'Médico A']);
    }

    public function derivaciones()
    {
        if (session('doctor_profile') !== 'Médico A') return redirect()->route('medico.dashboard')->with('error', 'Solo Médico A');
        $pacientes = Triage::whereIn('status', ['En Atención', 'Hospitalizado'])->get();
        $derivaciones = \DB::table('derivations')->orderBy('created_at', 'desc')->take(20)->get();
        return view('medico.derivaciones', compact('pacientes', 'derivaciones'));
    }

    public function auditoria()
    {
        if (session('doctor_profile') !== 'Médico A') return redirect()->route('medico.dashboard')->with('error', 'Solo Médico A');
        return view('medico.auditoria', ['role' => 'Médico A']);
    }

    public function alertas()
    {
        $role = session('doctor_profile');
        $alerts = MedicalAlert::with('triage')->orderBy('is_read')->orderBy('created_at', 'desc')->get();
        return view('medico.alertas', compact('alerts', 'role'));
    }

    public function markAlertRead($id) { MedicalAlert::find($id)?->update(['is_read' => 1]); return back()->with('success', 'Alerta leída'); }
    public function eliminarAlerta($id) { MedicalAlert::findOrFail($id)->delete(); return back()->with('success', 'Alerta eliminada'); }

    public function reportes()
    {
        if (session('doctor_profile') === 'Médico C') return redirect()->route('medico.dashboard')->with('error', 'Sin acceso');
        return view('medico.reportes', ['role' => session('doctor_profile')]);
    }
}
