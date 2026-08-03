<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Triage;
use App\Models\Bed;
use App\Models\NurseEvolution;
use App\Models\Hospitalization;
use App\Models\MedicalAlert;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class NurseController extends Controller
{
    /**
     * Dashboard de Enfermería
     * GET /api/nurse/dashboard
     */
    /**
 * Dashboard de Enfermería
 * GET /api/nurse/dashboard
 */
public function apiDashboard()
{
    try {
        $user = auth()->user();
        $uid = $user->id;
        $role = $user->role;

        // Estadísticas
        $critical = Triage::where('triage_level', 'Rojo')
            ->whereIn('status', ['En Espera', 'En Atención'])
            ->count();

        $hospitalized = Triage::where('status', 'Hospitalizado')->count();
        $bedsAvailable = Bed::where('status', 'Disponible')->count();
        
        // Pacientes activos (En Atención + Hospitalizado)
        $active = Triage::whereIn('status', ['En Atención', 'Hospitalizado'])->count();

        // Alertas no leídas
        $alerts = MedicalAlert::where('is_read', 0)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($a) {
                return [
                    'id' => $a->id,
                    'type' => $a->type ?? 'Alerta',
                    'message' => $a->message,
                    'severity' => $a->severity ?? 'Normal',
                    'created_at' => $a->created_at,
                ];
            });

        // Pacientes críticos (para mostrar en el dashboard)
        $criticalPatients = Triage::where('triage_level', 'Rojo')
            ->whereIn('status', ['En Espera', 'En Atención', 'Hospitalizado'])
            ->limit(5)
            ->get()
            ->map(function($p) {
                return [
                    'id' => $p->id,
                    'patient_name' => $p->patient_name,
                    'age' => $p->age,
                    'triage_level' => $p->triage_level,
                    'status' => $p->status,
                    'symptoms' => $p->symptoms ?? $p->chief_complaint ?? 'Sin síntomas',
                    'vitals_fc' => $p->vitals_fc,
                    'vitals_temp' => $p->vitals_temp,
                    'vitals_spo2' => $p->vitals_spo2,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                ],
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
        \Log::error('Error in apiDashboard: ' . $e->getMessage());
        \Log::error('Line: ' . $e->getLine());
        \Log::error('File: ' . $e->getFile());
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ], 500);
    }
}

    /**
     * Lista de pacientes de enfermería
     * GET /api/nurse/patients
     */
    /**
 * Lista de pacientes de enfermería
 * GET /api/nurse/patients
 */
public function apiPatients(Request $request)
{
    try {
        $user = auth()->user();
        $uid = $user->id;

        $perPage = (int) $request->get('per_page', 10);
        $search = $request->get('search', '');

        $query = Triage::whereIn('status', ['En Atención', 'Hospitalizado']);

        if (!empty($search)) {
            $query->where('patient_name', 'LIKE', "%{$search}%");
        }

        $patients = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $formattedPatients = [];
        foreach ($patients->items() as $p) {
            $formattedPatients[] = [
                'id' => $p->id,
                'patient_name' => $p->patient_name ?? 'N/A',
                'age' => $p->age ?? 0,
                'triage_level' => $p->triage_level,
                'status' => $p->status,
                'diagnostico' => $p->diagnostico,
                'bed_id' => $p->bed_id,
                'created_at' => $p->created_at,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'patients' => $formattedPatients,
                'pagination' => [
                    'total' => $patients->total(),
                    'per_page' => $patients->perPage(),
                    'current_page' => $patients->currentPage(),
                    'last_page' => $patients->lastPage(),
                ]
            ]
        ]);
    } catch (\Exception $e) {
        \Log::error('Error in apiPatients: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}
    /**
     * Registrar Signos Vitales (RÁPIDO)
     * POST /api/nurse/vitals
     */
    public function apiStoreVitals(Request $request)
    {
        try {
            $user = auth()->user();

            $request->validate([
                'triage_id' => 'required|exists:triages,id',
                'vitals_ta' => 'nullable|string',
                'vitals_fc' => 'nullable|numeric',
                'vitals_temp' => 'nullable|numeric',
                'vitals_spo2' => 'nullable|numeric',
            ]);

            $triage = Triage::findOrFail($request->triage_id);
            $triage->update($request->only(['vitals_ta', 'vitals_fc', 'vitals_temp', 'vitals_spo2']));

            // Guardar en MongoDB (si existe)
            try {
                \App\Models\MongoTriageLog::create([
                    'patient_id' => $triage->id,
                    'patient_name' => $triage->patient_name,
                    'triage_level' => $triage->triage_level,
                    'vitals_fc' => $request->vitals_fc,
                    'vitals_temp' => $request->vitals_temp,
                    'vitals_spo2' => $request->vitals_spo2,
                    'timestamp' => now()
                ]);
            } catch (\Exception $e) {
                // Si no hay MongoDB, ignorar
            }

            return response()->json([
                'success' => true,
                'message' => 'Signos vitales registrados correctamente',
                'data' => [
                    'id' => $triage->id,
                    'patient_name' => $triage->patient_name,
                    'vitals_ta' => $triage->vitals_ta,
                    'vitals_fc' => $triage->vitals_fc,
                    'vitals_temp' => $triage->vitals_temp,
                    'vitals_spo2' => $triage->vitals_spo2,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error in apiStoreVitals: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Registrar Nota de Evolución (RÁPIDO)
     * POST /api/nurse/evolution
     */
    public function apiStoreEvolution(Request $request)
    {
        try {
            $user = auth()->user();

            $request->validate([
                'triage_id' => 'required|exists:triages,id',
                'notes' => 'required|string',
                'priority' => 'nullable|string',
            ]);

            $triage = Triage::findOrFail($request->triage_id);

            $evolution = NurseEvolution::create([
                'triage_id' => $request->triage_id,
                'patient_name' => $triage->patient_name,
                'nurse_id' => $user->id,
                'observation' => $request->notes,
                'priority' => $request->priority ?? 'Normal',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nota de evolución registrada correctamente',
                'data' => [
                    'id' => $evolution->id,
                    'patient_name' => $triage->patient_name,
                    'notes' => $request->notes,
                    'created_at' => $evolution->created_at,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error in apiStoreEvolution: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener Mapa de Camas
     * GET /api/nurse/beds
     */
    public function apiBeds()
    {
        try {
            $beds = Bed::orderBy('area')
                ->orderBy('floor')
                ->orderBy('room_number')
                ->orderBy('bed_number')
                ->get();

            $stats = [
                'total' => $beds->count(),
                'disponibles' => $beds->where('status', 'Disponible')->count(),
                'ocupadas' => $beds->where('status', 'Ocupada')->count(),
                'limpieza' => $beds->where('status', 'Limpieza')->count(),
                'mantenimiento' => $beds->where('status', 'Mantenimiento')->count(),
            ];

            $formattedBeds = [];
            foreach ($beds as $bed) {
                $patientName = null;
                if ($bed->status === 'Ocupada') {
                    $hospitalization = Hospitalization::where('bed_id', $bed->id)
                        ->where('status', 'Activa')
                        ->first();
                    if ($hospitalization) {
                        $triage = Triage::find($hospitalization->triage_id);
                        $patientName = $triage ? $triage->patient_name : $bed->patient_name;
                    }
                }

                $formattedBeds[] = [
                    'id' => $bed->id,
                    'area' => $bed->area ?? 'General',
                    'floor' => $bed->floor,
                    'room_number' => $bed->room_number,
                    'bed_number' => $bed->bed_number,
                    'status' => $bed->status,
                    'patient_name' => $patientName,
                    'triage_level' => $bed->triage_level,
                ];
            }

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
     * Obtener Alertas
     * GET /api/nurse/alerts
     */
    public function apiAlerts(Request $request)
    {
        try {
            $perPage = (int) $request->get('per_page', 10);
            $search = $request->get('search', '');

            $query = MedicalAlert::orderBy('created_at', 'desc');

            if (!empty($search)) {
                $query->where('message', 'LIKE', "%{$search}%");
            }

            $alerts = $query->paginate($perPage);

            $formattedAlerts = [];
            foreach ($alerts->items() as $a) {
                $formattedAlerts[] = [
                    'id' => $a->id,
                    'type' => $a->type ?? 'Alerta',
                    'message' => $a->message,
                    'severity' => $a->severity ?? 'Normal',
                    'is_read' => (bool) $a->is_read,
                    'triage_id' => $a->triage_id,
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
                    ]
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
            $alert = MedicalAlert::findOrFail($id);
            $alert->is_read = 1;
            $alert->save();

            return response()->json([
                'success' => true,
                'message' => 'Alerta marcada como leída'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiMarkAlertRead: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Resumen de alertas
     * GET /api/nurse/alerts/summary
     */
 /**
 * Resumen de alertas - VERSIÓN SEGURA
 * GET /api/nurse/alerts/summary
 */
public function apiAlertsSummary()
{
    try {
        $total = MedicalAlert::count();
        $noLeidas = MedicalAlert::where('is_read', 0)->count();

        // Contar críticas por tipo o mensaje
        $criticas = MedicalAlert::where('is_read', 0)
            ->where(function($q) {
                $q->where('type', 'LIKE', '%Crítico%')
                  ->orWhere('type', 'LIKE', '%Critico%')
                  ->orWhere('message', 'LIKE', '%TRIAGE ROJO%')
                  ->orWhere('message', 'LIKE', '%crítico%')
                  ->orWhere('message', 'LIKE', '%critico%');
            })
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'no_leidas' => $noLeidas,
                'criticas' => $criticas,
            ]
        ]);
    } catch (\Exception $e) {
        \Log::error('Error in apiAlertsSummary: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}
}
