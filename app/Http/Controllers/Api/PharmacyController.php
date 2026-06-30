<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medication;
use App\Models\PurchaseOrder;
use App\Models\CrashCart;
use App\Models\PatientMedication;
use App\Models\RestockRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Provider; 
use App\Models\Triage; // ← AGREGAR
use App\Models\User; // ← AGREGAR
class PharmacyController extends Controller
{
    /**
     * Obtener dashboard de farmacia para app móvil
     * GET /api/pharmacy/dashboard
     */
    public function apiDashboard()
    {
        try {
            Log::info('PharmacyController::apiDashboard called');
            
            // CENTRAL - con valores por defecto
            $centralStock = Medication::where('origin', 'Central')->sum('stock') ?? 0;
            $centralValue = Medication::where('origin', 'Central')->selectRaw('COALESCE(SUM(stock * price), 0) as total')->value('total') ?? 0;
            $centralLow = Medication::where('origin', 'Central')->whereRaw('stock <= min_stock')->where('stock', '>', 0)->count() ?? 0;
            $centralOut = Medication::where('origin', 'Central')->where('stock', 0)->count() ?? 0;
            
            // HOSPITALARIA
            $hospStock = Medication::whereIn('origin', ['Hospitalaria', 'Urgencias', 'Quirurgico'])->sum('stock') ?? 0;
            $hospValue = Medication::whereIn('origin', ['Hospitalaria', 'Urgencias', 'Quirurgico'])->selectRaw('COALESCE(SUM(stock * price), 0) as total')->value('total') ?? 0;
            
            // OPERACIONES
            $pending_orders = PurchaseOrder::whereIn('status', ['Borrador', 'Enviada', 'En Transito'])->count() ?? 0;
            $cart_alerts = CrashCart::where('status', '!=', 'Completo')->count() ?? 0;
            $dispensed_today = PatientMedication::whereDate('created_at', today())->count() ?? 0;
            $controlled_today = PatientMedication::whereDate('created_at', today())
                ->whereHas('medication', function($q) { 
                    $q->where('required_level', 'A'); 
                })
                ->count() ?? 0;
            $pending_restock = RestockRequest::whereIn('status', ['Solicitada', 'Aprobada'])->count() ?? 0;
            $expiring_critical = Medication::whereDate('expiry_date', '<=', now()->addDays(7))->count() ?? 0;
            
            // TOP DISPENSADOS
            $topDispensed = PatientMedication::whereDate('created_at', today())
                ->selectRaw('medication_name, COALESCE(SUM(quantity), 0) as total')
                ->groupBy('medication_name')
                ->orderByDesc('total')
                ->take(5)
                ->get();
            
            // ULTIMAS DISPENSACIONES
            $recentDispensed = PatientMedication::orderBy('created_at', 'desc')
                ->take(8)
                ->get();
            
            // PRÓXIMOS A CADUCAR
            $expiringSoon = Medication::whereDate('expiry_date', '<=', now()->addDays(30))
                ->orderBy('expiry_date', 'asc')
                ->take(5)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'central' => [
                        'stock' => (int) $centralStock,
                        'value' => (float) $centralValue,
                        'low' => (int) $centralLow,
                        'out' => (int) $centralOut,
                    ],
                    'hospital' => [
                        'stock' => (int) $hospStock,
                        'value' => (float) $hospValue,
                    ],
                    'stats' => [
                        'pending_orders' => (int) $pending_orders,
                        'cart_alerts' => (int) $cart_alerts,
                        'dispensed_today' => (int) $dispensed_today,
                        'controlled_today' => (int) $controlled_today,
                        'pending_restock' => (int) $pending_restock,
                        'expiring_critical' => (int) $expiring_critical,
                    ],
                    'top_dispensed' => $topDispensed->map(function($item) {
                        return [
                            'medication_name' => $item->medication_name ?? 'N/A',
                            'total' => (int) ($item->total ?? 0),
                        ];
                    }),
                    'recent_dispensed' => $recentDispensed->map(function($item) {
                        return [
                            'id' => $item->id,
                            'patient_name' => $item->patient_name ?? 'N/A',
                            'medication_name' => $item->medication_name ?? 'N/A',
                            'quantity' => (int) ($item->quantity ?? 0),
                            'interaction_alert' => (bool) $item->interaction_alert,
                            'created_at' => $item->created_at,
                        ];
                    }),
                    'expiring_soon' => $expiringSoon->map(function($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name ?? 'N/A',
                            'expiry_date' => $item->expiry_date,
                        ];
                    }),
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

    public function apiInventory()
    {
        try {
            Log::info('PharmacyController::apiInventory called');
            
            $medications = Medication::orderBy('origin')
                ->orderBy('required_level')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $medications->map(function($med) {
                    return [
                        'id' => $med->id,
                        'name' => $med->name,
                        'active_ingredient' => $med->active_ingredient,
                        'required_level' => $med->required_level,
                        'enfermera_can_administer' => (bool) $med->enfermera_can_administer,
                        'origin' => $med->origin,
                        'stock' => (int) $med->stock,
                        'min_stock' => (int) $med->min_stock,
                        'price' => (float) $med->price,
                        'lot_number' => $med->lot_number,
                        'expiry_date' => $med->expiry_date,
                        'location' => $med->location,
                        'provider_name' => $med->provider_name,
                        'created_at' => $med->created_at,
                    ];
                }),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in apiInventory: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Registrar nuevo medicamento
     * POST /api/pharmacy/medication
     */
    public function apiStoreMedication(Request $request)
    {
        try {
            Log::info('PharmacyController::apiStoreMedication called');
            
            $user = auth()->user();
            
            $request->validate([
                'name' => 'required|string|max:255',
                'active_ingredient' => 'required|string|max:255',
                'stock' => 'required|integer|min:0',
                'min_stock' => 'required|integer|min:0',
                'price' => 'required|numeric|min:0',
                'required_level' => 'required|in:A,B,C,Enfermera',
                'origin' => 'required|string',
                'lot_number' => 'required|string',
                'expiry_date' => 'required|date',
                'location' => 'nullable|string',
                'provider_name' => 'nullable|string',
                'enfermera_can_administer' => 'nullable|boolean',
            ]);
            
            // Crear medicamento
            $medication = Medication::create([
                'name' => $request->name,
                'active_ingredient' => $request->active_ingredient,
                'stock' => $request->stock,
                'min_stock' => $request->min_stock,
                'price' => $request->price,
                'required_level' => $request->required_level,
                'origin' => $request->origin,
                'lot_number' => $request->lot_number,
                'expiry_date' => $request->expiry_date,
                'location' => $request->location,
                'provider_name' => $request->provider_name,
                'enfermera_can_administer' => $request->enfermera_can_administer ?? ($request->required_level === 'Enfermera'),
            ]);
            
            // Registrar en AuditLog
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'action' => 'Medicamento Registrado',
                'module' => 'Farmacia - Inventario',
                'details' => $medication->name . ' | Nivel: ' . $medication->required_level,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Medicamento registrado correctamente',
                'data' => ['id' => $medication->id],
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in apiStoreMedication: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    /**
 * Obtener medicamentos controlados (Nivel A)
 * GET /api/pharmacy/controlled
 */
public function apiControlled()
{
    try {
        Log::info('PharmacyController::apiControlled called');
        
        $controlledMeds = Medication::where('required_level', 'A')
            ->orderBy('name')
            ->get();
        
        // Estadísticas
        $total = $controlledMeds->count();
        $lowStock = $controlledMeds->filter(function($m) {
            return $m->stock <= $m->min_stock && $m->stock > 0;
        })->count();
        $expired = $controlledMeds->filter(function($m) {
            return $m->expiry_date && $m->expiry_date < now();
        })->count();
        $expiringSoon = $controlledMeds->filter(function($m) {
            return $m->expiry_date && $m->expiry_date <= now()->addDays(30) && $m->expiry_date >= now();
        })->count();
        
        return response()->json([
            'success' => true,
            'data' => $controlledMeds->map(function($med) {
                return [
                    'id' => $med->id,
                    'name' => $med->name,
                    'active_ingredient' => $med->active_ingredient,
                    'stock' => (int) $med->stock,
                    'min_stock' => (int) $med->min_stock,
                    'lot_number' => $med->lot_number,
                    'expiry_date' => $med->expiry_date,
                    'provider_name' => $med->provider_name,
                ];
            }),
            'stats' => [
                'total' => $total,
                'low_stock' => $lowStock,
                'expired' => $expired,
                'expiring_soon' => $expiringSoon,
            ],
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiControlled: ' . $e->getMessage());
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}
/**
 * Obtener lista de proveedores
 * GET /api/pharmacy/providers
 */
public function apiProviders()
{
    try {
        Log::info('PharmacyController::apiProviders called');
        
        $providers = Provider::orderBy('name')->get();
        
        return response()->json([
            'success' => true,
            'data' => $providers->map(function($provider) {
                // Calcular score promedio
                $totalScore = ($provider->delivery_score + $provider->price_score + $provider->quality_score) / 3;
                
                return [
                    'id' => $provider->id,
                    'name' => $provider->name,
                    'rfc' => $provider->rfc,
                    'phone' => $provider->phone,
                    'supply_type' => $provider->supply_type,
                    'delivery_score' => (float) ($provider->delivery_score ?? 0),
                    'price_score' => (float) ($provider->price_score ?? 0),
                    'quality_score' => (float) ($provider->quality_score ?? 0),
                    'total_score' => round($totalScore, 1),
                    'total_orders' => (int) ($provider->total_orders ?? 0),
                    'avg_delivery_days' => (int) ($provider->avg_delivery_days ?? 0),
                    'late_deliveries' => (int) ($provider->late_deliveries ?? 0),
                    'status' => $provider->status,
                ];
            }),
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiProviders: ' . $e->getMessage());
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}


public function apiDispensation()
{
    try {
        Log::info('PharmacyController::apiDispensation called');
        
        // LIMITAR medicamentos a 20 con stock
        $medications = Medication::where('stock', '>', 0)
            ->orderBy('name')
            ->limit(20) // ← LIMITADO A 20
            ->get();
        
        // LIMITAR pacientes a 20
        $patients = Triage::whereIn('status', ['En Espera', 'En Atención', 'Hospitalizado'])
            ->orderBy('created_at', 'desc')
            ->limit(20) // ← LIMITADO A 20
            ->get();
        
        // LIMITAR doctores a 15
        $doctors = User::whereIn('role', ['Médico A', 'Médico B', 'Médico C', 'Urgenciólogo'])
            ->where('status', 'Activo')
            ->limit(15) // ← LIMITADO A 15
            ->get();
        
        // LIMITAR registros recientes a 5
        $recent = PatientMedication::orderBy('created_at', 'desc')
            ->limit(5) // ← LIMITADO A 5
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'medications' => $medications->map(function($m) {
                    return [
                        'id' => $m->id,
                        'name' => $m->name,
                        'stock' => (int) $m->stock,
                        'min_stock' => (int) $m->min_stock,
                        'required_level' => $m->required_level,
                    ];
                }),
                'patients' => $patients->map(function($p) {
                    return [
                        'id' => $p->id,
                        'patient_name' => $p->patient_name,
                        'triage_level' => $p->triage_level,
                    ];
                }),
                'doctors' => $doctors->map(function($d) {
                    return [
                        'id' => $d->id,
                        'name' => $d->name,
                        'role' => $d->role,
                    ];
                }),
                'recent' => $recent->map(function($r) {
                    $doctorName = null;
                    if ($r->prescribed_by) {
                        $doctor = User::find($r->prescribed_by);
                        $doctorName = $doctor ? $doctor->name : null;
                    }
                    
                    return [
                        'id' => $r->id,
                        'patient_name' => $r->patient_name ?? 'N/A',
                        'medication_name' => $r->medication_name ?? 'N/A',
                        'quantity' => (int) ($r->quantity ?? 0),
                        'doctor_name' => $doctorName,
                        'interaction_alert' => (bool) $r->interaction_alert,
                        'created_at' => $r->created_at,
                    ];
                }),
            ]
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiDispensation: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}


    /**
     * Dispensar medicamento
     * POST /api/pharmacy/dispense
     */
    public function apiDispense(Request $request)
    {
        try {
            Log::info('PharmacyController::apiDispense called');
            
            $user = auth()->user();
            
            $request->validate([
                'medication_id' => 'required|exists:medications,id',
                'patient_id' => 'required|exists:triages,id',
                'doctor_id' => 'required|exists:users,id',
                'quantity' => 'required|integer|min:1',
            ]);
            
            $med = Medication::findOrFail($request->medication_id);
            $doctor = User::findOrFail($request->doctor_id);
            $patient = Triage::findOrFail($request->patient_id);
            
            // Verificar permisos según nivel
            $denied = false;
            $reason = '';
            
            if ($med->required_level == 'A' && !in_array($doctor->role, ['Médico A', 'Urgenciólogo'])) {
                $denied = true;
                $reason = 'Solo Médico A o Urgenciólogo puede recetar Nivel A.';
            } elseif ($med->required_level == 'B' && !in_array($doctor->role, ['Médico A', 'Médico B', 'Urgenciólogo'])) {
                $denied = true;
                $reason = 'Se requiere al menos Médico B para Nivel B.';
            } elseif (!$med->enfermera_can_administer && in_array($doctor->role, ['Enfermera A', 'Enfermera B', 'Enfermera C'])) {
                $denied = true;
                $reason = 'Enfermería solo puede dispensar medicamentos autorizados.';
            }
            
            if ($denied) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'user_role' => $user->role,
                    'action' => 'Dispensación DENEGADA',
                    'module' => 'Farmacia - Recetas',
                    'details' => "Dr. {$doctor->name} ({$doctor->role}) intentó recetar {$med->name}. Motivo: {$reason}",
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => $reason,
                ], 403);
            }
            
            if ($request->quantity > $med->stock) {
                return response()->json([
                    'success' => false,
                    'error' => "Stock insuficiente. Solo hay {$med->stock} unidades.",
                ], 400);
            }
            
            // Verificar interacciones
            $interactionAlert = false;
            $interactionDetails = null;
            
            $patientMeds = PatientMedication::where('triage_id', $patient->id)
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->pluck('medication_name')
                ->toArray();
                
            if (!empty($patientMeds)) {
                $criticalInteractions = [
                    'Morfina' => ['Midazolam', 'Fentanilo', 'Diazepam'],
                    'Midazolam' => ['Morfina', 'Fentanilo'],
                    'Fentanilo' => ['Morfina', 'Midazolam', 'Diazepam'],
                    'Diazepam' => ['Morfina', 'Fentanilo', 'Midazolam'],
                ];
                
                $interactions = $criticalInteractions[$med->name] ?? [];
                $found = [];
                foreach ($patientMeds as $prevMed) {
                    if (in_array($prevMed, $interactions)) {
                        $found[] = $prevMed;
                    }
                }
                if (!empty($found)) {
                    $interactionAlert = true;
                    $interactionDetails = "INTERACCIÓN con: " . implode(', ', $found) . ". Depresión respiratoria potencial.";
                }
            }
            
            // Reducir stock
            $med->decrement('stock', $request->quantity);
            
            // Registrar dispensación
            PatientMedication::create([
                'triage_id' => $patient->id,
                'patient_name' => $patient->patient_name,
                'medication_id' => $med->id,
                'medication_name' => $med->name,
                'quantity' => $request->quantity,
                'dispensed_by' => $user->id,
                'prescribed_by' => $doctor->id,
                'interaction_alert' => $interactionAlert,
                'interaction_details' => $interactionDetails,
            ]);
            
            AuditLog::create([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_role' => $user->role,
                'action' => 'Medicamento Dispensado',
                'module' => 'Farmacia - Recetas',
                'details' => "{$med->name} x{$request->quantity} para {$patient->patient_name}" . ($interactionAlert ? ' | ALERTA' : ''),
            ]);
            
            // Auto-solicitar reabastecimiento si es necesario
            if ($med->fresh()->stock <= $med->min_stock && $med->fresh()->stock > 0) {
                $existing = RestockRequest::where('medication_id', $med->id)
                    ->whereIn('status', ['Solicitada', 'Aprobada'])
                    ->first();
                if (!$existing) {
                    $this->autoRequestRestock($med);
                }
            }
            
            $message = "Receta dispensada: {$med->name} x{$request->quantity}. Stock actual: {$med->fresh()->stock}";
            $response = [
                'success' => true,
                'message' => $message,
            ];
            
            if ($interactionAlert) {
                $response['warning'] = $interactionDetails;
            }
            
            return response()->json($response);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in apiDispense: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-solicitar reabastecimiento (método auxiliar)
     */
    private function autoRequestRestock($med)
    {
        try {
            $priority = $med->stock == 0 ? 'Critica' : ($med->stock <= 3 ? 'Alta' : 'Media');
            $qty = $med->min_stock * 3;
            $reqNum = 'REQ-' . date('Ymd') . '-' . str_pad(RestockRequest::count() + 1, 4, '0', STR_PAD_LEFT);
            
            RestockRequest::create([
                'request_number' => $reqNum,
                'medication_id' => $med->id,
                'quantity_requested' => $qty,
                'priority' => $priority,
                'status' => 'Solicitada',
                'requested_by' => auth()->id(),
                'reason' => "Stock bajo automático: {$med->stock} unidades (Mínimo: {$med->min_stock})",
                'required_by' => now()->addDays($priority == 'Critica' ? 1 : ($priority == 'Alta' ? 3 : 7)),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in autoRequestRestock: ' . $e->getMessage());
        }
    }

    /**
 * Obtener medicamentos con acceso de enfermería
 * GET /api/pharmacy/nurse-meds
 */
public function apiNurseMeds()
{
    try {
        Log::info('PharmacyController::apiNurseMeds called');
        
        $meds = Medication::where('enfermera_can_administer', true)
            ->orderBy('name')
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $meds->map(function($med) {
                return [
                    'id' => $med->id,
                    'name' => $med->name,
                    'active_ingredient' => $med->active_ingredient,
                    'stock' => (int) $med->stock,
                    'min_stock' => (int) $med->min_stock,
                    'origin' => $med->origin,
                    'lot_number' => $med->lot_number,
                    'expiry_date' => $med->expiry_date,
                    'location' => $med->location,
                ];
            }),
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in apiNurseMeds: ' . $e->getMessage());
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}
}