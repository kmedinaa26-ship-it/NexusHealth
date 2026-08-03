<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController; 
use App\Http\Controllers\Api\DoctorController;
use App\Http\Controllers\Api\NurseController;
use App\Http\Controllers\Api\PharmacyController;
use App\Http\Controllers\Api\EmergencyController; 

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    
    // ==========================================
    // RUTAS DE AUTENTICACIÓN
    // ==========================================
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/verify-token', [AuthController::class, 'verifyToken']);
    
    // ==========================================
    // RUTAS PARA ADMIN
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
         Route::get('/pharmacy/dashboard', [AdminController::class, 'apiPharmacyDashboard']);
    Route::post('/pharmacy/prescribe', [AdminController::class, 'apiPharmacyPrescribe']);
    
    });
    
    // ==========================================
    // RUTAS PARA FARMACIA
    // ==========================================
    Route::middleware(['auth:sanctum', 'role:Farmacéutico,Admin Farmacia,SuperAdmin,Administrador Hospitalario'])->prefix('pharmacy')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [PharmacyController::class, 'apiDashboard']);
        
        // Inventario
        Route::get('/inventory', [PharmacyController::class, 'apiInventory']);
        
        // Stock bajo
        Route::get('/low-stock', [PharmacyController::class, 'apiLowStock']);
        
        // Solicitudes pendientes
        Route::get('/pending-requests', [PharmacyController::class, 'apiPendingRequests']);
        
        // Buscar medicamento
        Route::get('/search', [PharmacyController::class, 'apiSearch']);
        
        // Medicamentos controlados
        Route::get('/controlled', [PharmacyController::class, 'apiControlled']);
        
        // Alertas resumen
        Route::get('/alerts-summary', [PharmacyController::class, 'apiAlertsSummary']);
        
        // Proveedores
        Route::get('/providers', [PharmacyController::class, 'apiProviders']);
        
        // Medicamentos para enfermería
        Route::get('/nurse-meds', [PharmacyController::class, 'apiNurseMeds']);
    });
    
    // ==========================================
    // RUTAS PARA ENFERMERÍA
    // ==========================================
    Route::middleware(['auth:sanctum', 'role:Enfermera A,Enfermera B,Enfermera C'])->prefix('nurse')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [NurseController::class, 'apiDashboard']);
        
        // Pacientes
        Route::get('/patients', [NurseController::class, 'apiPatients']);
        
        // Signos Vitales
        Route::post('/vitals', [NurseController::class, 'apiStoreVitals']);
        
        // Evolución
        Route::post('/evolution', [NurseController::class, 'apiStoreEvolution']);
        
        // Camas
        Route::get('/beds', [NurseController::class, 'apiBeds']);
        
        // Alertas
        Route::get('/alerts', [NurseController::class, 'apiAlerts']);
        Route::get('/alerts/summary', [NurseController::class, 'apiAlertsSummary']);
        Route::post('/alerts/{id}/read', [NurseController::class, 'apiMarkAlertRead']);
    });
    
    // ==========================================
    // RUTAS PARA MÉDICOS
    // ==========================================
    Route::middleware(['role:Médico A,Médico B,Médico C'])->prefix('doctor')->group(function () {
        
        // Dashboard
        Route::get('/dashboard', [DoctorController::class, 'apiDashboard']);
        
        // Pacientes
        Route::get('/patients', [DoctorController::class, 'apiPatients']);
        Route::get('/patients/{id}', [DoctorController::class, 'apiGetPatient']);
        Route::post('/patients', [DoctorController::class, 'apiRegisterPatient']);
        Route::put('/patients/{id}', [DoctorController::class, 'apiUpdatePatient']);
        Route::post('/patients/{id}/assign', [DoctorController::class, 'apiAssignPatient']);
        Route::post('/patients/{id}/discharge', [DoctorController::class, 'apiDischargePatient']);
        Route::post('/patients/{id}/derive', [DoctorController::class, 'apiDerivePatient']);
        
        // Búsqueda de pacientes
        Route::get('/search-patients', [DoctorController::class, 'apiSearchPatients']);
        
        // Consulta
        Route::post('/consultation', [DoctorController::class, 'apiStoreConsultation']);
        
        // Diagnósticos
        Route::get('/diagnostics', [DoctorController::class, 'apiDiagnostics']);
        Route::post('/diagnostic', [DoctorController::class, 'apiStoreDiagnostic']);
        
        // Recetas
        Route::get('/prescriptions', [DoctorController::class, 'apiPrescriptions']);
        Route::post('/prescription', [DoctorController::class, 'apiStorePrescription']);
        Route::post('/prescription/{id}/cancel', [DoctorController::class, 'apiCancelPrescription']);
        
        // Signos Vitales
        Route::get('/vitals', [DoctorController::class, 'apiVitals']);
        
        // Tratamientos
        Route::get('/treatments', [DoctorController::class, 'apiTreatments']);
        Route::post('/treatment', [DoctorController::class, 'apiStoreTreatment']);
        
        // Hospitalización
        Route::get('/hospitalization', [DoctorController::class, 'apiHospitalization']);
        Route::post('/hospitalization', [DoctorController::class, 'apiStoreHospitalization']);
        
        // Camas
        Route::get('/beds', [DoctorController::class, 'apiBeds']);
        Route::get('/beds/{id}', [DoctorController::class, 'apiBedDetail']);
        
        // Farmacia
        Route::get('/pharmacy-stock', [DoctorController::class, 'apiPharmacyStock']);
        Route::get('/supplies', [DoctorController::class, 'apiSupplies']);
        
        // Solicitudes de servicio
        Route::post('/service-request', [DoctorController::class, 'apiStoreServiceRequest']);
        
        // Reportes
        Route::get('/reports', [DoctorController::class, 'apiReports']);
        Route::get('/deaths', [DoctorController::class, 'apiDeaths']);
        Route::get('/deaths/{id}/certificate', [DoctorController::class, 'apiDeathCertificate']);
        Route::get('/export-pdf', [DoctorController::class, 'apiExportPDF']);
        Route::get('/export-excel', [DoctorController::class, 'apiExportExcel']);
        
        // UCI
        Route::get('/uci', [DoctorController::class, 'apiUCI']);
        Route::get('/uci/{id}', [DoctorController::class, 'apiUCIDetail']);
    });
    
    // ==========================================
    // ALERTAS - SIN ROLES (SOLO AUTENTICACIÓN)
    // ==========================================
    Route::prefix('doctor')->group(function () {
        Route::get('/alerts', [DoctorController::class, 'apiAlerts']);
        Route::get('/alerts/summary', [DoctorController::class, 'apiAlertsSummary']);
        Route::post('/alerts/{id}/read', [DoctorController::class, 'apiMarkAlertRead']);
        Route::delete('/alerts/{id}', [DoctorController::class, 'apiDeleteAlert']);
    });
    
    // ==========================================
    //  RUTAS DE EMERGENCIA (CÓDIGO AZUL) - CORREGIDO
    // ==========================================
    Route::prefix('emergency')->group(function () {
        Route::post('/activate', [EmergencyController::class, 'activate']);
        Route::get('/active', [EmergencyController::class, 'active']);
        Route::post('/{id}/resolve', [EmergencyController::class, 'resolve']);
        Route::get('/patients', [EmergencyController::class, 'getPatients']); 
    });

}); // <--- CIERRE DEL GRUPO PRINCIPAL auth:sanctum
