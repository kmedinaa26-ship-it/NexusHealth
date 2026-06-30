<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController; 
// use App\Http\Controllers\Api\DoctorController;
 use App\Http\Controllers\Api\NurseController;
use App\Http\Controllers\Api\PharmacyController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    
    // Rutas de autenticación
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/verify-token', [AuthController::class, 'verifyToken']);
    
    // ==========================================
    // RUTAS PARA MÉDICOS 
    // ==========================================
    // Route::middleware(['role:Médico A,Médico B,Médico C,Especialista,Urgenciólogo'])->prefix('doctor')->group(function () {
    //     Route::get('/patients', [DoctorController::class, 'getPatients']);
    //     Route::get('/appointments', [DoctorController::class, 'getAppointments']);
    //     Route::post('/prescriptions', [DoctorController::class, 'createPrescription']);
    // });
    
    // ==========================================
    // RUTAS PARA ENFERMERAS 
    // ==========================================
    // Route::middleware(['role:Enfermera A,Enfermera B,Enfermera C'])->prefix('nurse')->group(function () {
    //     Route::post('/vital-signs', [NurseController::class, 'storeVitalSigns']);
    //     Route::get('/triage-list', [NurseController::class, 'getTriageList']);
    // });
    
    // ==========================================
    // RUTAS PARA ADMIN (SuperAdmin y Administrador Hospitalario)
    // ==========================================
    Route::middleware(['role:SuperAdmin,Administrador Hospitalario'])->prefix('admin')->group(function () {
        
        // ---- USUARIOS ----
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::post('/users', [AdminController::class, 'storeUser']);
        Route::put('/users/{id}/approve', [AdminController::class, 'approveUser']);
        Route::put('/users/{id}/reject', [AdminController::class, 'rejectUser']);
        Route::put('/users/{id}/role', [AdminController::class, 'updateRole']);
        Route::put('/users/{id}/status', [AdminController::class, 'toggleStatus']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        
        // ---- RISK SCORE ----
        Route::get('/risk-score', [AdminController::class, 'getRiskScore']); 
        
        // ---- ROLES Y PERMISOS ----
        Route::get('/roles-permissions', [AdminController::class, 'getRolesPermissions']);
        Route::post('/toggle-permission', [AdminController::class, 'togglePermission']); 
        
        // ---- PACIENTES ----
        Route::get('/patients', [AdminController::class, 'getPatients']); 
        Route::put('/patients/{id}/status', [AdminController::class, 'updatePatientStatus']);
        
        // ---- URGENCIAS ----
        Route::prefix('emergency')->group(function () {
            Route::get('/dashboard', [AdminController::class, 'apiEmergencyDashboard']);
            Route::post('/patient', [AdminController::class, 'apiStoreTriage']);
            Route::put('/patient/{id}/vitals', [AdminController::class, 'apiUpdateVitals']);
            Route::put('/patient/{id}/derive', [AdminController::class, 'apiDerivePatient']);
            Route::put('/patient/{id}/discharge', [AdminController::class, 'apiDischargePatient']);
        });
        
        // ---- CAMAS ----
        Route::get('/beds', [AdminController::class, 'apiGetBeds']);
        Route::post('/beds', [AdminController::class, 'apiStoreBed']);
        Route::put('/beds/{id}/status', [AdminController::class, 'apiUpdateBedStatus']);
        Route::delete('/beds/{id}', [AdminController::class, 'apiDeleteBed']);
        
        // ---- AMBULANCIAS ----
        Route::get('/ambulances', [AdminController::class, 'apiGetAmbulances']);
        Route::post('/ambulances', [AdminController::class, 'apiStoreAmbulance']);
        Route::put('/ambulances/{id}/status', [AdminController::class, 'apiUpdateAmbulanceStatus']);
        Route::delete('/ambulances/{id}', [AdminController::class, 'apiDeleteAmbulance']);
        
        // ---- HOSPITAL LIVE ----
        Route::get('/hospital-live', [AdminController::class, 'apiHospitalLive']);
        
        // ---- AUDITORIA ----
        Route::get('/auditoria/dashboard', [AdminController::class, 'apiAuditoriaDashboard']);
        
        // ---- BIG DATA ----
        Route::get('/bigdata/dashboard', [AdminController::class, 'apiBigDataDashboard']);
        Route::post('/bigdata/run-etl', [AdminController::class, 'apiBigDataRunETL']);
        
        // ---- ACTIVIDAD SOSPECHOSA ----
        Route::get('/suspicious-activity', [AdminController::class, 'apiGetSuspiciousActivity']);
        
        // ---- MONITOR LIVE ----
        Route::get('/monitor-live', [AdminController::class, 'apiGetMonitorLive']);
        
        // ---- MAPA DE CALOR ----
        Route::get('/heatmap', [AdminController::class, 'apiGetHeatmap']);
        
        // ---- INGESTA DE DATOS ----
        Route::post('/ingesta/upload', [AdminController::class, 'apiUploadCSV']);
        Route::get('/ingesta/preview', [AdminController::class, 'apiGetCSVPreview']);
        
        // ---- LIMPIEZA DE DATOS ----
        Route::post('/clean-data', [AdminController::class, 'apiCleanData']);
        Route::get('/clean-result', [AdminController::class, 'apiGetCleanResult']);
        
        // ---- FINANZAS ----
        Route::get('/finanzas/dashboard', [AdminController::class, 'apiFinanzasDashboard']);
        Route::post('/finanzas/verify-pin', [AdminController::class, 'apiFinanzasVerifyPin']);
        
    }); // <--- CIERRE DEL GRUPO ADMIN
    
    // ==========================================
    // RUTAS PARA FARMACIA (Roles específicos de farmacia)
    // ==========================================
    Route::middleware(['role:SuperAdmin,Administrador Hospitalario,Farmacéutico,Admin Farmacia'])->prefix('pharmacy')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'apiPharmacyDashboard']);
        Route::get('/inventory', [AdminController::class, 'apiPharmacyInventory']);
        Route::post('/prescribe', [AdminController::class, 'apiPharmacyPrescribe']);
    });
     Route::middleware(['role:Enfermera A,Enfermera B,Enfermera C'])->prefix('nurse')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [NurseController::class, 'apiDashboard']);
        Route::get('/alerts', [NurseController::class, 'apiAlerts']);
        Route::post('/alerts/{id}/read', [NurseController::class, 'apiMarkAlertRead']);
         Route::get('/triage', [NurseController::class, 'apiTriage']);
        Route::post('/triage', [NurseController::class, 'apiStoreTriage']);
        Route::put('/patients/{id}/vitals', [NurseController::class, 'apiUpdateVitals']);
        Route::put('/patients/{id}/derive', [NurseController::class, 'apiDerivePatient']);
          Route::get('/patients', [NurseController::class, 'apiGetPatients']);
        Route::post('/vitals', [NurseController::class, 'apiStoreVitals']);
        
        
        // Más endpoints se agregarán después...
        // Route::get('/triage', [NurseController::class, 'apiTriage']);
        // Route::post('/triage', [NurseController::class, 'apiStoreTriage']);
        // Route::post('/vitals', [NurseController::class, 'apiStoreVitals']);
        // Route::get('/patients', [NurseController::class, 'apiPatients']);
        // Route::get('/beds', [NurseController::class, 'apiBeds']);
        // Route::get('/medications', [NurseController::class, 'apiMedications']);
        
    });
      Route::middleware(['role:Farmacéutico,Admin Farmacia,SuperAdmin,Administrador Hospitalario'])->prefix('pharmacy')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [PharmacyController::class, 'apiDashboard']);
         Route::get('/inventory', [PharmacyController::class, 'apiInventory']);
        Route::post('/medication', [PharmacyController::class, 'apiStoreMedication']);
            Route::get('/controlled', [PharmacyController::class, 'apiControlled']);
        Route::get('/providers', [PharmacyController::class, 'apiProviders']);
Route::get('/dispensation', [PharmacyController::class, 'apiDispensation']);
        Route::post('/dispense', [PharmacyController::class, 'apiDispense']);
                Route::get('/nurse-meds', [PharmacyController::class, 'apiNurseMeds']);

    });

}); // <--- CIERRE DEL GRUPO PRINCIPAL auth:sanctum