<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\NurseController;

Route::get('/', function () {
    return redirect()->route('login');
});

// ==========================================
// RUTAS DE ENFERMERIA
// ==========================================
Route::middleware(['auth', 'verified', 'role:Enfermera A,Enfermera B,Enfermera C'])->prefix('enfermeria')->name('enfermeria.')->group(function () {
    Route::get('/dashboard', [NurseController::class, 'dashboard'])->name('dashboard');
    Route::get('/triage', [NurseController::class, 'triage'])->name('triage');
    Route::post('/triage', [NurseController::class, 'storeTriage'])->name('storeTriage');
    Route::get('/signos-vitales', [NurseController::class, 'signosVitales'])->name('signos');
    Route::post('/signos-vitales', [NurseController::class, 'storeVitals'])->name('storeVitals');
    Route::get('/pacientes', [NurseController::class, 'pacientes'])->name('pacientes');
    Route::post('/pacientes/{id}/enviar', [NurseController::class, 'enviarA'])->name('enviarA');
    Route::put('/pacientes/{id}/reasignar', [NurseController::class, 'reasignarPaciente'])->name('reasignar');
    Route::put('/pacientes/{id}/alta', [NurseController::class, 'darAlta'])->name('darAlta');
    Route::get('/hospitalizacion', [NurseController::class, 'hospitalizacion'])->name('hospitalizacion');
    Route::post('/hospitalizacion', [NurseController::class, 'storeHospitalization'])->name('storeHospitalization');
    Route::get('/evolucion', [NurseController::class, 'evolucion'])->name('evolucion');
    Route::post('/evolucion', [NurseController::class, 'storeEvolution'])->name('storeEvolution');
    Route::get('/alertas', [NurseController::class, 'alertas'])->name('alertas');
    Route::post('/alertas/{id}/read', [NurseController::class, 'markAlertRead'])->name('markAlertRead');
    Route::get('/medicamentos', [NurseController::class, 'medicamentos'])->name('medicamentos');
    Route::get('/documentacion', [NurseController::class, 'documentacion'])->name('documentacion');
    Route::get('/solicitudes-farmacia', [NurseController::class, 'solicitudesFarmacia'])->name('solicitudesFarmacia');
    Route::get('/reportes', [NurseController::class, 'reportes'])->name('reportes');
});

// ==========================================
// RUTAS DE FARMACIA
// ==========================================
Route::middleware(['auth', 'verified', 'role:Farmacéutico,Admin Farmacia'])->prefix('farmacia')->name('farmacia.')->group(function () {
    Route::get('/dashboard', [PharmacyController::class, 'dashboard'])->name('dashboard');
    Route::get('/inventario', [PharmacyController::class, 'inventory'])->name('inventory');
    Route::get('/enfermera-meds', [PharmacyController::class, 'enfermeraMeds'])->name('enfermeraMeds');
    Route::get('/controlados', [PharmacyController::class, 'controlled'])->name('controlled');
    Route::post('/medicamento', [PharmacyController::class, 'storeMedication'])->name('storeMedication');
    Route::get('/dispensacion', [PharmacyController::class, 'dispensacion'])->name('dispensacion');
    Route::post('/dispensacion', [PharmacyController::class, 'dispenseMedication'])->name('dispense');
    Route::get('/proveedores', [PharmacyController::class, 'proveedores'])->name('proveedores');
    Route::get('/movimientos', [PharmacyController::class, 'movimientos'])->name('movimientos');
    Route::get('/anomalias', [PharmacyController::class, 'anomalias'])->name('anomalias');
    Route::get('/consumo', [PharmacyController::class, 'consumo'])->name('consumo');
    Route::get('/desabasto', [PharmacyController::class, 'desabasto'])->name('desabasto');
    Route::post('/desabasto/solicitar', [PharmacyController::class, 'solicitarReStock'])->name('solicitarReStock');
    Route::get('/desabasto/{id}/alternativas', [PharmacyController::class, 'alternativas'])->name('alternativas');
    Route::post('/desabasto/{id}/aprobar', [PharmacyController::class, 'aprobarSolicitud'])->name('aprobarSolicitud');
    Route::get('/exportar', [PharmacyController::class, 'exportar'])->name('exportar');
    Route::get('/carga', [PharmacyController::class, 'carga'])->name('carga');
    Route::post('/carga', [PharmacyController::class, 'uploadCSV'])->name('uploadCSV');
    Route::get('/traspasos', [PharmacyController::class, 'traspasos'])->name('traspasos');
    Route::post('/traspasos', [PharmacyController::class, 'storeTraspaso'])->name('traspaso');
    Route::get('/ordenes', [PharmacyController::class, 'ordenes'])->name('ordenes');
    Route::post('/ordenes', [PharmacyController::class, 'storeOrden'])->name('storeOrden');
    Route::post('/ordenes/{id}/recibir', [PharmacyController::class, 'recibirOrden'])->name('recibirOrden');
    Route::get('/crash-carts', [PharmacyController::class, 'crashCarts'])->name('crashCarts');
    Route::post('/crash-carts/{id}/verificar', [PharmacyController::class, 'verificarCart'])->name('verificarCart');
    Route::get('/interacciones', [PharmacyController::class, 'interacciones'])->name('interacciones');
    Route::get('/historial-paciente/{id}', [PharmacyController::class, 'historialPaciente'])->name('historialPaciente');
});

// ==========================================
// RUTAS DE SUPERADMIN
// ==========================================
Route::middleware(['auth', 'verified', 'role:SuperAdmin,Administrador Hospitalario'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/personal', [SuperAdminController::class, 'personal'])->name('personal');
    Route::post('/personal', [SuperAdminController::class, 'storeUser'])->name('storeUser');
    Route::post('/personal/{user}/approve', [SuperAdminController::class, 'approveUser'])->name('approveUser');
    Route::put('/personal/{user}/reject', [SuperAdminController::class, 'rejectUser'])->name('rejectUser');
    Route::put('/personal/{user}/role', [SuperAdminController::class, 'updateRole'])->name('updateRole');
    Route::put('/personal/{user}/status', [SuperAdminController::class, 'toggleStatus'])->name('toggleStatus');
    Route::delete('/personal/{user}', [SuperAdminController::class, 'deleteUser'])->name('deleteUser');
    Route::get('/score-riesgo', [SuperAdminController::class, 'scoreRiesgo'])->name('scoreRiesgo');
    Route::get('/pacientes', [SuperAdminController::class, 'pacientes'])->name('pacientes');
    Route::get('/urgencias', [SuperAdminController::class, 'urgencias'])->name('urgencias');
    Route::post('/urgencias', [SuperAdminController::class, 'storeTriage'])->name('storeTriage');
    Route::put('/urgencias/{triage}/vitals', [SuperAdminController::class, 'updateVitals'])->name('updateVitals');
    Route::put('/urgencias/{triage}/derive', [SuperAdminController::class, 'derivePatient'])->name('derivePatient');
    Route::get('/urgencias/{triage}/pase-salida', [SuperAdminController::class, 'paseSalida'])->name('paseSalida');
    Route::get('/farmacia', [SuperAdminController::class, 'farmacia'])->name('farmacia');
    Route::post('/farmacia/prescribe', [SuperAdminController::class, 'prescribe'])->name('farmacia.prescribe');
    Route::post('/farmacia', [SuperAdminController::class, 'storeMedication'])->name('storeMedication');
    Route::get('/finanzas/auth', [SuperAdminController::class, 'finanzasAuth'])->name('finanzas.auth');
    Route::post('/finanzas/verify', [SuperAdminController::class, 'finanzasVerify'])->name('finanzas.verify');
    Route::post('/finanzas/lock', [SuperAdminController::class, 'finanzasLock'])->name('finanzas.lock');
    Route::middleware('finance.pin')->group(function () {
        Route::get('/finanzas', [SuperAdminController::class, 'finanzas'])->name('finanzas');
        Route::put('/finanzas/{invoice}/cancel', [SuperAdminController::class, 'cancelInvoice'])->name('finanzas.cancel');
        Route::get('/finanzas/export/pdf', [SuperAdminController::class, 'exportFinancePDF'])->name('finanzas.export.pdf');
        Route::get('/finanzas/export/csv', [SuperAdminController::class, 'exportFinanceCSV'])->name('finanzas.export.csv');
    });
    Route::get('/auditoria', [SuperAdminController::class, 'auditoria'])->name('auditoria');
    Route::get('/actividad-sospechosa', [SuperAdminController::class, 'actividadSospechosa'])->name('actividadSospechosa');
    Route::get('/monitor', [SuperAdminController::class, 'monitorLive'])->name('monitorLive');
    Route::delete('/monitor/{id}', [SuperAdminController::class, 'forceLogout'])->name('forceLogout');
    Route::get('/roles', [SuperAdminController::class, 'roles'])->name('roles');
    Route::post('/roles/toggle', [SuperAdminController::class, 'togglePermission'])->name('togglePermission');
    Route::get('/ingesta', [SuperAdminController::class, 'ingesta'])->name('ingesta');
    Route::post('/ingesta', [SuperAdminController::class, 'uploadCSV'])->name('uploadCSV');
    Route::get('/limpieza', [SuperAdminController::class, 'limpieza'])->name('limpieza');
    Route::post('/limpieza', [SuperAdminController::class, 'cleanData'])->name('cleanData');
    Route::get('/mapa-calor', [SuperAdminController::class, 'mapaCalor'])->name('mapaCalor');
    Route::get('/reportes', function () { return view('superadmin.reportes'); })->name('reportes');
    Route::get('/reportes/personal', [SuperAdminController::class, 'reportPersonal'])->name('report.personal');
    Route::get('/reportes/camas', [SuperAdminController::class, 'reportCamadas'])->name('report.camas');
    Route::get('/reportes/farmacia', [SuperAdminController::class, 'reportFarmacia'])->name('report.farmacia');
    Route::get('/camas', [SuperAdminController::class, 'camas'])->name('camas');
    Route::post('/camas', [SuperAdminController::class, 'storeBed'])->name('storeBed');
    Route::put('/camas/{bed}/status', [SuperAdminController::class, 'updateBedStatus'])->name('updateBedStatus');
    Route::get('/proveedores', [SuperAdminController::class, 'proveedores'])->name('proveedores');
    Route::post('/proveedores', [SuperAdminController::class, 'storeProvider'])->name('storeProvider');
    Route::put('/proveedores/{provider}/status', [SuperAdminController::class, 'toggleProviderStatus'])->name('toggleProviderStatus');
    Route::delete('/proveedores/{provider}', [SuperAdminController::class, 'deleteProvider'])->name('deleteProvider');
});

require __DIR__.'/auth.php';
