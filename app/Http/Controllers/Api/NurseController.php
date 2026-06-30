<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Triage;
use App\Models\Bed;
use App\Models\MedicalAlert;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NurseController extends Controller
{
    /**
     * Obtener dashboard de enfermería para app móvil
     * Endpoint: GET /api/nurse/dashboard
     */
    public function apiDashboard()
    {
        try {
            Log::info('NurseController::apiDashboard called');
            
            $user = auth()->user();
            $role = $user->role;
            
            $critical = Triage::where('triage_level', 'Rojo')
                ->whereIn('status', ['En Espera', 'En Atención'])
                ->count();
            
            $active = Triage::whereIn('status', ['En Atención', 'Hospitalizado'])
                ->count();
            
            $hospitalized = Triage::where('status', 'Hospitalizado')
                ->count();
            
            $bedsAvailable = Bed::where('status', 'Disponible')
                ->count();
            
            $criticalPatients = Triage::where('triage_level', 'Rojo')
                ->whereIn('status', ['En Espera', 'En Atención'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($p) {
                    return [
                        'id' => $p->id,
                        'patient_name' => $p->patient_name,
                        'age' => $p->age,
                        'triage_level' => $p->triage_level,
                        'symptoms' => $p->symptoms ?? $p->chief_complaint ?? 'Pendiente',
                        'vitals_ta' => $p->vitals_ta,
                        'vitals_fc' => $p->vitals_fc,
                        'vitals_temp' => $p->vitals_temp,
                        'vitals_spo2' => $p->vitals_spo2,
                        'status' => $p->status,
                        'assigned_area' => $p->assigned_area,
                        'created_at' => $p->created_at,
                    ];
                });
            
            $alerts = MedicalAlert::where('is_read', 0)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function($a) {
                    return [
                        'id' => $a->id,
                        'type' => $a->type,
                        'message' => $a->message,
                        'severity' => $a->severity ?? 'Normal',
                        'is_read' => $a->is_read,
                        'created_at' => $a->created_at,
                    ];
                });
            
            $permissions = [
                'can_register_triage' => in_array($role, ['Enfermera A', 'Enfermera B', 'Enfermera C']),
                'can_register_vitals' => in_array($role, ['Enfermera A', 'Enfermera B', 'Enfermera C']),
                'can_administer_medication' => in_array($role, ['Enfermera A', 'Enfermera B']),
                'can_hospitalize' => in_array($role, ['Enfermera A', 'Enfermera B']),
                'can_manage_beds' => in_array($role, ['Enfermera A', 'Enfermera B']),
                'can_discharge' => in_array($role, ['Enfermera A']),
                'can_view_medications' => in_array($role, ['Enfermera A', 'Enfermera B']),
                'can_request_medication' => in_array($role, ['Enfermera A', 'Enfermera B']),
            ];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'role' => $role,
                    ],
                    'permissions' => $permissions,
                    'stats' => [
                        'critical' => $critical,
                        'active' => $active,
                        'hospitalized' => $hospitalized,
                        'beds_available' => $bedsAvailable,
                    ],
                    'critical_patients' => $criticalPatients,
                    'alerts' => $alerts,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in apiDashboard: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener lista de alertas
     * GET /api/nurse/alerts
     */
    public function apiAlerts(Request $request)
    {
        try {
            Log::info('NurseController::apiAlerts called');
            
            $perPage = $request->get('per_page', 20);
            $page = $request->get('page', 1);
            $unreadOnly = $request->get('unread_only', false);
            
            $query = MedicalAlert::orderBy('created_at', 'desc');
            
            if ($unreadOnly) {
                $query->where('is_read', 0);
            }
            
            $alerts = $query->paginate($perPage, ['*'], 'page', $page);
            
            $unreadCount = MedicalAlert::where('is_read', 0)->count();
            
            $items = $alerts->items();
            $formattedItems = array_map(function($alert) {
                return [
                    'id' => $alert->id,
                    'type' => $alert->type,
                    'message' => $alert->message,
                    'severity' => $alert->severity ?? 'Normal',
                    'is_read' => $alert->is_read,
                    'patient_id' => $alert->triage_id,
                    'patient_name' => $alert->triage ? $alert->triage->patient_name : null,
                    'created_at' => $alert->created_at,
                    'updated_at' => $alert->updated_at,
                ];
            }, $items);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'items' => $formattedItems,
                    'unread_count' => $unreadCount,
                    'total' => $alerts->total(),
                    'per_page' => $alerts->perPage(),
                    'current_page' => $alerts->currentPage(),
                    'last_page' => $alerts->lastPage(),
                    'from' => $alerts->firstItem(),
                    'to' => $alerts->lastItem(),
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in apiAlerts: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Marcar alerta como leída
     * POST /api/nurse/alerts/{id}/read
     */
    public function apiMarkAlertRead($id)
    {
        try {
            Log::info('NurseController::apiMarkAlertRead called for id: ' . $id);
            
            $user = auth()->user();
            
            $alert = MedicalAlert::findOrFail($id);
            $alert->is_read = 1;
            $alert->save();
            
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'action' => 'Alerta Leída',
                'module' => 'Enfermería',
                'details' => "Alerta #{$id} marcada como leída: {$alert->type}",
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Alerta marcada como leída',
                'data' => [
                    'id' => $alert->id,
                    'is_read' => $alert->is_read,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in apiMarkAlertRead: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener lista de pacientes en triage
     * GET /api/nurse/triage
     */
    public function apiTriage(Request $request)
    {
        try {
            Log::info('NurseController::apiTriage called');
            
            $perPage = $request->get('per_page', 30);
            $page = $request->get('page', 1);
            $status = $request->get('status');
            
            $query = Triage::orderBy('created_at', 'desc');
            
            if ($status) {
                $query->where('status', $status);
            }
            
            $triages = $query->paginate($perPage, ['*'], 'page', $page);
            
            $colors = [
                'Rojo' => '#DC2626',
                'Naranja' => '#EA580C',
                'Amarillo' => '#F59E0B',
                'Verde' => '#F97316',
                'Azul' => '#3B82F6'
            ];
            
            $stats = [
                'critical' => Triage::where('triage_level', 'Rojo')->whereIn('status', ['En Espera', 'En Atención'])->count(),
                'waiting' => Triage::where('status', 'En Espera')->count(),
                'in_attention' => Triage::where('status', 'En Atención')->count(),
                'hospitalized' => Triage::where('status', 'Hospitalizado')->count(),
            ];
            
            return response()->json([
                'success' => true,
                'data' => [
                    'patients' => $triages->items(),
                    'stats' => $stats,
                    'pagination' => [
                        'total' => $triages->total(),
                        'per_page' => $triages->perPage(),
                        'current_page' => $triages->currentPage(),
                        'last_page' => $triages->lastPage(),
                    ],
                    'colors' => $colors,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiTriage: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Registrar nuevo paciente en triage
     * POST /api/nurse/triage
     */
    public function apiStoreTriage(Request $request)
    {
        try {
            Log::info('NurseController::apiStoreTriage called');
            
            $user = auth()->user();
            $role = $user->role;
            
            if (!in_array($role, ['Enfermera A', 'Enfermera B', 'Enfermera C'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permiso para registrar pacientes'
                ], 403);
            }
            
            $request->validate([
                'patient_name' => 'required|string|max:255',
                'triage_level' => 'required|in:Rojo,Naranja,Amarillo,Verde,Azul',
                'age' => 'required|integer|min:0|max:150',
                'chief_complaint' => 'nullable|string',
            ]);
            
            $patientName = ucwords(strtolower(trim($request->patient_name)));
            $chiefComplaint = ucfirst(strtolower(trim($request->chief_complaint ?? '')));
            
            $existingPatient = Triage::where('patient_name', $patientName)
                ->where('age', $request->age)
                ->whereIn('status', ['En Espera', 'En Atención'])
                ->whereDate('created_at', today())
                ->first();
            
            if ($existingPatient) {
                return response()->json([
                    'success' => false,
                    'error' => 'Este paciente ya ha sido registrado hoy en Urgencias'
                ], 400);
            }
            
            $triage = Triage::create([
                'patient_name' => $patientName,
                'triage_level' => $request->triage_level,
                'age' => $request->age,
                'chief_complaint' => $chiefComplaint,
                'symptoms' => $request->symptoms ?? $chiefComplaint ?? 'Pendiente',
                'status' => 'En Espera',
                'created_by' => $user->id,
            ]);
            
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $role,
                'action' => 'Registro Triage',
                'module' => 'Enfermería',
                'details' => "Paciente: {$patientName} | Nivel: {$request->triage_level}",
            ]);
            
            try {
                \App\Models\MongoTriageLog::create([
                    'patient_id' => $triage->id,
                    'patient_name' => $patientName,
                    'triage_level' => $request->triage_level,
                    'age' => $request->age,
                    'specialty' => 'Urgencias',
                    'vitals_fc' => $request->vitals_fc ?? 80,
                    'vitals_temp' => $request->vitals_temp ?? 36.5,
                    'vitals_spo2' => $request->vitals_spo2 ?? 98,
                    'timestamp' => now()
                ]);
            } catch (\Exception $e) {
                Log::warning('Error saving to MongoDB: ' . $e->getMessage());
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Paciente registrado correctamente',
                'data' => ['id' => $triage->id]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in apiStoreTriage: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar signos vitales de paciente (desde Triage)
     * PUT /api/nurse/patients/{id}/vitals
     */
    public function apiUpdateVitals(Request $request, $id)
    {
        try {
            Log::info('NurseController::apiUpdateVitals called for id: ' . $id);
            
            $user = auth()->user();
            $role = $user->role;
            
            if (!in_array($role, ['Enfermera A', 'Enfermera B', 'Enfermera C'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permiso para registrar signos vitales'
                ], 403);
            }
            
            $request->validate([
                'vitals_ta' => 'required|string',
                'vitals_fc' => 'required|string',
                'vitals_temp' => 'required|string',
                'vitals_spo2' => 'required|string',
                'assigned_area' => 'nullable|string',
            ]);
            
            $triage = Triage::find($id);
            
            if (!$triage) {
                return response()->json([
                    'success' => false,
                    'error' => 'Paciente no encontrado'
                ], 404);
            }
            
            $triage->update([
                'vitals_ta' => $request->vitals_ta,
                'vitals_fc' => $request->vitals_fc,
                'vitals_temp' => $request->vitals_temp,
                'vitals_spo2' => $request->vitals_spo2,
                'assigned_area' => $request->assigned_area ?? 'Urgencias',
                'status' => 'En Atención',
            ]);
            
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $role,
                'action' => 'Signos Vitales',
                'module' => 'Enfermería',
                'details' => "Paciente: {$triage->patient_name} | TA: {$request->vitals_ta} | FC: {$request->vitals_fc}",
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Signos vitales registrados correctamente',
                'data' => [
                    'id' => $triage->id,
                    'status' => $triage->status
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in apiUpdateVitals: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Derivar paciente a otro hospital
     * PUT /api/nurse/patients/{id}/derive
     */
    public function apiDerivePatient(Request $request, $id)
    {
        try {
            Log::info('NurseController::apiDerivePatient called for id: ' . $id);
            
            $user = auth()->user();
            $role = $user->role;
            
            if (!in_array($role, ['Enfermera A', 'Enfermera B'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permisos para derivar pacientes. Solo Enfermeras A y B pueden hacerlo.'
                ], 403);
            }
            
            $request->validate([
                'derivation_hospital' => 'required|string|max:255',
            ]);
            
            $triage = Triage::find($id);
            
            if (!$triage) {
                return response()->json([
                    'success' => false,
                    'error' => 'Paciente no encontrado'
                ], 404);
            }
            
            if ($triage->is_derived) {
                return response()->json([
                    'success' => false,
                    'error' => 'Este paciente ya ha sido derivado anteriormente'
                ], 400);
            }
            
            $triage->is_derived = true;
            $triage->derivation_hospital = $request->derivation_hospital;
            $triage->status = 'Derivado';
            $triage->save();
            
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $role,
                'action' => 'Derivación',
                'module' => 'Enfermería',
                'details' => "Paciente: {$triage->patient_name} | Hospital: {$request->derivation_hospital}",
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Paciente derivado correctamente',
                'data' => [
                    'id' => $triage->id,
                    'is_derived' => $triage->is_derived,
                    'derivation_hospital' => $triage->derivation_hospital,
                    'status' => $triage->status
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in apiDerivePatient: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener lista de pacientes y signos vitales recientes
     * GET /api/nurse/patients
     */
  /**
 * Obtener lista de pacientes y signos vitales recientes (LIMITADO)
 * GET /api/nurse/patients
 */
public function apiGetPatients(Request $request)
{
    try {
        Log::info('NurseController::apiGetPatients called');
        
        // SOLO 20 pacientes (no todos)
        $patients = Triage::whereIn('status', ['En Atención', 'Hospitalizado'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        // SOLO 5 registros recientes (no 10)
        $recentVitals = Triage::whereIn('status', ['En Atención', 'Hospitalizado'])
            ->whereNotNull('vitals_fc')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'patients' => $patients->map(function($p) {
                    return [
                        'id' => $p->id,
                        'patient_name' => $p->patient_name,
                        'age' => $p->age,
                        'triage_level' => $p->triage_level,
                        'status' => $p->status,
                        'assigned_area' => $p->assigned_area,
                    ];
                }),
                'recent_vitals' => $recentVitals->map(function($v) {
                    return [
                        'id' => $v->id,
                        'patient_name' => $v->patient_name,
                        'ta' => $v->vitals_ta,
                        'fc' => $v->vitals_fc,
                        'temp' => $v->vitals_temp,
                        'spo2' => $v->vitals_spo2,
                        'is_critical' => $this->isCritical($v->vitals_fc, $v->vitals_temp, $v->vitals_spo2),
                        'created_at' => $v->updated_at,
                    ];
                }),
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiGetPatients: ' . $e->getMessage());
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

    /**
     * Determinar si los signos vitales son críticos
     */
    private function isCritical($fc, $temp, $spo2)
    {
        if (!$fc || !$temp || !$spo2) return false;
        
        $fc = intval($fc);
        $temp = floatval($temp);
        $spo2 = intval($spo2);
        
        return ($fc > 120 || $fc < 50 || $temp > 39 || $temp < 35 || $spo2 < 90);
    }

    /**
     * Registrar signos vitales (igual que el web)
     * POST /api/nurse/vitals
     */
    public function apiStoreVitals(Request $request)
    {
        try {
            Log::info('NurseController::apiStoreVitals called');
            
            $user = auth()->user();
            $role = $user->role;
            
            if (!in_array($role, ['Enfermera A', 'Enfermera B', 'Enfermera C'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permiso para registrar signos vitales'
                ], 403);
            }
            
            $request->validate([
                'triage_id' => 'required|exists:triages,id',
                'ta' => 'required|string',
                'fc' => 'required|string',
                'temp' => 'required|string',
                'spo2' => 'required|string',
                'fr' => 'nullable|string',
                'glucose' => 'nullable|string',
                'pain_scale' => 'nullable|integer|min:0|max:10',
                'notes' => 'nullable|string',
            ]);
            
            $triage = Triage::find($request->triage_id);
            if (!$triage) {
                return response()->json([
                    'success' => false,
                    'error' => 'Paciente no encontrado'
                ], 404);
            }
            
            $triage->vitals_ta = $request->ta;
            $triage->vitals_fc = $request->fc;
            $triage->vitals_temp = $request->temp;
            $triage->vitals_spo2 = $request->spo2;
            $triage->status = 'En Atención';
            $triage->save();
            
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $role,
                'action' => 'Signos Vitales',
                'module' => 'Enfermería',
                'details' => 'Paciente: ' . $triage->patient_name,
            ]);
            
            $isCritical = $this->isCritical($request->fc, $request->temp, $request->spo2);
            
            if ($isCritical) {
                MedicalAlert::create([
                    'triage_id' => $request->triage_id,
                    'type' => 'Signos Vitales Críticos',
                    'message' => "Paciente {$triage->patient_name} presenta signos vitales críticos. FC: {$request->fc}, Temp: {$request->temp}°C, SpO2: {$request->spo2}%",
                    'severity' => 'Crítica',
                    'is_read' => 0,
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Signos vitales registrados correctamente',
                'data' => [
                    'id' => $triage->id,
                    'is_critical' => $isCritical,
                    'patient_name' => $triage->patient_name,
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in apiStoreVitals: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}