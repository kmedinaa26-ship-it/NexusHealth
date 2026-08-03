<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Triage;
use App\Models\Medication;
use App\Models\Bed;
use App\Models\Hospitalization;
use App\Models\MedicalAlert;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    /**
     * Obtener dashboard del médico
     * GET /api/doctor/dashboard
     */
   public function apiDashboard()
{
    try {
        Log::info('DoctorController::apiDashboard called');
        
        $user = auth()->user();
        $role = $user->role;
        $doctorName = $user->name;
        
        $isA = $role === 'Médico A';
        $isB = $role === 'Médico B';
        $isC = $role === 'Médico C';
        
        $uid = $user->id;
        
        // ==========================================
        // ESTADÍSTICAS - usando doctor_id
        // ==========================================
        try {
            $misPacientes = Triage::where('doctor_id', $uid)
                ->whereIn('status', ['En Atención', 'Hospitalizado'])
                ->count();
        } catch (\Exception $e) {
            Log::warning('Error con doctor_id, intentando con assigned_doctor_id: ' . $e->getMessage());
            $misPacientes = Triage::where('assigned_doctor_id', $uid)
                ->whereIn('status', ['En Atención', 'Hospitalizado'])
                ->count();
        }
        
        $criticos = Triage::where('triage_level', 'Rojo')
            ->whereIn('status', ['En Espera', 'En Atención'])
            ->count();
        
        $hospitalizados = Triage::where('status', 'Hospitalizado')->count();
        $camasDisponibles = Bed::where('status', 'Disponible')->count();
        $stockBajo = Medication::where('stock', '<', 10)->count();
        
        // ==========================================
        // MIS PACIENTES (LIMITADO A 5 PARA EVITAR LAG)
        // ==========================================
        try {
            $misPacientesLista = Triage::where('doctor_id', $uid)
                ->whereIn('status', ['En Atención', 'Hospitalizado'])
                ->orderBy('triage_level', 'asc')
                ->limit(5)
                ->get()
                ->map(function($p) {
                    return [
                        'id' => $p->id,
                        'patient_name' => $p->patient_name,
                        'triage_level' => $p->triage_level,
                        'status' => $p->status,
                        'diagnostico' => $p->diagnostico,
                    ];
                });
        } catch (\Exception $e) {
            Log::warning('Error con doctor_id en pacientes, usando assigned_doctor_id: ' . $e->getMessage());
            $misPacientesLista = Triage::where('assigned_doctor_id', $uid)
                ->whereIn('status', ['En Atención', 'Hospitalizado'])
                ->orderBy('triage_level', 'asc')
                ->limit(5)
                ->get()
                ->map(function($p) {
                    return [
                        'id' => $p->id,
                        'patient_name' => $p->patient_name,
                        'triage_level' => $p->triage_level,
                        'status' => $p->status,
                        'diagnostico' => $p->diagnostico,
                    ];
                });
        }
        
        // ==========================================
        // ALERTAS (LIMITADO A 3)
        // ==========================================
        $alerts = MedicalAlert::where('is_read', 0)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function($a) {
                return [
                    'id' => $a->id,
                    'type' => $a->type,
                    'message' => $a->message,
                    'severity' => $a->severity ?? 'Normal',
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => [
                'doctor_name' => $doctorName,
                'role' => $role,
                'isA' => $isA,
                'isB' => $isB,
                'isC' => $isC,
                'stats' => [
                    'misPacientes' => $misPacientes,
                    'criticos' => $criticos,
                    'hospitalizados' => $hospitalizados,
                    'camasDisponibles' => $camasDisponibles,
                    'stockBajo' => $stockBajo,
                ],
                'patients' => $misPacientesLista,
                'alerts' => $alerts,
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiDashboard: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}
    /**
     * Obtener lista de pacientes para la app móvil (CON PAGINACIÓN)
     * GET /api/doctor/patients
     */
public function apiPatients(Request $request)
{
    try {
        Log::info('DoctorController::apiPatients called');
        
        $user = auth()->user();
        $role = $user->role;
        $uid = $user->id;
        
        $isA = $role === 'Médico A';
        $isB = $role === 'Médico B';
        $isC = $role === 'Médico C';
        
        $perPage = (int) $request->get('per_page', 8); // REDUCIDO A 8
        $page = (int) $request->get('page', 1);
        $search = $request->get('search', '');
        
        // Construir query
        if ($isA) {
            $query = Triage::whereIn('status', ['En Espera', 'En Atención', 'Hospitalizado']);
        } else {
            $query = Triage::where('doctor_id', $uid)
                ->whereIn('status', ['En Atención', 'Hospitalizado']);
        }
        
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('patient_name', 'LIKE', "%{$search}%")
                  ->orWhere('symptoms', 'LIKE', "%{$search}%")
                  ->orWhere('diagnostico', 'LIKE', "%{$search}%");
            });
        }
        
        $patients = $query->orderBy('triage_level', 'asc')
            ->paginate($perPage, ['*'], 'page', $page);
        
        $statsQuery = clone $query;
        $stats = [
            'rojos' => (clone $statsQuery)->where('triage_level', 'Rojo')->count(),
            'naranjas' => (clone $statsQuery)->where('triage_level', 'Naranja')->count(),
            'amarillos' => (clone $statsQuery)->where('triage_level', 'Amarillo')->count(),
        ];
        
        $doctors = [];
        if ($isA) {
            $doctors = User::whereIn('role', ['Médico A', 'Médico B', 'Médico C'])
                ->where('status', 1)
                ->limit(20)
                ->get()
                ->map(function($d) {
                    return [
                        'id' => $d->id,
                        'name' => $d->name,
                        'role' => $d->role,
                    ];
                });
        }
        
        $formattedPatients = [];
        foreach ($patients->items() as $p) {
            $doctorName = null;
            if ($p->doctor_id) {
                $doctor = User::find($p->doctor_id);
                $doctorName = $doctor ? $doctor->name : null;
            }
            
            $formattedPatients[] = [
                'id' => $p->id,
                'patient_name' => $p->patient_name ?? 'N/A',
                'age' => $p->age ?? 0,
                'triage_level' => $p->triage_level ?? 'N/A',
                'status' => $p->status ?? 'En Espera',
                'chief_complaint' => $p->symptoms ?? $p->chief_complaint ?? 'Sin motivo',
                'diagnostico' => $p->diagnostico ?? null,
                'doctor_id' => $p->doctor_id ?? null,
                'doctor_name' => $doctorName,
                'created_at' => $p->created_at,
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'patients' => $formattedPatients,
                'stats' => $stats,
                'doctors' => $doctors,
                'isA' => $isA,
                'isB' => $isB,
                'isC' => $isC,
                'pagination' => [
                    'total' => $patients->total(),
                    'per_page' => $patients->perPage(),
                    'current_page' => $patients->currentPage(),
                    'last_page' => $patients->lastPage(),
                    'from' => $patients->firstItem(),
                    'to' => $patients->lastItem(),
                ]
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiPatients: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}
    /**
     * Obtener un paciente específico
     * GET /api/doctor/patients/{id}
     */
    public function apiGetPatient($id)
    {
        try {
            Log::info('DoctorController::apiGetPatient called for id: ' . $id);
            
            $patient = Triage::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $patient->id,
                    'patient_name' => $patient->patient_name,
                    'age' => $patient->age,
                    'gender' => $patient->gender,
                    'triage_level' => $patient->triage_level,
                    'status' => $patient->status,
                    'chief_complaint' => $patient->chief_complaint,
                    'symptoms' => $patient->symptoms,
                    'allergies' => $patient->allergies,
                    'diagnostico' => $patient->diagnostico,
                    'doctor_notes' => $patient->doctor_notes,
                    'doctor_id' => $patient->doctor_id,
                    'created_at' => $patient->created_at,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in apiGetPatient: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Registrar nuevo paciente
     * POST /api/doctor/patients
     */
    public function apiRegisterPatient(Request $request)
    {
        try {
            Log::info('DoctorController::apiRegisterPatient called');
            
            $user = auth()->user();
            
            $request->validate([
                'patient_name' => 'required|string|max:255',
                'age' => 'required|integer|min:0',
                'gender' => 'nullable|string',
                'chief_complaint' => 'required|string',
                'triage_level' => 'required|string',
                'symptoms' => 'nullable|string',
                'allergies' => 'nullable|string',
                'diagnostico' => 'nullable|string',
                'doctor_notes' => 'nullable|string',
            ]);
            
            $patient = Triage::create([
                'patient_name' => $request->patient_name,
                'age' => $request->age,
                'gender' => $request->gender,
                'chief_complaint' => $request->chief_complaint,
                'triage_level' => $request->triage_level,
                'symptoms' => $request->symptoms,
                'allergies' => $request->allergies,
                'diagnostico' => $request->diagnostico,
                'doctor_notes' => $request->doctor_notes,
                'doctor_id' => $user->id,
                'status' => 'En Espera',
            ]);
            
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'action' => 'Paciente Registrado',
                'module' => 'Médico',
                'details' => "Paciente {$patient->patient_name} registrado por {$user->name}",
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Paciente registrado correctamente',
                'data' => ['id' => $patient->id],
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in apiRegisterPatient: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar paciente
     * PUT /api/doctor/patients/{id}
     */
    public function apiUpdatePatient(Request $request, $id)
    {
        try {
            Log::info('DoctorController::apiUpdatePatient called for id: ' . $id);
            
            $user = auth()->user();
            $patient = Triage::findOrFail($id);
            
            if ($user->role !== 'Médico A' && $patient->doctor_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permiso para editar este paciente'
                ], 403);
            }
            
            $patient->update($request->only([
                'patient_name', 'age', 'gender', 'chief_complaint',
                'triage_level', 'symptoms', 'allergies', 'diagnostico',
                'doctor_notes', 'status'
            ]));
            
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'action' => 'Paciente Actualizado',
                'module' => 'Médico',
                'details' => "Paciente {$patient->patient_name} actualizado por {$user->name}",
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Paciente actualizado correctamente',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in apiUpdatePatient: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Asignar paciente a médico
     * POST /api/doctor/patients/{id}/assign
     */
    public function apiAssignPatient(Request $request, $id)
    {
        try {
            Log::info('DoctorController::apiAssignPatient called for id: ' . $id);
            
            $user = auth()->user();
            if ($user->role !== 'Médico A') {
                return response()->json([
                    'success' => false,
                    'error' => 'Solo Médico A puede asignar pacientes'
                ], 403);
            }
            
            $request->validate([
                'doctor_id' => 'required|exists:users,id',
            ]);
            
            $patient = Triage::findOrFail($id);
            $patient->doctor_id = $request->doctor_id;
            $patient->status = 'En Atención';
            $patient->save();
            
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'action' => 'Paciente Asignado',
                'module' => 'Médico',
                'details' => "Paciente {$patient->patient_name} asignado a doctor ID: {$request->doctor_id}",
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Paciente asignado correctamente',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in apiAssignPatient: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Dar de alta paciente
     * POST /api/doctor/patients/{id}/discharge
     */
    public function apiDischargePatient($id)
    {
        try {
            Log::info('DoctorController::apiDischargePatient called for id: ' . $id);
            
            $user = auth()->user();
            
            $patient = Triage::findOrFail($id);
            
            if ($user->role !== 'Médico A' && $patient->doctor_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permiso para dar de alta a este paciente'
                ], 403);
            }
            
            $patient->status = 'Alta';
            $patient->discharge_date = now();
            $patient->discharge_type = 'Alta Hospitalaria';
            $patient->discharge_doctor_id = $user->id;
            $patient->save();
            
            try {
                $hosp = Hospitalization::where('triage_id', $id)->where('status', 'Activa')->first();
                if ($hosp) {
                    $hosp->status = 'Alta';
                    $hosp->discharge_date = now();
                    $hosp->save();
                    
                    $bed = Bed::find($hosp->bed_id);
                    if ($bed) {
                        $bed->status = 'Disponible';
                        $bed->patient_name = null;
                        $bed->triage_level = null;
                        $bed->save();
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Error liberando cama: ' . $e->getMessage());
            }
            
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'action' => 'Alta Médica',
                'module' => 'Médico',
                'details' => "Paciente {$patient->patient_name} dado de alta",
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Paciente dado de alta correctamente',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in apiDischargePatient: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Derivar paciente
     * POST /api/doctor/patients/{id}/derive
     */
    public function apiDerivePatient(Request $request, $id)
    {
        try {
            Log::info('DoctorController::apiDerivePatient called for id: ' . $id);
            
            $user = auth()->user();
            
            if ($user->role !== 'Médico A') {
                return response()->json([
                    'success' => false,
                    'error' => 'Solo Médico A puede derivar pacientes'
                ], 403);
            }
            
            $request->validate([
                'hospital_destino' => 'required|string|max:255',
                'motivo' => 'required|string',
                'notas' => 'nullable|string',
            ]);
            
            $patient = Triage::findOrFail($id);
            $patient->status = 'Derivado';
            $patient->save();
            
            DB::table('derivations')->insert([
                'triage_id' => $id,
                'patient_name' => $patient->patient_name,
                'hospital_destino' => $request->hospital_destino,
                'motivo' => $request->motivo,
                'notas' => $request->notas,
                'doctor_id' => $user->id,
                'status' => 'Pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'action' => 'Paciente Derivado',
                'module' => 'Médico',
                'details' => "Paciente {$patient->patient_name} derivado a {$request->hospital_destino} por {$user->name}",
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Paciente derivado correctamente',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in apiDerivePatient: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Registrar consulta médica (API)
     * POST /api/doctor/consultation
     */
    public function apiStoreConsultation(Request $request)
    {
        try {
            Log::info('DoctorController::apiStoreConsultation called');
            
            $user = auth()->user();
            
            $request->validate([
                'triage_id' => 'required|exists:triages,id',
                'diagnostico' => 'required|string',
                'tratamiento' => 'required|string',
                'notas' => 'nullable|string',
            ]);
            
            $patient = Triage::findOrFail($request->triage_id);
            
            // Verificar que el paciente esté asignado al médico o sea Médico A
            if ($user->role !== 'Médico A' && $patient->doctor_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permiso para consultar a este paciente'
                ], 403);
            }
            
            $patient->update([
                'diagnostico' => $request->diagnostico,
                'tratamiento' => $request->tratamiento,
                'doctor_notes' => $request->notas,
                'status' => 'En Atención',
            ]);
            
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'action' => 'Consulta Registrada',
                'module' => 'Médico',
                'details' => "Consulta para paciente {$patient->patient_name} por {$user->name}",
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Consulta registrada correctamente',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in apiStoreConsultation: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener diagnósticos del médico (CON PAGINACIÓN)
     * GET /api/doctor/diagnostics
     */
    public function apiDiagnostics(Request $request)
    {
        try {
            Log::info('DoctorController::apiDiagnostics called');
            
            $user = auth()->user();
            $uid = $user->id;
            $role = $user->role;
            
            $isA = $role === 'Médico A';
            
            // Parámetros de paginación
            $perPage = (int) $request->get('per_page', 10);
            $page = (int) $request->get('page', 1);
            $search = $request->get('search', '');
            
            // Construir query - usando doctor_id
            if ($isA) {
                $query = Triage::whereIn('status', ['En Atención', 'Hospitalizado']);
            } else {
                $query = Triage::where('doctor_id', $uid)
                    ->whereIn('status', ['En Atención', 'Hospitalizado']);
            }
            
            // Buscador - SOLO cuando hay texto
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('patient_name', 'LIKE', "%{$search}%")
                      ->orWhere('diagnostico', 'LIKE', "%{$search}%")
                      ->orWhere('cie10', 'LIKE', "%{$search}%");
                });
            }
            
            // Ordenar y paginar
            $patients = $query->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'patients' => $patients->items(),
                    'pagination' => [
                        'total' => $patients->total(),
                        'per_page' => $patients->perPage(),
                        'current_page' => $patients->currentPage(),
                        'last_page' => $patients->lastPage(),
                        'from' => $patients->firstItem(),
                        'to' => $patients->lastItem(),
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in apiDiagnostics: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Guardar diagnóstico (API)
     * POST /api/doctor/diagnostic
     */
    public function apiStoreDiagnostic(Request $request)
    {
        try {
            Log::info('DoctorController::apiStoreDiagnostic called');
            
            $user = auth()->user();
            
            $request->validate([
                'triage_id' => 'required|exists:triages,id',
                'diagnostico' => 'required|string',
                'cie10' => 'nullable|string',
                'doctor_notes' => 'nullable|string',
            ]);
            
            $patient = Triage::findOrFail($request->triage_id);
            
            // Verificar permiso
            if ($user->role !== 'Médico A' && $patient->doctor_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permiso para editar este paciente'
                ], 403);
            }
            
            $patient->update([
                'diagnostico' => $request->diagnostico,
                'cie10' => $request->cie10,
                'doctor_notes' => $request->doctor_notes,
            ]);
            
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'action' => 'Diagnóstico Registrado',
                'module' => 'Médico',
                'details' => "Diagnóstico para paciente {$patient->patient_name} por {$user->name}",
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Diagnóstico guardado correctamente',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in apiStoreDiagnostic: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
 * Obtener recetas del médico (CON PAGINACIÓN Y BÚSQUEDA - OPTIMIZADO)
 * GET /api/doctor/prescriptions
 */
public function apiPrescriptions(Request $request)
{
    try {
        Log::info('DoctorController::apiPrescriptions called');
        
        $user = auth()->user();
        $uid = $user->id;
        $role = $user->role;
        
        $isA = $role === 'Médico A';
        $isC = $role === 'Médico C';
        
        // Parámetros
        $perPage = (int) $request->get('per_page', 8);
        $page = (int) $request->get('page', 1);
        $search = $request->get('search', '');
        
        // ==========================================
        // PACIENTES - SOLO 10 POR DEFECTO
        // ==========================================
        $patientQuery = Triage::select('id', 'patient_name', 'age', 'triage_level', 'status', 'created_at', 'diagnostico');
        
        if (!$isA) {
            $patientQuery->where('doctor_id', $uid);
        }
        
        // ORDENAR POR NOMBRE Y LIMITAR A 15
        $patients = $patientQuery->orderBy('patient_name', 'asc')
            ->limit(15)
            ->get()
            ->map(function($p) {
                return [
                    'id' => $p->id,
                    'patient_name' => $p->patient_name,
                    'age' => $p->age,
                    'triage_level' => $p->triage_level,
                    'status' => $p->status,
                    'created_at' => $p->created_at,
                    'diagnostico' => $p->diagnostico,
                ];
            });
        
        // ==========================================
        // MEDICAMENTOS - LIMITADO A 20
        // ==========================================
        $medQuery = Medication::orderBy('name')->limit(20);
        if ($isC) {
            $medQuery->where('required_level', 'C');
        } elseif (!$isA) {
            $medQuery->where('required_level', '!=', 'A');
        }
        $medications = $medQuery->get()->map(function($m) {
            return [
                'id' => $m->id,
                'name' => $m->name,
                'stock' => (int) $m->stock,
                'required_level' => $m->required_level,
            ];
        });
        
        // ==========================================
        // RECETAS DEL MÉDICO
        // ==========================================
        $query = DB::table('prescriptions')
            ->leftJoin('triages', 'prescriptions.triage_id', '=', 'triages.id')
            ->leftJoin('medications', 'prescriptions.medication_id', '=', 'medications.id')
            ->where('prescriptions.doctor_id', $uid);
        
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('triages.patient_name', 'LIKE', "%{$search}%")
                  ->orWhere('medications.name', 'LIKE', "%{$search}%");
            });
        }
        
        $prescriptions = $query->orderBy('prescriptions.created_at', 'desc')
            ->select(
                'prescriptions.*', 
                'triages.patient_name', 
                'medications.name as medication_name'
            )
            ->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'success' => true,
            'data' => [
                'prescriptions' => $prescriptions->items(),
                'patients' => $patients,
                'medications' => $medications,
                'pagination' => [
                    'total' => $prescriptions->total(),
                    'per_page' => $prescriptions->perPage(),
                    'current_page' => $prescriptions->currentPage(),
                    'last_page' => $prescriptions->lastPage(),
                    'from' => $prescriptions->firstItem(),
                    'to' => $prescriptions->lastItem(),
                ]
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiPrescriptions: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}
/**
 * Buscar pacientes para recetas
 * GET /api/doctor/search-patients
 */
public function apiSearchPatients(Request $request)
{
    try {
        Log::info('DoctorController::apiSearchPatients called');
        
        $user = auth()->user();
        $uid = $user->id;
        $role = $user->role;
        $isA = $role === 'Médico A';
        
        $query = $request->get('query', '');
        $limit = (int) $request->get('limit', 20);
        
        if (empty($query) || strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }
        
        $patientQuery = Triage::select('id', 'patient_name', 'age', 'triage_level', 'status', 'created_at', 'diagnostico');
        
        if (!$isA) {
            $patientQuery->where('doctor_id', $uid);
        }
        
        // Búsqueda por nombre o ID
        $patientQuery->where(function($q) use ($query) {
            $q->where('patient_name', 'LIKE', "%{$query}%")
              ->orWhere('id', 'LIKE', "%{$query}%");
        });
        
        $patients = $patientQuery->orderBy('patient_name', 'asc')
            ->limit($limit)
            ->get()
            ->map(function($p) {
                return [
                    'id' => $p->id,
                    'patient_name' => $p->patient_name,
                    'age' => $p->age,
                    'triage_level' => $p->triage_level,
                    'status' => $p->status,
                    'created_at' => $p->created_at,
                    'diagnostico' => $p->diagnostico,
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $patients
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiSearchPatients: ' . $e->getMessage());
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}
    /**
     * Enviar receta a farmacia (API)
     * POST /api/doctor/prescription
     */
    public function apiStorePrescription(Request $request)
    {
        try {
            Log::info('DoctorController::apiStorePrescription called');
            
            $user = auth()->user();
            
            $request->validate([
                'triage_id' => 'required|exists:triages,id',
                'medication_id' => 'required|exists:medications,id',
                'quantity' => 'nullable|integer|min:1',
                'dosis' => 'nullable|string',
                'frecuencia' => 'nullable|string',
                'duracion' => 'nullable|string',
                'indicaciones' => 'nullable|string',
            ]);
            
            DB::table('prescriptions')->insert([
                'triage_id' => $request->triage_id,
                'patient_id' => $request->triage_id,
                'medication_id' => $request->medication_id,
                'quantity' => $request->quantity ?? 1,
                'dosis' => $request->dosis,
                'frecuencia' => $request->frecuencia,
                'duracion' => $request->duracion,
                'indicaciones' => $request->indicaciones,
                'doctor_id' => $user->id,
                'status' => 'Pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'action' => 'Receta Creada',
                'module' => 'Médico',
                'details' => "Receta creada por {$user->name}",
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Receta enviada a farmacia correctamente',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in apiStorePrescription: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Cancelar receta (API)
     * POST /api/doctor/prescription/{id}/cancel
     */
    public function apiCancelPrescription($id)
    {
        try {
            Log::info('DoctorController::apiCancelPrescription called for id: ' . $id);
            
            $user = auth()->user();
            
            $updated = DB::table('prescriptions')
                ->where('id', $id)
                ->where('doctor_id', $user->id)
                ->where('status', 'Pendiente')
                ->update([
                    'status' => 'Cancelada',
                    'updated_at' => now(),
                ]);
            
            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'error' => 'No se pudo cancelar la receta'
                ], 400);
            }
            
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'action' => 'Receta Cancelada',
                'module' => 'Médico',
                'details' => "Receta #{$id} cancelada por {$user->name}",
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Receta cancelada correctamente',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in apiCancelPrescription: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

/**
 * Obtener signos vitales del médico (CON PAGINACIÓN Y BÚSQUEDA)
 * GET /api/doctor/vitals
 */
public function apiVitals(Request $request)
{
    try {
        Log::info('DoctorController::apiVitals called');
        
        $user = auth()->user();
        $uid = $user->id;
        $role = $user->role;
        
        $isA = $role === 'Médico A';
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);
        $search = $request->get('search', '');
        
        // ==========================================
        // CONSTRUIR QUERY
        // ==========================================
        $query = Triage::query();
        
        // Solo pacientes que tienen al menos un signo vital
        $query->where(function($q) {
            $q->whereNotNull('vitals_fc')
              ->orWhereNotNull('vitals_ta')
              ->orWhereNotNull('vitals_temp')
              ->orWhereNotNull('vitals_spo2');
        });
        
        // Si no es Médico A, solo ver sus pacientes
        if (!$isA) {
            $query->where('doctor_id', $uid);
        }
        
        // ==========================================
        // BUSCADOR - CORREGIDO
        // ==========================================
        if (!empty($search)) {
            $query->where('patient_name', 'LIKE', "%{$search}%");
        }
        
        // ==========================================
        // ORDENAR Y PAGINAR
        // ==========================================
        $vitals = $query->orderBy('updated_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        
        // ==========================================
        // FORMATEAR DATOS
        // ==========================================
        $formattedVitals = [];
        foreach ($vitals->items() as $v) {
            // Extraer systolic/diastolic de vitals_ta
            $systolic = null;
            $diastolic = null;
            if ($v->vitals_ta) {
                $parts = explode('/', $v->vitals_ta);
                if (count($parts) == 2) {
                    $systolic = trim($parts[0]);
                    $diastolic = trim($parts[1]);
                }
            }
            
            $formattedVitals[] = [
                'id' => $v->id,
                'patient_name' => $v->patient_name ?? 'N/A',
                'triage_level' => $v->triage_level ?? 'N/A',
                'systolic' => $systolic,
                'diastolic' => $diastolic,
                'heart_rate' => $v->vitals_fc,
                'temperature' => $v->vitals_temp,
                'spo2' => $v->vitals_spo2,
                'created_at' => $v->updated_at ?? $v->created_at,
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'vitals' => $formattedVitals,
                'pagination' => [
                    'total' => $vitals->total(),
                    'per_page' => $vitals->perPage(),
                    'current_page' => $vitals->currentPage(),
                    'last_page' => $vitals->lastPage(),
                    'from' => $vitals->firstItem(),
                    'to' => $vitals->lastItem(),
                ]
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiVitals: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Obtener tratamientos del médico (CON PAGINACIÓN Y BÚSQUEDA)
 * GET /api/doctor/treatments
 */
/**
 * Obtener tratamientos del médico - CORREGIDO (usando assigned_doctor)
 * GET /api/doctor/treatments
 */
public function apiTreatments(Request $request)
{
    try {
        Log::info('DoctorController::apiTreatments called');
        
        $user = auth()->user();
        $uid = $user->id;
        $role = $user->role;
        
        $isA = $role === 'Médico A';
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);
        $search = $request->get('search', '');
        
        // ==========================================
        // PACIENTES DEL MÉDICO - usando assigned_doctor
        // ==========================================
        $patients = Triage::where('assigned_doctor', $uid)
            ->whereIn('status', ['En Atención', 'Hospitalizado'])
            ->orderBy('patient_name', 'asc')
            ->limit(20)
            ->get()
            ->map(function($p) {
                return [
                    'id' => $p->id,
                    'patient_name' => $p->patient_name,
                    'triage_level' => $p->triage_level,
                    'status' => $p->status,
                ];
            });
        
        // ==========================================
        // TRATAMIENTOS - usando assigned_doctor
        // ==========================================
        $query = Triage::where('assigned_doctor', $uid);
        
        // Solo pacientes con tratamiento
        $query->whereNotNull('tratamiento')
              ->where('tratamiento', '!=', '');
        
        // Buscador
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('patient_name', 'LIKE', "%{$search}%")
                  ->orWhere('tratamiento', 'LIKE', "%{$search}%");
            });
        }
        
        $treatments = $query->orderBy('updated_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        
        // Formatear datos
        $formattedTreatments = [];
        foreach ($treatments->items() as $t) {
            $formattedTreatments[] = [
                'id' => $t->id,
                'patient_name' => $t->patient_name ?? 'N/A',
                'triage_level' => $t->triage_level ?? 'N/A',
                'descripcion' => $t->tratamiento ?? 'Sin descripción',
                'doctor_notes' => $t->doctor_notes,
                'created_at' => $t->updated_at ?? $t->created_at,
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'treatments' => $formattedTreatments,
                'patients' => $patients,
                'pagination' => [
                    'total' => $treatments->total(),
                    'per_page' => $treatments->perPage(),
                    'current_page' => $treatments->currentPage(),
                    'last_page' => $treatments->lastPage(),
                    'from' => $treatments->firstItem(),
                    'to' => $treatments->lastItem(),
                ]
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiTreatments: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Guardar tratamiento (API)
 * POST /api/doctor/treatment
 */
/**
 * Guardar tratamiento (API) - CORREGIDO
 * POST /api/doctor/treatment
 */
/**
 * Guardar tratamiento (API) - CORREGIDO
 * POST /api/doctor/treatment
 */
public function apiStoreTreatment(Request $request)
{
    try {
        Log::info('DoctorController::apiStoreTreatment called');
        
        $user = auth()->user();
        $userRole = $user->role;
        
        // ==========================================
        // VERIFICAR QUE SEA MÉDICO A (con log para depurar)
        // ==========================================
        Log::info('User role: ' . $userRole);
        Log::info('User ID: ' . $user->id);
        
        if ($userRole !== 'Médico A') {
            Log::warning('Usuario no autorizado: ' . $userRole);
            return response()->json([
                'success' => false,
                'error' => 'Solo Médico A puede crear tratamientos. Tu rol es: ' . $userRole
            ], 403);
        }
        
        $request->validate([
            'triage_id' => 'required|exists:triages,id',
            'descripcion' => 'required|string',
            'doctor_notes' => 'nullable|string',
        ]);
        
        $patient = Triage::findOrFail($request->triage_id);
        
        // ==========================================
        // USAR assigned_doctor (NO doctor_id)
        // ==========================================
        if ($patient->assigned_doctor !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'No tienes permiso para este paciente'
            ], 403);
        }
        
        // Actualizar el tratamiento
        $patient->tratamiento = $request->descripcion;
        if ($request->doctor_notes) {
            $patient->doctor_notes = $request->doctor_notes;
        }
        $patient->save();
        
        AuditLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'action' => 'Tratamiento Registrado',
            'module' => 'Médico',
            'details' => "Tratamiento para paciente {$patient->patient_name} por {$user->name}",
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Tratamiento guardado correctamente',
        ]);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'error' => 'Error de validación',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error in apiStoreTreatment: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}
  
/**
 * Obtener datos de hospitalización
 * GET /api/doctor/hospitalization
 */
public function apiHospitalization(Request $request)
{
    try {
        Log::info('DoctorController::apiHospitalization called');
        
        $user = auth()->user();
        $uid = $user->id;
        $role = $user->role;
        
        $isA = $role === 'Médico A';
        $isB = $role === 'Médico B';
        $isC = $role === 'Médico C';
        
        // Pacientes disponibles para hospitalizar
        $patients = Triage::where('assigned_doctor', $uid)
            ->whereIn('status', ['En Atención', 'En Espera'])
            ->orderBy('triage_level', 'asc')
            ->limit(50)
            ->get()
            ->map(function($p) {
                return [
                    'id' => $p->id,
                    'patient_name' => $p->patient_name,
                    'age' => $p->age,
                    'triage_level' => $p->triage_level,
                    'status' => $p->status,
                    'diagnostico' => $p->diagnostico,
                ];
            });
        
        // Camas disponibles
        $beds = Bed::where('status', 'Disponible')
            ->orderBy('floor', 'asc')
            ->orderBy('room_number', 'asc')
            ->limit(30)
            ->get()
            ->map(function($b) {
                return [
                    'id' => $b->id,
                    'floor' => $b->floor,
                    'room_number' => $b->room_number,
                    'bed_number' => $b->bed_number,
                    'type' => $b->type ?? 'General',
                    'status' => $b->status,
                ];
            });
        
        // Hospitalizaciones activas - Usando el modelo
        $hospitalizations = Hospitalization::where('status', 'Activa')
            ->with(['triage', 'bed'])
            ->orderBy('admission_date', 'desc')
            ->limit(30)
            ->get()
            ->map(function($h) {
                return [
                    'id' => $h->id,
                    'patient_name' => $h->patient_name ?? ($h->triage ? $h->triage->patient_name : 'N/A'),
                    'triage_level' => $h->triage ? $h->triage->triage_level : 'N/A',
                    'bed_label' => $h->bed ? "P{$h->bed->floor}-H{$h->bed->room_number}-C{$h->bed->bed_number}" : 'N/A',
                    'diagnosis' => $h->diagnosis ?? $h->diagnostico ?? 'N/A',
                    'status' => $h->status,
                    'admission_date' => $h->admission_date,
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => [
                'patients' => $patients,
                'beds' => $beds,
                'hospitalizations' => $hospitalizations,
                'isA' => $isA,
                'isB' => $isB,
                'isC' => $isC,
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiHospitalization: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}
/**
 * Guardar hospitalización
 * POST /api/doctor/hospitalization
 */
public function apiStoreHospitalization(Request $request)
{
    try {
        Log::info('=== apiStoreHospitalization START ===');
        Log::info('User: ' . auth()->user()->id . ' - ' . auth()->user()->name);
        Log::info('Role: ' . auth()->user()->role);
        Log::info('Request data: ', $request->all());
        
        $user = auth()->user();
        
        // Solo Médico A y B pueden hospitalizar
        if (!in_array($user->role, ['Médico A', 'Médico B'])) {
            Log::warning('Usuario no autorizado: ' . $user->role);
            return response()->json([
                'success' => false,
                'error' => 'No tienes permiso para hospitalizar pacientes'
            ], 403);
        }
        
        Log::info('Validando request...');
        $request->validate([
            'triage_id' => 'required|exists:triages,id',
            'bed_id' => 'required|exists:beds,id',
            'diagnostico' => 'required|string',
        ]);
        Log::info('Validación OK');
        
        Log::info('Buscando paciente...');
        $triage = Triage::find($request->triage_id);
        if (!$triage) {
            Log::warning('Paciente no encontrado: ' . $request->triage_id);
            return response()->json([
                'success' => false,
                'error' => 'Paciente no encontrado'
            ], 404);
        }
        Log::info('Paciente encontrado: ' . $triage->patient_name);
        
        Log::info('Buscando cama...');
        $bed = Bed::find($request->bed_id);
        if (!$bed) {
            Log::warning('Cama no encontrada: ' . $request->bed_id);
            return response()->json([
                'success' => false,
                'error' => 'Cama no encontrada'
            ], 404);
        }
        Log::info('Cama encontrada: ' . $bed->id . ' - Estado: ' . $bed->status);
        
        // Verificar que la cama esté disponible
        if ($bed->status !== 'Disponible') {
            Log::warning('Cama no disponible: ' . $bed->status);
            return response()->json([
                'success' => false,
                'error' => 'La cama no está disponible'
            ], 400);
        }
        
        Log::info('Verificando hospitalización existente...');
        $existingHospitalization = Hospitalization::where('triage_id', $request->triage_id)
            ->where('status', 'Activa')
            ->first();
            
        if ($existingHospitalization) {
            Log::warning('Paciente ya hospitalizado: ' . $request->triage_id);
            return response()->json([
                'success' => false,
                'error' => 'El paciente ya está hospitalizado'
            ], 400);
        }
        
        Log::info('Creando hospitalización...');
        
        // Verificar qué campos tiene el modelo
        Log::info('Campos fillable del modelo: ', (new Hospitalization())->getFillable());
        
        // ==========================================
        // CREAR HOSPITALIZACIÓN
        // ==========================================
        $hospitalizationData = [
            'triage_id' => $request->triage_id,
            'bed_id' => $request->bed_id,
            'patient_id' => $triage->id,
            'patient_name' => $triage->patient_name,
            'doctor_id' => $user->id,
            'admission_date' => now(),
            'diagnosis' => $request->diagnostico,
            'diagnostico' => $request->diagnostico,
            'status' => 'Activa',
        ];
        
        Log::info('Datos a insertar: ', $hospitalizationData);
        
        try {
            $hospitalization = Hospitalization::create($hospitalizationData);
            Log::info('Hospitalización creada ID: ' . $hospitalization->id);
        } catch (\Exception $e) {
            Log::error('Error al crear hospitalización: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'error' => 'Error al crear hospitalización: ' . $e->getMessage()
            ], 500);
        }
        
        Log::info('Actualizando cama...');
        try {
            $bed->status = 'Ocupada';
            $bed->patient_name = $triage->patient_name;
            $bed->triage_level = $triage->triage_level;
            $bed->save();
            Log::info('Cama actualizada');
        } catch (\Exception $e) {
            Log::error('Error al actualizar cama: ' . $e->getMessage());
        }
        
        Log::info('Actualizando triage...');
        try {
            $triage->status = 'Hospitalizado';
            $triage->save();
            Log::info('Triage actualizado');
        } catch (\Exception $e) {
            Log::error('Error al actualizar triage: ' . $e->getMessage());
        }
        
        Log::info('Creando AuditLog...');
        try {
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'action' => 'Hospitalización',
                'module' => 'Médico',
                'details' => "Paciente {$triage->patient_name} hospitalizado en cama {$request->bed_id}",
            ]);
            Log::info('AuditLog creado');
        } catch (\Exception $e) {
            Log::error('Error al crear AuditLog: ' . $e->getMessage());
        }
        
        Log::info('=== apiStoreHospitalization SUCCESS ===');
        return response()->json([
            'success' => true,
            'message' => 'Paciente hospitalizado correctamente',
            'data' => ['hospitalization_id' => $hospitalization->id]
        ]);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Error de validación: ', $e->errors());
        return response()->json([
            'success' => false,
            'error' => 'Error de validación',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('=== apiStoreHospitalization ERROR ===');
        Log::error('Mensaje: ' . $e->getMessage());
        Log::error('Archivo: ' . $e->getFile() . ':' . $e->getLine());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'error' => 'Error interno del servidor: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Obtener camas del médico
 * GET /api/doctor/beds
 */
public function apiBeds(Request $request)
{
    try {
        Log::info('DoctorController::apiBeds called');
        
        // Obtener todas las camas con sus estados
        $beds = Bed::orderBy('floor', 'asc')
            ->orderBy('room_number', 'asc')
            ->get();
        
        // Estadísticas
        $stats = [
            'disponibles' => $beds->where('status', 'Disponible')->count(),
            'ocupadas' => $beds->where('status', 'Ocupada')->count(),
            'limpieza' => $beds->where('status', 'Limpieza')->count(),
            'mantenimiento' => $beds->where('status', 'Mantenimiento')->count(),
        ];
        
        // Formatear camas
        $formattedBeds = $beds->map(function($bed) {
            // Obtener paciente si está ocupada
            $patientName = null;
            if ($bed->status === 'Ocupada') {
                $hospitalization = Hospitalization::where('bed_id', $bed->id)
                    ->where('status', 'Activa')
                    ->first();
                if ($hospitalization) {
                    $triage = Triage::find($hospitalization->triage_id);
                    if ($triage) {
                        $patientName = $triage->patient_name;
                    }
                }
            }
            
            return [
                'id' => $bed->id,
                'floor' => $bed->floor,
                'room_number' => $bed->room_number,
                'bed_number' => $bed->bed_number,
                'type' => $bed->type ?? 'General',
                'status' => $bed->status,
                'patient_name' => $patientName,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'beds' => $formattedBeds,
                'stats' => $stats,
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiBeds: ' . $e->getMessage());
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

/**
 * Obtener detalle de una cama específica
 * GET /api/doctor/beds/{id}
 */
public function apiBedDetail($id)
{
    try {
        Log::info('DoctorController::apiBedDetail called for id: ' . $id);
        
        $bed = Bed::find($id);
        if (!$bed) {
            return response()->json([
                'success' => false,
                'error' => 'Cama no encontrada'
            ], 404);
        }
        
        $data = [
            'id' => $bed->id,
            'floor' => $bed->floor,
            'room_number' => $bed->room_number,
            'bed_number' => $bed->bed_number,
            'type' => $bed->type ?? 'General',
            'status' => $bed->status,
            'patient' => null,
        ];
        
        // Si está ocupada, obtener información del paciente
        if ($bed->status === 'Ocupada') {
            $hospitalization = Hospitalization::where('bed_id', $bed->id)
                ->where('status', 'Activa')
                ->first();
            
            if ($hospitalization) {
                $triage = Triage::find($hospitalization->triage_id);
                if ($triage) {
                    $data['patient'] = [
                        'patient_name' => $triage->patient_name,
                        'age' => $triage->age,
                        'triage_level' => $triage->triage_level,
                        'diagnostico' => $triage->diagnostico,
                        'admission_date' => $hospitalization->admission_date,
                    ];
                }
            }
        }
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiBedDetail: ' . $e->getMessage());
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

/**
 * Obtener stock de farmacia para médico
 * GET /api/doctor/pharmacy-stock
 */
public function apiPharmacyStock(Request $request)
{
    try {
        Log::info('DoctorController::apiPharmacyStock called');
        
        $user = auth()->user();
        $role = $user->role;
        
        $isA = $role === 'Médico A';
        $isC = $role === 'Médico C';
        
        $perPage = (int) $request->get('per_page', 20);
        $page = (int) $request->get('page', 1);
        $search = $request->get('search', '');
        
        // Construir query
        $query = Medication::orderBy('name');
        
        // Restricciones por nivel
        if ($isC) {
            $query->where('required_level', 'C');
        } elseif (!$isA) {
            $query->where('required_level', '!=', 'A');
        }
        // Médico A ve TODOS los medicamentos
        
        // Buscador
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('presentation', 'LIKE', "%{$search}%")
                  ->orWhere('active_ingredient', 'LIKE', "%{$search}%");
            });
        }
        
        $medications = $query->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'success' => true,
            'data' => [
                'medications' => $medications->items(),
                'pagination' => [
                    'total' => $medications->total(),
                    'per_page' => $medications->perPage(),
                    'current_page' => $medications->currentPage(),
                    'last_page' => $medications->lastPage(),
                    'from' => $medications->firstItem(),
                    'to' => $medications->lastItem(),
                ]
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiPharmacyStock: ' . $e->getMessage());
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

/**
 * Obtener catálogo de medicamentos e insumos para médico
 * GET /api/doctor/supplies
 */
public function apiSupplies(Request $request)
{
    try {
        Log::info('DoctorController::apiSupplies called');
        
        $user = auth()->user();
        $role = $user->role;
        
        $isA = $role === 'Médico A';
        $isC = $role === 'Médico C';
        
        $perPage = (int) $request->get('per_page', 20);
        $page = (int) $request->get('page', 1);
        $search = $request->get('search', '');
        
        // Construir query
        $query = Medication::orderBy('name');
        
        // Restricciones por nivel
        if ($isC) {
            $query->where('required_level', 'C');
        } elseif (!$isA) {
            $query->where('required_level', '!=', 'A');
        }
        // Médico A ve TODOS los medicamentos
        
        // Buscador
        if (!empty($search)) {
            $query->where('name', 'LIKE', "%{$search}%");
        }
        
        $supplies = $query->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'success' => true,
            'data' => [
                'supplies' => $supplies->items(),
                'pagination' => [
                    'total' => $supplies->total(),
                    'per_page' => $supplies->perPage(),
                    'current_page' => $supplies->currentPage(),
                    'last_page' => $supplies->lastPage(),
                    'from' => $supplies->firstItem(),
                    'to' => $supplies->lastItem(),
                ]
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiSupplies: ' . $e->getMessage());
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

/**
 * Guardar solicitud de servicio (farmacia)
 * POST /api/doctor/service-request
 */
public function apiStoreServiceRequest(Request $request)
{
    try {
        Log::info('DoctorController::apiStoreServiceRequest called');
        
        $user = auth()->user();
        
        $request->validate([
            'tipo' => 'required|string',
            'descripcion' => 'required|string',
            'prioridad' => 'nullable|string',
            'medication_id' => 'nullable|exists:medications,id',
        ]);
        
        $data = [
            'triage_id' => $request->triage_id ?? null,
            'patient_id' => $request->triage_id ?? null,
            'tipo' => $request->tipo,
            'descripcion' => $request->descripcion,
            'prioridad' => $request->prioridad ?? 'Normal',
            'doctor_id' => $user->id,
            'status' => 'Pendiente',
            'created_at' => now(),
            'updated_at' => now(),
        ];
        
        $id = DB::table('service_requests')->insertGetId($data);
        
        AuditLog::create([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_role' => $user->role,
            'action' => 'Solicitud de Servicio',
            'module' => 'Médico',
            'details' => "Solicitud: {$request->tipo} - {$request->descripcion}",
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Solicitud enviada correctamente',
            'data' => ['id' => $id]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiStoreServiceRequest: ' . $e->getMessage());
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

/**
 * Obtener reportes del médico
 * GET /api/doctor/reports
 */
public function apiReports(Request $request)
{
    try {
        Log::info('DoctorController::apiReports called');
        
        $user = auth()->user();
        $uid = $user->id;
        $role = $user->role;
        
        // Solo Médico A y B pueden ver reportes
        if ($role === 'Médico C') {
            return response()->json([
                'success' => false,
                'error' => 'No tienes acceso a los reportes'
            ], 403);
        }
        
        // Estadísticas
        $pacientesAtendidos = Triage::where('assigned_doctor', $uid)->count();
        $recetasEmitidas = DB::table('prescriptions')->where('doctor_id', $uid)->count();
        $estudiosSolicitados = DB::table('medical_studies')->where('doctor_id', $uid)->count();
        $altasDadas = Triage::where('discharge_doctor_id', $uid)
            ->where('status', 'Alta')
            ->count();
        
        return response()->json([
            'success' => true,
            'data' => [
                'pacientesAtendidos' => $pacientesAtendidos,
                'altasDadas' => $altasDadas,
                'recetasEmitidas' => $recetasEmitidas,
                'estudiosSolicitados' => $estudiosSolicitados,
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiReports: ' . $e->getMessage());
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}


/**
 * Obtener lista de defunciones (CON PAGINACIÓN Y OPTIMIZADO)
 * GET /api/doctor/deaths
 */
public function apiDeaths(Request $request)
{
    try {
        Log::info('DoctorController::apiDeaths called');
        
        $user = auth()->user();
        $uid = $user->id;
        
        // Solo Médico A puede ver defunciones
        if ($user->role !== 'Médico A') {
            return response()->json([
                'success' => false,
                'error' => 'Solo Médico A puede ver defunciones'
            ], 403);
        }
        
        // Parámetros de paginación
        $perPage = (int) $request->get('per_page', 15);
        $page = (int) $request->get('page', 1);
        
        // ==========================================
        // OBTENER DEFUNCIONES CON PAGINACIÓN
        // ==========================================
        $deathsQuery = DB::table('patient_deaths')
            ->select('id', 'triage_id', 'death_certificate_number', 'cause_of_death', 
                     'death_time', 'autopsy_required', 'notified_family', 'clinical_summary')
            ->orderBy('death_time', 'desc');
        
        $deaths = $deathsQuery->paginate($perPage, ['*'], 'page', $page);
        
        // ==========================================
        // OBTENER STATS (EJECUTADOS EN PARALELO)
        // ==========================================
        $total = DB::table('patient_deaths')->count();
        
        $esteMes = DB::table('patient_deaths')
            ->whereYear('death_time', now()->year)
            ->whereMonth('death_time', now()->month)
            ->count();
        
        // Causas principales - LIMITADO A 5
        $causas = DB::table('patient_deaths')
            ->select('cause_of_death', DB::raw('COUNT(*) as total'))
            ->whereNotNull('cause_of_death')
            ->groupBy('cause_of_death')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();
        
        // ==========================================
        // FORMATEAR DATOS (SOLO LOS ITEMS PAGINADOS)
        // ==========================================
        $formattedDeaths = [];
        foreach ($deaths->items() as $d) {
            // Obtener paciente solo si existe
            $patientName = 'N/A';
            if ($d->triage_id) {
                $patient = Triage::select('patient_name')->find($d->triage_id);
                if ($patient) {
                    $patientName = $patient->patient_name;
                }
            }
            
            $formattedDeaths[] = [
                'id' => $d->id,
                'death_certificate_number' => $d->death_certificate_number ?? 'N/A',
                'patient_name' => $patientName,
                'cause_of_death' => $d->cause_of_death ?? 'Sin causa registrada',
                'death_time' => $d->death_time,
                'death_time_formatted' => $d->death_time ? date('d/m/Y H:i', strtotime($d->death_time)) : 'N/A',
                'autopsy_required' => (bool) $d->autopsy_required,
                'notified_family' => $d->notified_family ?? 'No',
                'clinical_summary' => $d->clinical_summary,
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'deaths' => $formattedDeaths,
                'total' => $total,
                'este_mes' => $esteMes,
                'causas' => $causas,
                'pagination' => [
                    'total' => $deaths->total(),
                    'per_page' => $deaths->perPage(),
                    'current_page' => $deaths->currentPage(),
                    'last_page' => $deaths->lastPage(),
                    'from' => $deaths->firstItem(),
                    'to' => $deaths->lastItem(),
                ]
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiDeaths: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Obtener certificado de defunción (PDF)
 * GET /api/doctor/deaths/{id}/certificate
 */
public function apiDeathCertificate($id)
{
    try {
        Log::info('DoctorController::apiDeathCertificate called for id: ' . $id);
        
        $user = auth()->user();
        
        // Solo Médico A puede ver certificados
        if ($user->role !== 'Médico A') {
            return response()->json([
                'success' => false,
                'error' => 'Solo Médico A puede ver certificados de defunción'
            ], 403);
        }
        
        $death = DB::table('patient_deaths')->where('id', $id)->first();
        if (!$death) {
            return response()->json([
                'success' => false,
                'error' => 'Defunción no encontrada'
            ], 404);
        }
        
        // En una implementación real, aquí se generaría el PDF
        // Por ahora, devolvemos un mensaje con los datos
        
        $patient = Triage::find($death->triage_id);
        $doctor = User::find($death->doctor_id);
        
        return response()->json([
            'success' => true,
            'data' => [
                'url' => null, // En producción sería la URL del PDF
                'certificate' => [
                    'number' => $death->death_certificate_number,
                    'patient_name' => $patient ? $patient->patient_name : 'N/A',
                    'patient_age' => $patient ? $patient->age : 'N/A',
                    'cause_of_death' => $death->cause_of_death,
                    'death_time' => $death->death_time,
                    'doctor_name' => $doctor ? $doctor->name : 'N/A',
                    'clinical_summary' => $death->clinical_summary,
                    'autopsy_required' => $death->autopsy_required,
                ]
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiDeathCertificate: ' . $e->getMessage());
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

/**
 * Exportar reportes a PDF
 * GET /api/doctor/export-pdf
 */
public function apiExportPDF(Request $request)
{
    try {
        Log::info('DoctorController::apiExportPDF called');
        
        $user = auth()->user();
        $uid = $user->id;
        
        if ($user->role === 'Médico C') {
            return response()->json([
                'success' => false,
                'error' => 'No tienes acceso a los reportes'
            ], 403);
        }
        
        // Datos
        $pacientesAtendidos = Triage::where('assigned_doctor', $uid)->count();
        $recetasEmitidas = DB::table('prescriptions')->where('doctor_id', $uid)->count();
        $estudiosSolicitados = DB::table('medical_studies')->where('doctor_id', $uid)->count();
        $altasDadas = Triage::where('discharge_doctor_id', $uid)->where('status', 'Alta')->count();
        
        $pacientes = Triage::where('assigned_doctor', $uid)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();
        
        $doctorName = $user->name;
        $fecha = now()->format('d/m/Y H:i');
        
        $html = $this->generateReportHTML(
            $pacientesAtendidos,
            $altasDadas,
            $recetasEmitidas,
            $estudiosSolicitados,
            $pacientes,
            $doctorName,
            $fecha
        );
        
        return response()->json([
            'success' => true,
            'data' => [
                'html' => $html,
                'filename' => 'Reporte_Medico_' . date('Y-m-d') . '.pdf'
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiExportPDF: ' . $e->getMessage());
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

/**
 * Exportar reportes a Excel
 * GET /api/doctor/export-excel
 */
public function apiExportExcel(Request $request)
{
    try {
        Log::info('DoctorController::apiExportExcel called');
        
        $user = auth()->user();
        $uid = $user->id;
        
        if ($user->role === 'Médico C') {
            return response()->json([
                'success' => false,
                'error' => 'No tienes acceso a los reportes'
            ], 403);
        }
        
        // Datos para Excel
        $pacientes = Triage::where('assigned_doctor', $uid)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();
        
        $data = [];
        $data[] = ['Paciente', 'Edad', 'Nivel', 'Estado', 'Diagnóstico', 'Fecha'];
        
        foreach ($pacientes as $p) {
            $data[] = [
                $p->patient_name ?? 'N/A',
                $p->age ?? 'N/A',
                $p->triage_level ?? 'N/A',
                $p->status ?? 'N/A',
                $p->diagnostico ?? 'N/A',
                $p->created_at ? $p->created_at->format('d/m/Y') : 'N/A',
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'rows' => $data,
                'filename' => 'Reporte_Medico_' . date('Y-m-d') . '.xlsx'
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiExportExcel: ' . $e->getMessage());
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

/**
 * Generar HTML para el reporte
 */
private function generateReportHTML($pacientesAtendidos, $altasDadas, $recetasEmitidas, $estudiosSolicitados, $pacientes, $doctorName, $fecha)
{
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Reporte Médico</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
            .header { text-align: center; padding-bottom: 20px; border-bottom: 2px solid #2563EB; margin-bottom: 20px; }
            .header h1 { color: #1E293B; margin: 0; font-size: 24px; }
            .header p { color: #6B7280; margin: 5px 0; }
            .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px; }
            .stat-card { background: #F8FAFC; border-radius: 10px; padding: 15px; text-align: center; border-left: 4px solid #2563EB; }
            .stat-number { font-size: 28px; font-weight: bold; color: #1E293B; }
            .stat-label { font-size: 12px; color: #6B7280; margin-top: 5px; }
            .section-title { font-size: 18px; font-weight: bold; color: #1E293B; margin: 25px 0 15px; padding-bottom: 10px; border-bottom: 1px solid #E5E7EB; }
            table { width: 100%; border-collapse: collapse; font-size: 12px; }
            th { background: #F1F5F9; color: #1E293B; padding: 10px; text-align: left; border: 1px solid #E5E7EB; }
            td { padding: 8px 10px; border: 1px solid #E5E7EB; }
            .badge-rojo { background: #FEE2E2; color: #991B1B; padding: 2px 10px; border-radius: 12px; font-size: 10px; font-weight: bold; }
            .badge-naranja { background: #FFEDD5; color: #9A3412; padding: 2px 10px; border-radius: 12px; font-size: 10px; font-weight: bold; }
            .badge-verde { background: #D1FAE5; color: #065F46; padding: 2px 10px; border-radius: 12px; font-size: 10px; font-weight: bold; }
            .badge-amarillo { background: #FEF3C7; color: #92400E; padding: 2px 10px; border-radius: 12px; font-size: 10px; font-weight: bold; }
            .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #E5E7EB; text-align: center; font-size: 11px; color: #9CA3AF; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>📊 Reporte de Actividad Médica</h1>
            <p>Dr(a). ' . $doctorName . '</p>
            <p>Generado: ' . $fecha . '</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card" style="border-left-color: #3B82F6;">
                <div class="stat-number">' . $pacientesAtendidos . '</div>
                <div class="stat-label">Pacientes Atendidos</div>
            </div>
            <div class="stat-card" style="border-left-color: #10B981;">
                <div class="stat-number">' . $altasDadas . '</div>
                <div class="stat-label">Altas Dadas</div>
            </div>
            <div class="stat-card" style="border-left-color: #F59E0B;">
                <div class="stat-number">' . $recetasEmitidas . '</div>
                <div class="stat-label">Recetas Emitidas</div>
            </div>
            <div class="stat-card" style="border-left-color: #8B5CF6;">
                <div class="stat-number">' . $estudiosSolicitados . '</div>
                <div class="stat-label">Estudios Solicitados</div>
            </div>
        </div>

        <div class="section-title">📋 Últimos Pacientes Atendidos</div>
        <table>
            <thead>
                <tr>
                    <th>Paciente</th>
                    <th>Edad</th>
                    <th>Nivel</th>
                    <th>Estado</th>
                    <th>Diagnóstico</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($pacientes as $p) {
        $badgeClass = 'badge-verde';
        $nivel = $p->triage_level ?? 'N/A';
        if ($nivel == 'Rojo') $badgeClass = 'badge-rojo';
        elseif ($nivel == 'Naranja') $badgeClass = 'badge-naranja';
        elseif ($nivel == 'Amarillo') $badgeClass = 'badge-amarillo';
        
        $html .= '<tr>
                    <td>' . ($p->patient_name ?? 'N/A') . '</td>
                    <td>' . ($p->age ?? 'N/A') . '</td>
                    <td><span class="' . $badgeClass . '">' . $nivel . '</span></td>
                    <td>' . ($p->status ?? 'N/A') . '</td>
                    <td>' . ($p->diagnostico ?? 'N/A') . '</td>
                </tr>';
    }

    $html .= '</tbody>
        </table>
        <div class="footer">
            <p>Documento generado automáticamente - HealthNexus</p>
        </div>
    </body>
    </html>';

    return $html;
}

/**
 * Obtener alertas - VERSIÓN SIMPLIFICADA SIN FILTROS
 * GET /api/doctor/alerts
 */
public function apiAlerts(Request $request)
{
    try {
        \Log::info('=== apiAlerts CALLED ===');
        
        $user = auth()->user();
        $uid = $user->id;
        
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);
        
        \Log::info("User: $uid, PerPage: $perPage, Page: $page");
        
        // Query simple
        $alerts = MedicalAlert::orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        
        \Log::info("Total alerts: " . $alerts->total());
        
        $formattedAlerts = [];
        foreach ($alerts->items() as $a) {
            $formattedAlerts[] = [
                'id' => $a->id,
                'type' => $a->type ?? 'Alerta',
                'message' => $a->message,
                'severity' => $a->severity ?? 'Normal',
                'is_read' => (bool) $a->is_read,
                'doctor_id' => $a->doctor_id,
                'triage_id' => $a->triage_id,
                'patient_name' => null,
                'created_at' => $a->created_at,
                'created_at_formatted' => $a->created_at ? $a->created_at->format('d/m/Y H:i') : 'N/A',
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'alerts' => $formattedAlerts,
                'pagination' => [
                    'total' => $alerts->total(),
                    'per_page' => $alerts->perPage(),
                    'current_page' => $alerts->currentPage(),
                    'last_page' => $alerts->lastPage(),
                    'from' => $alerts->firstItem(),
                    'to' => $alerts->lastItem(),
                ]
            ]
        ]);
        
    } catch (\Exception $e) {
        \Log::error('apiAlerts ERROR: ' . $e->getMessage());
        \Log::error('File: ' . $e->getFile() . ':' . $e->getLine());
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ], 500);
    }
}

/**
 * Obtener resumen de alertas
 * GET /api/doctor/alerts/summary
 */
public function apiAlertsSummary()
{
    try {
        \Log::info('=== apiAlertsSummary CALLED ===');
        
        $total = MedicalAlert::count();
        $noLeidas = MedicalAlert::where('is_read', 0)->count();
        $criticas = MedicalAlert::where('severity', 'Crítica')->where('is_read', 0)->count();
        
        \Log::info("Total: $total, NoLeidas: $noLeidas, Criticas: $criticas");
        
        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'no_leidas' => $noLeidas,
                'criticas' => $criticas,
            ]
        ]);
        
    } catch (\Exception $e) {
        \Log::error('apiAlertsSummary ERROR: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Marcar alerta como leída
 * POST /api/doctor/alerts/{id}/read
 */
public function apiMarkAlertRead($id)
{
    try {
        \Log::info('=== apiMarkAlertRead CALLED === ID: ' . $id);
        
        $alert = MedicalAlert::find($id);
        
        if (!$alert) {
            return response()->json([
                'success' => false,
                'error' => 'Alerta no encontrada'
            ], 404);
        }
        
        $alert->is_read = 1;
        $alert->read_at = now();
        $alert->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Alerta marcada como leída'
        ]);
        
    } catch (\Exception $e) {
        \Log::error('apiMarkAlertRead ERROR: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Eliminar alerta
 * DELETE /api/doctor/alerts/{id}
 */
public function apiDeleteAlert($id)
{
    try {
        \Log::info('=== apiDeleteAlert CALLED === ID: ' . $id);
        
        $alert = MedicalAlert::find($id);
        
        if (!$alert) {
            return response()->json([
                'success' => false,
                'error' => 'Alerta no encontrada'
            ], 404);
        }
        
        $alert->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Alerta eliminada'
        ]);
        
    } catch (\Exception $e) {
        \Log::error('apiDeleteAlert ERROR: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
 
        }
}
/**
 * Obtener pacientes de UCI
 * GET /api/doctor/uci
 */
public function apiUCI(Request $request)
{
    try {
        $user = auth()->user();
        $uid = $user->id;
        $role = $user->role;
        
        // Solo Médico A puede ver UCI
        if ($role !== 'Médico A') {
            return response()->json([
                'success' => false,
                'error' => 'Solo Médico A puede acceder a UCI'
            ], 403);
        }
        
        $perPage = (int) $request->get('per_page', 10);
        $page = (int) $request->get('page', 1);
        $search = $request->get('search', '');
        
        // Pacientes críticos (Triage Rojo)
        $query = Triage::where('triage_level', 'Rojo')
            ->whereIn('status', ['En Atención', 'Hospitalizado', 'En Espera']);
        
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('patient_name', 'LIKE', "%{$search}%")
                  ->orWhere('chief_complaint', 'LIKE', "%{$search}%")
                  ->orWhere('diagnostico', 'LIKE', "%{$search}%");
            });
        }
        
        $patients = $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);
        
        // Estadísticas
        $totalCriticos = Triage::where('triage_level', 'Rojo')
            ->whereIn('status', ['En Atención', 'Hospitalizado', 'En Espera'])
            ->count();
        
        $enAtencion = Triage::where('triage_level', 'Rojo')
            ->where('status', 'En Atención')
            ->count();
        
        $hospitalizados = Triage::where('triage_level', 'Rojo')
            ->where('status', 'Hospitalizado')
            ->count();
        
        $enEspera = Triage::where('triage_level', 'Rojo')
            ->where('status', 'En Espera')
            ->count();
        
        $formattedPatients = [];
        foreach ($patients->items() as $p) {
            $formattedPatients[] = [
                'id' => $p->id,
                'patient_name' => $p->patient_name ?? 'N/A',
                'age' => $p->age ?? 0,
                'gender' => $p->gender ?? 'N/A',
                'triage_level' => $p->triage_level,
                'status' => $p->status,
                'chief_complaint' => $p->chief_complaint ?? $p->symptoms ?? 'Sin motivo',
                'diagnostico' => $p->diagnostico ?? null,
                'doctor_id' => $p->doctor_id,
                'created_at' => $p->created_at,
                'created_at_formatted' => $p->created_at ? $p->created_at->format('d/m/Y H:i') : 'N/A',
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'patients' => $formattedPatients,
                'stats' => [
                    'total' => $totalCriticos,
                    'en_atencion' => $enAtencion,
                    'hospitalizados' => $hospitalizados,
                    'en_espera' => $enEspera,
                ],
                'pagination' => [
                    'total' => $patients->total(),
                    'per_page' => $patients->perPage(),
                    'current_page' => $patients->currentPage(),
                    'last_page' => $patients->lastPage(),
                    'from' => $patients->firstItem(),
                    'to' => $patients->lastItem(),
                ]
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiUCI: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Obtener detalle de paciente crítico
 * GET /api/doctor/uci/{id}
 */
public function apiUCIDetail($id)
{
    try {
        $user = auth()->user();
        $role = $user->role;
        
        // Solo Médico A
        if ($role !== 'Médico A') {
            return response()->json([
                'success' => false,
                'error' => 'Solo Médico A puede ver detalles de UCI'
            ], 403);
        }
        
        $patient = Triage::where('id', $id)
            ->where('triage_level', 'Rojo')
            ->firstOrFail();
        
        // Obtener hospitalización si existe
        $hospitalization = Hospitalization::where('triage_id', $id)
            ->where('status', 'Activa')
            ->first();
        
        // Obtener signos vitales recientes
        $vitals = \DB::table('vital_signs')
            ->where('triage_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Obtener alertas
        $alerts = MedicalAlert::where('triage_id', $id)
            ->where('is_read', 0)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'patient' => [
                    'id' => $patient->id,
                    'patient_name' => $patient->patient_name,
                    'age' => $patient->age,
                    'gender' => $patient->gender,
                    'triage_level' => $patient->triage_level,
                    'status' => $patient->status,
                    'chief_complaint' => $patient->chief_complaint,
                    'symptoms' => $patient->symptoms,
                    'allergies' => $patient->allergies,
                    'diagnostico' => $patient->diagnostico,
                    'doctor_notes' => $patient->doctor_notes,
                    'tratamiento' => $patient->tratamiento,
                    'created_at' => $patient->created_at,
                ],
                'hospitalization' => $hospitalization ? [
                    'id' => $hospitalization->id,
                    'bed_id' => $hospitalization->bed_id,
                    'admission_date' => $hospitalization->admission_date,
                    'diagnosis' => $hospitalization->diagnosis,
                ] : null,
                'vitals' => $vitals,
                'alerts' => $alerts,
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiUCIDetail: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}
}
