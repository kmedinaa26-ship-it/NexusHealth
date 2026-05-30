<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdminController;

Route::get('/', function () { return redirect()->route('login'); });

Route::middleware(['auth', 'verified', 'role:SuperAdmin,Administrador Hospitalario'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperAdminController::class, 'dashboard'])->name('dashboard');
    
    // Personal, Pacientes y Riesgo
    Route::get('/personal', [SuperAdminController::class, 'personal'])->name('personal');
    Route::post('/personal', [SuperAdminController::class, 'storeUser'])->name('storeUser');
    Route::post('/personal/{user}/approve', [SuperAdminController::class, 'approveUser'])->name('approveUser');
    Route::put('/personal/{user}/reject', [SuperAdminController::class, 'rejectUser'])->name('rejectUser');
    Route::put('/personal/{user}/role', [SuperAdminController::class, 'updateRole'])->name('updateRole');
    Route::put('/personal/{user}/status', [SuperAdminController::class, 'toggleStatus'])->name('toggleStatus');
    Route::delete('/personal/{user}', [SuperAdminController::class, 'deleteUser'])->name('deleteUser');
    Route::get('/score-riesgo', [SuperAdminController::class, 'scoreRiesgo'])->name('scoreRiesgo');
    Route::get('/pacientes', [SuperAdminController::class, 'pacientes'])->name('pacientes');
    
    // Urgencias
    Route::get('/urgencias', [SuperAdminController::class, 'urgencias'])->name('urgencias');
    Route::post('/urgencias', [SuperAdminController::class, 'storeTriage'])->name('storeTriage');
    Route::put('/urgencias/{triage}/vitals', [SuperAdminController::class, 'updateVitals'])->name('updateVitals');
    Route::put('/urgencias/{triage}/derive', [SuperAdminController::class, 'derivePatient'])->name('derivePatient');
    Route::get('/urgencias/{triage}/pase-salida', [SuperAdminController::class, 'paseSalida'])->name('paseSalida');

    // Finanzas (Seguridad Bancaria + Exportación)
    Route::get('/finanzas/auth', [SuperAdminController::class, 'finanzasAuth'])->name('finanzas.auth');
    Route::post('/finanzas/verify', [SuperAdminController::class, 'finanzasVerify'])->name('finanzas.verify');
    Route::post('/finanzas/lock', [SuperAdminController::class, 'finanzasLock'])->name('finanzas.lock');
    Route::middleware('finance.pin')->group(function () {
        Route::get('/finanzas', [SuperAdminController::class, 'finanzas'])->name('finanzas');
        Route::put('/finanzas/{invoice}/cancel', [SuperAdminController::class, 'cancelInvoice'])->name('finanzas.cancel');
        Route::get('/finanzas/export/pdf', [SuperAdminController::class, 'exportFinancePDF'])->name('finanzas.export.pdf');
        Route::get('/finanzas/export/csv', [SuperAdminController::class, 'exportFinanceCSV'])->name('finanzas.export.csv');
    });

    // Seguridad y Live
    Route::get('/auditoria', [SuperAdminController::class, 'auditoria'])->name('auditoria');
    Route::get('/actividad-sospechosa', [SuperAdminController::class, 'actividadSospechosa'])->name('actividadSospechosa');
    Route::get('/monitor', [SuperAdminController::class, 'monitorLive'])->name('monitorLive');
    Route::delete('/monitor/{id}', [SuperAdminController::class, 'forceLogout'])->name('forceLogout');
    Route::get('/roles', [SuperAdminController::class, 'roles'])->name('roles');
    Route::post('/roles/toggle', [SuperAdminController::class, 'togglePermission'])->name('togglePermission');

    // Datos
    Route::get('/ingesta', [SuperAdminController::class, 'ingesta'])->name('ingesta');
    Route::post('/ingesta', [SuperAdminController::class, 'uploadCSV'])->name('uploadCSV');
    Route::get('/limpieza', [SuperAdminController::class, 'limpieza'])->name('limpieza');
    Route::post('/limpieza', [SuperAdminController::class, 'cleanData'])->name('cleanData');

    // Hospital
    Route::get('/mapa-calor', [SuperAdminController::class, 'mapaCalor'])->name('mapaCalor');
    Route::get('/farmacia', [SuperAdminController::class, 'farmacia'])->name('farmacia');
    Route::post('/farmacia', [SuperAdminController::class, 'storeMedication'])->name('storeMedication');
    Route::get('/reportes', function () { return view('superadmin.reportes'); })->name('reportes');
    Route::get('/reportes/personal', [SuperAdminController::class, 'reportPersonal'])->name('report.personal');
    Route::get('/reportes/camas', [SuperAdminController::class, 'reportCamas'])->name('report.camas');
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
