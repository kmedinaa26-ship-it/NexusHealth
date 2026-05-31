<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use App\Models\Triage;
use App\Models\User;
use App\Models\Specialty;
use App\Models\Derivation;
use App\Models\Medication;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Bed;

class SpecialistController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $uid = $user->id;
        $mySpecialty = $user->specialty;

        $myPatients = Triage::where('assigned_doctor_id', $uid)
            ->whereIn('status', ['En Atencion', 'Hospitalizado'])
            ->orderBy('triage_level', 'asc')
            ->limit(15)
            ->get();

        $criticalPatients = Triage::where('triage_level', 'Rojo')
            ->whereIn('status', ['En Espera', 'En Atencion'])
            ->whereNull('assigned_doctor_id')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $specialties = Specialty::where('is_active', 1)
            ->withCount('doctors')
            ->orderBy('name')
            ->get();

        $todayAppointments = Appointment::where('doctor_id', $uid)
            ->whereDate('scheduled_at', now()->toDateString())
            ->orderBy('scheduled_at')
            ->get();

        $hospitalized = Triage::where('status', 'Hospitalizado')->count();
        $bedsAvailable = Bed::where('status', 'Disponible')->count();

        $pendientesDeriv = 0;
        if (Schema::hasColumn('derivations', 'to_doctor_id')) {
            $pendientesDeriv = Derivation::where('to_doctor_id', $uid)
                ->where('status', 'Pendiente')
                ->count();
        }

        return view('especialidades.dashboard', compact(
            'user', 'mySpecialty', 'myPatients', 'criticalPatients',
            'specialties', 'todayAppointments', 'hospitalized',
            'bedsAvailable', 'pendientesDeriv'
        ));
    }

    public function misPacientes()
    {
        $user = auth()->user();
        $uid = $user->id;
        $mySpecialty = $user->specialty;

        $myPatients = Triage::where('assigned_doctor_id', $uid)
            ->whereIn('status', ['En Atencion', 'Hospitalizado'])
            ->orderBy('triage_level', 'asc')
            ->paginate(20);

        $colegas = collect();
        if ($mySpecialty) {
            $colegas = User::where('specialty_id', $mySpecialty->id)
                ->where('id', '!=', $uid)
                ->get();
        }

        $todosMedicos = User::whereIn('role', ['Medico A', 'Medico B', 'Especialista'])
            ->where('id', '!=', $uid)
            ->orderBy('name')
            ->get();

        return view('especialidades.pacientes', compact('user', 'mySpecialty', 'myPatients', 'colegas', 'todosMedicos'));
    }

    public function hospitalizados()
    {
        $user = auth()->user();
        $uid = $user->id;

        $misHospitalizados = Triage::where('assigned_doctor_id', $uid)
            ->where('status', 'Hospitalizado')
            ->orderBy('triage_level', 'asc')
            ->paginate(20);

        $pendientes = Triage::whereNull('assigned_doctor_id')
            ->whereIn('status', ['En Espera', 'En Atencion'])
            ->orderBy('triage_level', 'asc')
            ->limit(20)
            ->get();

        $todosMedicos = User::whereIn('role', ['Medico A', 'Medico B', 'Especialista'])
            ->where('id', '!=', $uid)
            ->orderBy('name')
            ->get();

        $camas = Bed::orderBy('id')->get();

        return view('especialidades.hospitalizados', compact('user', 'misHospitalizados', 'pendientes', 'todosMedicos', 'camas'));
    }

    public function derivaciones()
    {
        $user = auth()->user();
        $uid = $user->id;

        $derivacionesRecibidas = collect();
        $derivacionesEnviadas = collect();
        $derivacionesHistorial = collect();

        if (Schema::hasColumn('derivations', 'to_doctor_id')) {
            $derivacionesRecibidas = Derivation::where('to_doctor_id', $uid)
                ->where('status', 'Pendiente')
                ->orderBy('created_at', 'desc')
                ->get();

            $derivacionesEnviadas = Derivation::where('from_doctor_id', $uid)
                ->whereIn('status', ['Pendiente', 'Aceptada'])
                ->orderBy('created_at', 'desc')
                ->get();

            $derivacionesHistorial = Derivation::where('from_doctor_id', $uid)
                ->orWhere('to_doctor_id', $uid)
                ->whereIn('status', ['Aceptada', 'Rechazada', 'Reagendada'])
                ->orderBy('updated_at', 'desc')
                ->limit(30)
                ->get();
        }

        $todosMedicos = User::whereIn('role', ['Medico A', 'Medico B', 'Especialista'])
            ->where('id', '!=', $uid)
            ->orderBy('name')
            ->get();

        $specialties = Specialty::where('is_active', 1)->orderBy('name')->get();
        $pacientesDerivar = Triage::where('assigned_doctor_id', $uid)
            ->whereIn('status', ['En Atencion', 'Hospitalizado'])
            ->orderBy('patient_name')
            ->get();

        return view('especialidades.derivaciones', compact(
            'user', 'derivacionesRecibidas', 'derivacionesEnviadas',
            'derivacionesHistorial', 'todosMedicos', 'specialties', 'pacientesDerivar'
        ));
    }

    public function crearDerivacion(Request $request)
    {
        $request->validate([
            'triage_id' => 'required|exists:triages,id',
            'to_doctor_id' => 'nullable|exists:users,id',
            'specialty_id' => 'nullable|exists:specialties,id',
            'reason' => 'required|string|max:500',
            'priority' => 'required|in:Normal,Urgente,Critica',
        ]);

        $data = [
            'triage_id' => $request->triage_id,
            'from_doctor_id' => auth()->id(),
            'to_doctor_id' => $request->to_doctor_id,
            'specialty_id' => $request->specialty_id,
            'reason' => $request->reason,
            'priority' => $request->priority,
            'status' => 'Pendiente',
            'patient_id' => $request->triage_id,
        ];

        if (Schema::hasColumn('derivations', 'doctor_id')) {
            $data['doctor_id'] = auth()->id();
        }
        if (Schema::hasColumn('derivations', 'motivo')) {
            $data['motivo'] = $request->reason;
        }

        Derivation::create($data);

        return back()->with('success', 'Derivacion creada exitosamente.');
    }

    public function aceptarDerivacion($id)
    {
        $derivacion = Derivation::findOrFail($id);
        $derivacion->update([
            'status' => 'Aceptada',
            'responded_at' => now(),
        ]);

        $triage = Triage::find($derivacion->triage_id);
        if ($triage) {
            $triage->update([
                'assigned_doctor_id' => auth()->id(),
                'status' => 'En Atencion',
            ]);
        }

        return back()->with('success', 'Derivacion aceptada. Paciente asignado a usted.');
    }

    public function rechazarDerivacion(Request $request, $id)
    {
        $derivacion = Derivation::findOrFail($id);
        $derivacion->update([
            'status' => 'Rechazada',
            'responded_at' => now(),
            'notes' => $request->input('motivo_rechazo', 'Sin motivo especificado'),
        ]);

        return back()->with('success', 'Derivacion rechazada.');
    }

    public function reagendarDerivacion(Request $request, $id)
    {
        $derivacion = Derivation::findOrFail($id);
        $nuevoMedico = $request->input('nuevo_doctor_id');

        if (!$nuevoMedico) {
            return back()->with('error', 'Seleccione un medico para reagendar.');
        }

        $derivacion->update([
            'status' => 'Reagendada',
            'responded_at' => now(),
            'notes' => 'Reagendada a otro medico',
        ]);

        Derivation::create([
            'triage_id' => $derivacion->triage_id,
            'from_doctor_id' => auth()->id(),
            'to_doctor_id' => $nuevoMedico,
            'specialty_id' => $derivacion->specialty_id,
            'reason' => $derivacion->reason,
            'priority' => $derivacion->priority,
            'status' => 'Pendiente',
            'patient_id' => $derivacion->triage_id,
            'doctor_id' => auth()->id(),
            'motivo' => 'Reagendada desde derivacion anterior',
        ]);

        return back()->with('success', 'Derivacion reagendada a otro medico.');
    }

    public function iaMedica()
    {
        $user = auth()->user();
        $uid = $user->id;

        $alertas = collect();
        $misPacientes = Triage::where('assigned_doctor_id', $uid)
            ->whereIn('status', ['En Atencion', 'Hospitalizado'])
            ->get();

        foreach ($misPacientes as $p) {
            if ($p->triage_level === 'Rojo') {
                $alertas->push([
                    'tipo' => 'critico',
                    'icono' => 'fa-heart-pulse',
                    'color' => '#DC2626',
                    'titulo' => 'Paciente Critico',
                    'detalle' => $p->patient_name . ' - Triage Rojo',
                    'tiempo' => $p->created_at->diffForHumans(),
                ]);
            }
            if ($p->triage_level === 'Naranja') {
                $alertas->push([
                    'tipo' => 'urgente',
                    'icono' => 'fa-triangle-exclamation',
                    'color' => '#EA580C',
                    'titulo' => 'Paciente Urgente',
                    'detalle' => $p->patient_name . ' - Triage Naranja',
                    'tiempo' => $p->created_at->diffForHumans(),
                ]);
            }
        }

        $criticosSinAsignar = Triage::where('triage_level', 'Rojo')
            ->whereIn('status', ['En Espera', 'En Atencion'])
            ->whereNull('assigned_doctor_id')
            ->count();

        if ($criticosSinAsignar > 0) {
            $alertas->push([
                'tipo' => 'sistema',
                'icono' => 'fa-robot',
                'color' => '#7C3AED',
                'titulo' => 'IA: Criticos sin asignar',
                'detalle' => $criticosSinAsignar . ' pacientes criticos esperando medico',
                'tiempo' => 'Ahora',
            ]);
        }

        $hospitalizadosCount = Triage::where('status', 'Hospitalizado')->count();
        $camasCount = Bed::where('status', 'Disponible')->count();
        if ($camasCount < 5 && $hospitalizadosCount > 10) {
            $alertas->push([
                'tipo' => 'sistema',
                'icono' => 'fa-bed-pulse',
                'color' => '#DC2626',
                'titulo' => 'IA: Riesgo de saturacion',
                'detalle' => 'Solo ' . $camasCount . ' camas disponibles para ' . $hospitalizadosCount . ' hospitalizados',
                'tiempo' => 'Ahora',
            ]);
        }

        $derivPendientes = 0;
        if (Schema::hasColumn('derivations', 'to_doctor_id')) {
            $derivPendientes = Derivation::where('to_doctor_id', $uid)->where('status', 'Pendiente')->count();
        }
        if ($derivPendientes > 0) {
            $alertas->push([
                'tipo' => 'derivacion',
                'icono' => 'fa-share',
                'color' => '#2563EB',
                'titulo' => 'Derivaciones pendientes',
                'detalle' => $derivPendientes . ' derivaciones esperando su respuesta',
                'tiempo' => 'Ahora',
            ]);
        }

        return view('especialidades.ia-medica', compact('user', 'alertas', 'misPacientes', 'criticosSinAsignar'));
    }

    public function medicamentos()
    {
        $user = auth()->user();
        $mySpecialty = $user->specialty;
        $medicamentos = Medication::orderBy('name')->paginate(40);
        $restringidos = [];
        if ($mySpecialty) {
            $decoded = json_decode($mySpecialty->restricted_medications, true);
            if (is_array($decoded)) {
                $restringidos = $decoded;
            }
        }
        return view('especialidades.medicamentos', compact('user', 'mySpecialty', 'medicamentos', 'restringidos'));
    }

    public function agenda()
    {
        return view('especialidades.agenda');
    }

    public function reportes()
    {
        $user = auth()->user();
        $uid = $user->id;

        $pacientesAtendidos = Triage::where('assigned_doctor_id', $uid)->count();
        $pacientesActivos = Triage::where('assigned_doctor_id', $uid)
            ->whereIn('status', ['En Atencion', 'Hospitalizado'])
            ->count();
        $criticosAtendidos = Triage::where('assigned_doctor_id', $uid)
            ->where('triage_level', 'Rojo')
            ->count();

        $derivacionesEnviadas = 0;
        $derivacionesAceptadas = 0;
        $derivacionesRechazadas = 0;

        if (Schema::hasColumn('derivations', 'from_doctor_id')) {
            $derivacionesEnviadas = Derivation::where('from_doctor_id', $uid)->count();
            $derivacionesAceptadas = Derivation::where('to_doctor_id', $uid)->where('status', 'Aceptada')->count();
            $derivacionesRechazadas = Derivation::where('to_doctor_id', $uid)->where('status', 'Rechazada')->count();
        }

        $logs = AuditLog::where('user_id', $uid)
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        return view('especialidades.reportes', compact(
            'user', 'pacientesAtendidos', 'pacientesActivos',
            'criticosAtendidos', 'derivacionesEnviadas',
            'derivacionesAceptadas', 'derivacionesRechazadas', 'logs'
        ));
    }

    public function aceptarPaciente(Request $request, $id)
    {
        $triage = Triage::findOrFail($id);
        $triage->update([
            'assigned_doctor_id' => auth()->id(),
            'status' => 'En Atencion',
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'user_role' => auth()->user()->role,
            'action' => 'Paciente Aceptado',
            'module' => 'Especialista',
            'ip_address' => $request->ip(),
            'details' => $triage->patient_name,
        ]);

        return back()->with('success', 'Paciente ' . $triage->patient_name . ' aceptado.');
    }

    public function derivarPaciente(Request $request, $id)
    {
        $request->validate(['doctor_id' => 'required|exists:users,id']);

        $triage = Triage::findOrFail($id);
        $doctor = User::findOrFail($request->doctor_id);
        $triage->update(['assigned_doctor_id' => $doctor->id]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'user_name' => auth()->user()->name,
            'user_role' => auth()->user()->role,
            'action' => 'Paciente Derivado',
            'module' => 'Especialista',
            'ip_address' => $request->ip(),
            'details' => $triage->patient_name . ' a ' . $doctor->name,
        ]);

        return back()->with('success', 'Paciente derivado a ' . $doctor->name . '.');
    }
}
