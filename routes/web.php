<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\PharmacyController;

Route::get('/', function () { return redirect()->route('login'); });

// FARMACIA
Route::middleware(['auth', 'verified', 'role:Farmacéutico,Admin Farmacia'])->prefix('farmacia')->name('farmacia.')->group(function () {
    Route::get('/dashboard', [PharmacyController::class, 'dashboard'])->name('dashboard');
    Route::get('/inventario', [PharmacyController::class, 'inventory'])->name('inventory');
    Route::post('/inventario', [PharmacyController::class, 'storeMedication'])->name('storeMedication');
    Route::get('/controlados', [PharmacyController::class, 'controlled'])->name('controlled');
    Route::get('/enfermera', [PharmacyController::class, 'enfermeraMeds'])->name('enfermeraMeds');
    Route::get('/dispensacion', [PharmacyController::class, 'dispensacion'])->name('dispensacion');
    Route::post('/dispensacion', [PharmacyController::class, 'dispenseMedication'])->name('dispense');
    Route::get('/paciente/{id}/historial', [PharmacyController::class, 'pacienteHistorial'])->name('pacienteHistorial');
    Route::get('/proveedores', [PharmacyController::class, 'proveedores'])->name('proveedores');
    Route::get('/ordenes', [PharmacyController::class, 'ordenes'])->name('ordenes');
    Route::get('/ordenes/crear', [PharmacyController::class, 'crearOrden'])->name('crearOrden');
    Route::post('/ordenes', [PharmacyController::class, 'storeOrden'])->name('storeOrden');
    Route::post('/ordenes/{id}/recibir', [PharmacyController::class, 'recibirOrden'])->name('recibirOrden');
    Route::get('/crash-carts', [PharmacyController::class, 'crashCarts'])->name('crashCarts');
    Route::post('/crash-carts/{id}/check', [PharmacyController::class, 'checkCart'])->name('checkCart');
    Route::get('/movimientos', [PharmacyController::class, 'movimientos'])->name('movimientos');
    Route::get('/anomalias', [PharmacyController::class, 'anomalias'])->name('anomalias');
    Route::get('/consumo', [PharmacyController::class, 'consumo'])->name('consumo');
    Route::get('/desabasto', [PharmacyController::class, 'desabasto'])->name('desabasto');
    Route::post('/desabasto/request', [PharmacyController::class, 'requestRestock'])->name('requestRestock');
    Route::post('/desabasto/{id}/approve', [PharmacyController::class, 'approveRestock'])->name('approveRestock');
    Route::get('/desabasto/{id}/alternatives', [PharmacyController::class, 'getAlternatives'])->name('alternatives');
    Route::get('/exportar', [PharmacyController::class, 'exportar'])->name('exportar');
    Route::get('/exportar/pdf', [PharmacyController::class, 'exportInventoryPDF'])->name('export.pdf');
    Route::get('/exportar/csv', [PharmacyController::class, 'exportInventoryCSV'])->name('export.csv');
    Route::get('/carga', [PharmacyController::class, 'carga'])->name('carga');
    Route::post('/carga', [PharmacyController::class, 'uploadCSV'])->name('uploadCSV');
    Route::get('/traspasos', [PharmacyController::class, 'traspasos'])->name('traspasos');
    Route::post('/traspasos', [PharmacyController::class, 'storeTraspaso'])->name('traspaso');
});

// SUPERADMIN
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
