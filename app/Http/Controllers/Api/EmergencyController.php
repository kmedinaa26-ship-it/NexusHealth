<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MedicalAlert;
use App\Models\Triage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmergencyController extends Controller
{
    public function activate(Request $request)
    {
        try {
            $user = auth()->user();
            
            Log::info('Usuario autenticado:', [
                'id' => $user->id, 
                'name' => $user->name, 
                'role' => $user->role
            ]);
            
            // ✅ PERMITIR TODOS LOS MEDICOS (con y sin tilde)
            $allowedRoles = [
                'Médico A', 'Medico A',
                'Médico B', 'Medico B',
                'Médico C', 'Medico C',
                'Enfermera A', 'Enfermera A'
            ];
            
            if (!in_array($user->role, $allowedRoles)) {
                return response()->json([
                    'success' => false,
                    'error' => 'No tienes permiso para activar el codigo azul'
                ], 403);
            }

            $request->validate([
                'location' => 'required|string|max:255',
                'notes' => 'nullable|string',
                'triage_id' => 'nullable|integer|exists:triages,id',
                'patient_name' => 'nullable|string|max:255',
            ]);

            $patient = null;
            $patientName = $request->patient_name ?? 'EMERGENCIA GENERAL';
            if ($request->triage_id) {
                $patient = Triage::find($request->triage_id);
                if ($patient) {
                    $patientName = $patient->patient_name;
                }
            }

            $message = "CODIGO AZUL - EMERGENCIA ACTIVADA\n";
            $message .= "Activado por: {$user->name} ({$user->role})\n";
            $message .= "Ubicacion: {$request->location}";
            if ($request->notes) {
                $message .= "\nNota: {$request->notes}";
            }
            if ($patient) {
                $message .= "\nPaciente: {$patient->patient_name} (ID: {$patient->id})";
            }

            $alert = MedicalAlert::create([
                'triage_id' => $request->triage_id ?? null,
                'patient_name' => $patientName,
                'type' => 'Codigo Azul',
                'severity' => 'Critica',
                'category' => 'Emergencia',
                'message' => $message,
                'is_read' => 0,
                'triggered_by' => $user->id,
                'target_user_id' => null,
            ]);

            $recipients = User::whereIn('role', ['Medico A', 'Medico B', 'Enfermera A', 'Enfermera B'])
                ->where('status', 1)
                ->where('id', '!=', $user->id)
                ->count();

            Log::info('CODIGO AZUL ACTIVADO por: ' . $user->name . ' - Ubicacion: ' . $request->location);

            return response()->json([
                'success' => true,
                'message' => 'Codigo Azul activado correctamente',
                'data' => [
                    'alert_id' => $alert->id,
                    'recipients' => $recipients,
                    'location' => $request->location,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error de validacion',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error activating emergency: ' . $e->getMessage());
            Log::error('Line: ' . $e->getLine());
            Log::error('File: ' . $e->getFile());
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function active()
    {
        try {
            $emergencies = MedicalAlert::where('type', 'Codigo Azul')
                ->where('is_read', 0)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            $formatted = [];
            foreach ($emergencies as $e) {
                $formatted[] = [
                    'id' => $e->id,
                    'message' => $e->message,
                    'created_at' => $e->created_at,
                    'time_ago' => $e->created_at ? $e->created_at->diffForHumans() : 'N/A',
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'active_emergencies' => $formatted,
                    'total' => count($formatted),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function resolve($id)
    {
        try {
            $alert = MedicalAlert::findOrFail($id);
            $alert->is_read = 1;
            $alert->save();

            return response()->json([
                'success' => true,
                'message' => 'Emergencia marcada como atendida'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPatients()
    {
        try {
            $patients = Triage::whereIn('status', ['En Atencion', 'Hospitalizado'])
                ->orderBy('patient_name', 'asc')
                ->limit(50)
                ->get();

            $formatted = [];
            foreach ($patients as $p) {
                $formatted[] = [
                    'id' => $p->id,
                    'patient_name' => $p->patient_name ?? 'N/A',
                    'triage_level' => $p->triage_level ?? 'N/A',
                    'status' => $p->status ?? 'N/A',
                    'age' => $p->age ?? 0,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $formatted
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting patients for emergency: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
