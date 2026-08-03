<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Medication;
use App\Models\PatientMedication;
use App\Models\RestockRequest;
use App\Models\Triage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PharmacyController extends Controller
{
    /**
     * Dashboard de Farmacia
     * GET /api/pharmacy/dashboard
     */
    public function apiDashboard()
    {
        try {
            $user = auth()->user();

            // Stock bajo (<= min_stock y > 0)
            $lowStock = Medication::whereRaw('stock <= min_stock')
                ->where('stock', '>', 0)
                ->count();

            // Sin stock
            $outOfStock = Medication::where('stock', 0)->count();

            // Total medicamentos
            $totalMeds = Medication::count();

            // Medicamentos controlados (Nivel A)
            $controlled = Medication::where('required_level', 'A')->count();

            // Solicitudes pendientes
            $pendingRequests = RestockRequest::whereIn('status', ['Solicitada', 'Aprobada'])->count();

            // Dispensaciones hoy
            $dispensedToday = PatientMedication::whereDate('created_at', today())->count();

            // Stock total
            $totalStock = Medication::sum('stock');

            // Alertas críticas (stock = 0 o muy bajo)
            $criticalAlerts = Medication::where('stock', 0)->count() + 
                             Medication::where('stock', '<=', 3)->where('stock', '>', 0)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => [
                        'low_stock' => $lowStock,
                        'out_of_stock' => $outOfStock,
                        'total_meds' => $totalMeds,
                        'controlled' => $controlled,
                        'pending_requests' => $pendingRequests,
                        'dispensed_today' => $dispensedToday,
                        'total_stock' => $totalStock,
                        'critical_alerts' => $criticalAlerts,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiDashboard: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener inventario (con paginación y búsqueda)
     * GET /api/pharmacy/inventory
     */
    public function apiInventory(Request $request)
    {
        try {
            $perPage = (int) $request->get('per_page', 15);
            $search = $request->get('search', '');
            $origin = $request->get('origin', '');
            $level = $request->get('level', '');

            $query = Medication::query();

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('active_ingredient', 'LIKE', "%{$search}%")
                      ->orWhere('lot_number', 'LIKE', "%{$search}%");
                });
            }

            if (!empty($origin)) {
                $query->where('origin', $origin);
            }

            if (!empty($level)) {
                $query->where('required_level', $level);
            }

            $medications = $query->orderBy('name')->paginate($perPage);

            $formattedMeds = [];
            foreach ($medications->items() as $m) {
                $formattedMeds[] = [
                    'id' => $m->id,
                    'name' => $m->name,
                    'active_ingredient' => $m->active_ingredient,
                    'stock' => (int) $m->stock,
                    'min_stock' => (int) $m->min_stock,
                    'price' => (float) $m->price,
                    'required_level' => $m->required_level,
                    'origin' => $m->origin,
                    'lot_number' => $m->lot_number,
                    'expiry_date' => $m->expiry_date,
                    'is_low_stock' => $m->stock <= $m->min_stock && $m->stock > 0,
                    'is_out_of_stock' => $m->stock == 0,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'medications' => $formattedMeds,
                    'pagination' => [
                        'total' => $medications->total(),
                        'per_page' => $medications->perPage(),
                        'current_page' => $medications->currentPage(),
                        'last_page' => $medications->lastPage(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiInventory: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener stock bajo
     * GET /api/pharmacy/low-stock
     */
    public function apiLowStock()
    {
        try {
            $medications = Medication::whereRaw('stock <= min_stock')
                ->where('stock', '>', 0)
                ->orderByRaw('(stock / min_stock) ASC')
                ->limit(20)
                ->get();

            $formattedMeds = [];
            foreach ($medications as $m) {
                $formattedMeds[] = [
                    'id' => $m->id,
                    'name' => $m->name,
                    'stock' => (int) $m->stock,
                    'min_stock' => (int) $m->min_stock,
                    'required_level' => $m->required_level,
                    'origin' => $m->origin,
                    'urgency' => $m->stock == 0 ? 'CRITICO' : ($m->stock <= 3 ? 'ALTO' : 'MEDIO'),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'medications' => $formattedMeds,
                    'total' => count($formattedMeds),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiLowStock: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener solicitudes pendientes
     * GET /api/pharmacy/pending-requests
     */
    public function apiPendingRequests()
    {
        try {
            $requests = RestockRequest::with(['medication', 'requester'])
                ->whereIn('status', ['Solicitada', 'Aprobada'])
                ->orderBy('priority', 'desc')
                ->orderBy('created_at', 'asc')
                ->limit(20)
                ->get();

            $formattedRequests = [];
            foreach ($requests as $r) {
                $formattedRequests[] = [
                    'id' => $r->id,
                    'request_number' => $r->request_number,
                    'medication_name' => $r->medication ? $r->medication->name : 'N/A',
                    'quantity_requested' => (int) $r->quantity_requested,
                    'priority' => $r->priority,
                    'status' => $r->status,
                    'requester_name' => $r->requester ? $r->requester->name : 'N/A',
                    'created_at' => $r->created_at,
                    'created_at_formatted' => $r->created_at ? $r->created_at->format('d/m/Y H:i') : 'N/A',
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'requests' => $formattedRequests,
                    'total' => count($formattedRequests),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiPendingRequests: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Buscar medicamento por nombre
     * GET /api/pharmacy/search
     */
    public function apiSearch(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $limit = (int) $request->get('limit', 10);

            if (empty($query) || strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $medications = Medication::where('name', 'LIKE', "%{$query}%")
                ->orWhere('active_ingredient', 'LIKE', "%{$query}%")
                ->limit($limit)
                ->get();

            $formattedMeds = [];
            foreach ($medications as $m) {
                $formattedMeds[] = [
                    'id' => $m->id,
                    'name' => $m->name,
                    'stock' => (int) $m->stock,
                    'min_stock' => (int) $m->min_stock,
                    'price' => (float) $m->price,
                    'required_level' => $m->required_level,
                    'origin' => $m->origin,
                    'lot_number' => $m->lot_number,
                    'expiry_date' => $m->expiry_date,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $formattedMeds
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiSearch: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener medicamentos controlados
     * GET /api/pharmacy/controlled
     */
    public function apiControlled()
    {
        try {
            $medications = Medication::where('required_level', 'A')
                ->orderBy('name')
                ->limit(50)
                ->get();

            $formattedMeds = [];
            foreach ($medications as $m) {
                $formattedMeds[] = [
                    'id' => $m->id,
                    'name' => $m->name,
                    'stock' => (int) $m->stock,
                    'lot_number' => $m->lot_number,
                    'expiry_date' => $m->expiry_date,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'medications' => $formattedMeds,
                    'total' => count($formattedMeds),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiControlled: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtener resumen de alertas de farmacia
     * GET /api/pharmacy/alerts-summary
     */
    public function apiAlertsSummary()
    {
        try {
            $outOfStock = Medication::where('stock', 0)->count();
            $lowStock = Medication::whereRaw('stock <= min_stock')->where('stock', '>', 0)->count();
            $pendingRequests = RestockRequest::whereIn('status', ['Solicitada', 'Aprobada'])->count();
            $expiringSoon = Medication::whereDate('expiry_date', '<=', now()->addDays(30))
                ->whereDate('expiry_date', '>', now())
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'out_of_stock' => $outOfStock,
                    'low_stock' => $lowStock,
                    'pending_requests' => $pendingRequests,
                    'expiring_soon' => $expiringSoon,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiAlertsSummary: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }


    /**
 * Obtener proveedores
 * GET /api/pharmacy/providers
 */
public function apiProviders()
{
    try {
        $providers = \App\Models\Provider::orderBy('name')->get();

        $formattedProviders = [];
        foreach ($providers as $p) {
            $formattedProviders[] = [
                'id' => $p->id,
                'name' => $p->name,
                'phone' => $p->phone ?? 'N/A',
                'email' => $p->email ?? 'N/A',
                'status' => $p->status ?? 'Activo',
                'total_orders' => $p->total_orders ?? 0,
                'late_deliveries' => $p->late_deliveries ?? 0,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $formattedProviders
        ]);
    } catch (\Exception $e) {
        Log::error('Error in apiProviders: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Obtener medicamentos que enfermería puede administrar
 * GET /api/pharmacy/nurse-meds
 */
public function apiNurseMeds()
{
    try {
        $medications = Medication::where('enfermera_can_administer', true)
            ->orderBy('name')
            ->limit(50)
            ->get();

        $formattedMeds = [];
        foreach ($medications as $m) {
            $formattedMeds[] = [
                'id' => $m->id,
                'name' => $m->name,
                'active_ingredient' => $m->active_ingredient ?? 'N/A',
                'stock' => (int) $m->stock,
                'min_stock' => (int) $m->min_stock,
                'origin' => $m->origin ?? 'N/A',
                'required_level' => $m->required_level,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $formattedMeds
        ]);
    } catch (\Exception $e) {
        Log::error('Error in apiNurseMeds: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}
}
