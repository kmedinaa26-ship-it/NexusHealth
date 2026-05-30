<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\NurseController;
use App\Http\Controllers\DoctorController;

Route::get('/', function () { return redirect()->route('login'); });

// ==========================================
// MÉDICO
// ==========================================
Route::middleware(['auth', 'verified', 'role:Médico A,Médico B,Médico C'])->prefix('medico')->name('medico.')->group(function () {
    Route::get('/dashboard', [DoctorController::class, 'dashboard'])->name('dashboard');
    Route::get('/seleccionar', [DoctorController::class, 'seleccionar'])->name('seleccionar');
    Route::post('/seleccionar-perfil', [DoctorController::class, 'seleccionarPerfil'])->name('seleccionarPerfil');
    Route::post('/validar-pin', [DoctorController::class, 'validarPin'])->name('validarPin');

    // PACIENTES - CRUD
    Route::get('/pacientes', [DoctorController::class, 'pacientes'])->name('pacientes');
    Route::post('/pacientes/{id}/asignar', [DoctorController::class, 'asignarPaciente'])->name('asignarPaciente');
    Route::get('registrar-paciente', [DoctorController::class, 'registrarPaciente'])->name('registrarPaciente');
    Route::post('registrar-paciente', [DoctorController::class, 'storeNuevoPaciente'])->name('storeNuevoPaciente');
    Route::post('/pacientes/{id}/transferir', [DoctorController::class, 'transferirPaciente'])->name('transferirPaciente');
    Route::get('/pacientes/{id}/editar', [DoctorController::class, 'editarPaciente'])->name('editarPaciente');
    Route::put('/pacientes/{id}', [DoctorController::class, 'actualizarPaciente'])->name('actualizarPaciente');
    Route::post('/pacientes/{id}/alta', [DoctorController::class, 'darAlta'])->name('darAlta');
    Route::post('/pacientes/{id}/defuncion', [DoctorController::class, 'registrarDefuncion'])->name('registrarDefuncion');
    Route::post('/pacientes/{id}/derivar', [DoctorController::class, 'derivarPaciente'])->name('derivarPaciente');

    // CONSULTA
    Route::get('/consulta', [DoctorController::class, 'consulta'])->name('consulta');
    Route::post('/consulta', [DoctorController::class, 'storeConsulta'])->name('storeConsulta');

    // DIAGNÓSTICOS - CRUD
    Route::get('/diagnosticos', [DoctorController::class, 'diagnosticos'])->name('diagnosticos');
    Route::post('/diagnosticos', [DoctorController::class, 'storeDiagnostico'])->name('storeDiagnostico');
    Route::put('/diagnosticos/{id}', [DoctorController::class, 'actualizarDiagnostico'])->name('actualizarDiagnostico');

    // RECETAS - CRUD
    Route::get('/recetas', [DoctorController::class, 'recetas'])->name('recetas');
    Route::post('/recetas', [DoctorController::class, 'storeReceta'])->name('storeReceta');
    Route::put('/recetas/{id}/cancelar', [DoctorController::class, 'cancelarReceta'])->name('cancelarReceta');

    // SIGNOS VITALES
    Route::get('/signos-vitales', [DoctorController::class, 'signosVitales'])->name('signos');

    // ESTUDIOS - CRUD
    Route::get('/estudios', [DoctorController::class, 'estudios'])->name('estudios');
    Route::post('/estudios', [DoctorController::class, 'storeEstudio'])->name('storeEstudio');
    Route::put('/estudios/{id}/resultado', [DoctorController::class, 'resultadoEstudio'])->name('resultadoEstudio');
    Route::delete('/estudios/{id}', [DoctorController::class, 'eliminarEstudio'])->name('eliminarEstudio');

    // HOSPITALIZACIÓN - CRUD
    Route::get('/hospitalizacion', [DoctorController::class, 'hospitalizacion'])->name('hospitalizacion');
    Route::post('/hospitalizacion', [DoctorController::class, 'storeHospitalizacion'])->name('storeHospitalizacion');
    Route::post('/hospitalizacion/{id}/alta', [DoctorController::class, 'altaHospitalizacion'])->name('altaHospitalizacion');
    Route::put('/hospitalizacion/{id}', [DoctorController::class, 'actualizarHospitalizacion'])->name('actualizarHospitalizacion');

    // TRATAMIENTOS
    Route::get('/tratamientos', [DoctorController::class, 'tratamientos'])->name('tratamientos');
    Route::post('/tratamientos', [DoctorController::class, 'storeTratamiento'])->name('storeTratamiento');

    // EVOLUCIÓN - CRUD
    Route::get('/evolucion', [DoctorController::class, 'evolucion'])->name('evolucion');
    Route::post('/evolucion', [DoctorController::class, 'storeEvolution'])->name('storeEvolution');
    Route::put('/evolucion/{id}', [DoctorController::class, 'actualizarEvolucion'])->name('actualizarEvolucion');
    Route::delete('/evolucion/{id}', [DoctorController::class, 'eliminarEvolucion'])->name('eliminarEvolucion');

    // CAMAS
    Route::get('/camas', [DoctorController::class, 'camas'])->name('camas');

    // SERVICIOS - CRUD
    Route::get('/servicios', [DoctorController::class, 'servicios'])->name('servicios');
    Route::post('/servicios', [DoctorController::class, 'solicitarServicio'])->name('solicitarServicio');
    Route::put('/servicios/{id}/cancelar', [DoctorController::class, 'cancelarServicio'])->name('cancelarServicio');

    // FARMACIA / INSUMOS
    Route::get('/farmacia-stock', [DoctorController::class, 'farmaciaStock'])->name('farmaciaStock');
    Route::get('/insumos', [DoctorController::class, 'insumos'])->name('insumos');

    // DEFUNCIONES / ÓBITOS
    Route::get('/defunciones', [DoctorController::class, 'defunciones'])->name('defunciones');
    Route::get('/defunciones/{id}', [DoctorController::class, 'verDefuncion'])->name('verDefuncion');
    Route::post('/defunciones/{id}/certificado', [DoctorController::class, 'generarCertificado'])->name('generarCertificado');

    // ALERTAS
    Route::get('/alertas', [DoctorController::class, 'alertas'])->name('alertas');
    Route::post('/alertas/{id}/read', [DoctorController::class, 'markAlertRead'])->name('markAlertRead');
    Route::delete('/alertas/{id}', [DoctorController::class, 'eliminarAlerta'])->name('eliminarAlerta');

    // REPORTES
    Route::get('/reportes', [DoctorController::class, 'reportes'])->name('reportes');

    // SOLO MÉDICO A
    Route::get('/uci', [DoctorController::class, 'uci'])->name('uci');
    Route::get('/quirofano', [DoctorController::class, 'quirofano'])->name('quirofano');
    Route::get('/controlados', [DoctorController::class, 'controlados'])->name('controlados');
    Route::get('/ia-medica', [DoctorController::class, 'iaMedica'])->name('iaMedica');
    Route::get('/derivaciones', [DoctorController::class, 'derivaciones'])->name('derivaciones');
    Route::post('/derivaciones', [DoctorController::class, 'storeDerivacion'])->name('storeDerivacion');
    Route::get('/auditoria', [DoctorController::class, 'auditoria'])->name('auditoria');
});

// ENFERMERIA
Route::middleware(['auth', 'verified', 'role:Enfermera A,Enfermera B,Enfermera C'])->prefix('enfermeria')->name('enfermeria.')->group(function () {
    Route::get('/dashboard', [NurseController::class, 'dashboard'])->name('dashboard');
    Route::get('/triage', [NurseController::class, 'triage'])->name('triage');
    Route::post('/triage', [NurseController::class, 'storeTriage'])->name('storeTriage');
    Route::post('/triage/{id}/status', [NurseController::class, 'updateTriageStatus'])->name('updateTriageStatus');
    Route::get('/signos-vitales', [NurseController::class, 'signosVitales'])->name('signos');
    Route::post('/signos-vitales', [NurseController::class, 'storeVitals'])->name('storeSignos');
    Route::get('/pacientes', [NurseController::class, 'pacientes'])->name('pacientes');
    Route::post('/pacientes/{id}/enviar', [NurseController::class, 'enviarA'])->name('enviarA');
    Route::get('/hospitalizacion', [NurseController::class, 'hospitalizacion'])->name('hospitalizacion');
    Route::post('/hospitalizacion', [NurseController::class, 'storeHospitalization'])->name('storeHospitalization');
    Route::get('/evolucion', [NurseController::class, 'evolucion'])->name('evolucion');
    Route::post('/evolucion', [NurseController::class, 'storeEvolution'])->name('storeEvolucion');
    Route::get('/alertas', [NurseController::class, 'alertas'])->name('alertas');
    Route::post('/alertas/{id}/read', [NurseController::class, 'markAlertRead'])->name('markAlertRead');
    Route::get('/medicamentos', [NurseController::class, 'medicamentos'])->name('medicamentos');
    Route::get('/documentacion', [NurseController::class, 'documentacion'])->name('documentacion');
    Route::get('/solicitudes-farmacia', [NurseController::class, 'solicitudesFarmacia'])->name('solicitudesFarmacia');
    Route::get('/reportes', [NurseController::class, 'reportes'])->name('reportes');
});

// FARMACIA
Route::middleware(['auth', 'verified', 'role:Farmacéutico,Admin Farmacia'])->prefix('farmacia')->name('farmacia.')->group(function () {
    Route::get('/dashboard', [PharmacyController::class, 'dashboard'])->name('dashboard');
    Route::get('/inventario', [PharmacyController::class, 'inventario'])->name('inventario');
    Route::post('/inventario', [PharmacyController::class, 'storeMedication'])->name('storeMedication');
    Route::put('/inventario/{id}', [PharmacyController::class, 'updateMedication'])->name('updateMedication');
    Route::get('/dispensacion', [PharmacyController::class, 'dispensacion'])->name('dispensacion');
    Route::post('/dispensacion', [PharmacyController::class, 'dispensar'])->name('dispensar');
    Route::get('/recetas-pendientes', [PharmacyController::class, 'recetasPendientes'])->name('recetasPendientes');
    Route::post('/recetas-pendientes/{id}/approve', [PharmacyController::class, 'approveReceta'])->name('approveReceta');
    Route::post('/recetas-pendientes/{id}/reject', [PharmacyController::class, 'rejectReceta'])->name('rejectReceta');
    Route::get('/controlados', [PharmacyController::class, 'controlados'])->name('controlados');
    Route::get('/alertas-stock', [PharmacyController::class, 'alertasStock'])->name('alertasStock');
    Route::get('/proveedores', [PharmacyController::class, 'proveedores'])->name('proveedores');
    Route::post('/proveedores', [PharmacyController::class, 'storeProveedor'])->name('storeProveedor');
    Route::get('/vencimientos', [PharmacyController::class, 'vencimientos'])->name('vencimientos');
    Route::get('/reportes', [PharmacyController::class, 'reportes'])->name('reportes');
    Route::get('/interacciones', [PharmacyController::class, 'interacciones'])->name('interacciones');
    Route::get('/crash-carts', [PharmacyController::class, 'crashCarts'])->name('crashCarts');
    Route::post('/crash-carts/{id}/verificar', [PharmacyController::class, 'verificarCart'])->name('verificarCart');
    Route::get('/historial-paciente/{id}', [PharmacyController::class, 'historialPaciente'])->name('historialPaciente');
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
    Route::post('/farmacia', [SuperAdminController::class, 'storeMedication'])->name('storeMedication');
    Route::get('/finanzas/auth', [SuperAdminController::class, 'finanzasAuth'])->name('finanzas.auth');
    Route::post('/finanzas/verify', [SuperAdminController::class, 'finanzasVerify'])->name('finanzas.verify');
    Route::post('/finanzas/lock', [SuperAdminController::class, 'finanzasLock'])->name('finanzas.lock');
    Route::get('/finanzas', [SuperAdminController::class, 'finanzas'])->name('finanzas');
    Route::put('/finanzas/{invoice}/cancel', [SuperAdminController::class, 'cancelInvoice'])->name('finanzas.cancel');
    Route::get('/finanzas/export/pdf', [SuperAdminController::class, 'exportFinancePDF'])->name('finanzas.export.pdf');
    Route::get('/finanzas/export/csv', [SuperAdminController::class, 'exportFinanceCSV'])->name('finanzas.export.csv');
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
});

require __DIR__.'/auth.php';
