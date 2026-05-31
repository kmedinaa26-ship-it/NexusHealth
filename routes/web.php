<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\NurseController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\SpecialistController;
use App\Http\Controllers\SpecialtyController;

Route::get('/dashboard', function () { return redirect()->route('login'); })->name('dashboard');
Route::get('/', function () { return redirect()->route('login'); });

// ==========================================
// MÉDICO
// ==========================================
Route::middleware(['auth', 'verified', 'role:Médico A,Médico B,Médico C,Especialista'])->prefix('medico')->name('medico.')->group(function () {
    Route::get('/especialista', [SpecialistController::class, 'dashboard'])->name('especialista.dashboard');
    Route::get('/especialista/pacientes', [SpecialistController::class, 'misPacientes']);
    Route::get('/especialista/hospitalizados', [SpecialistController::class, 'hospitalizados']);
    Route::get('/especialista/derivaciones', [SpecialistController::class, 'derivaciones']);
    Route::get('/especialista/reportes', [SpecialistController::class, 'reportes']);
    Route::get('/especialista/ia-medica', [SpecialistController::class, 'iaMedica']);
    Route::get('/especialista/medicamentos', [SpecialistController::class, 'medicamentos']);
    Route::post('/especialista/derivaciones/crear', [SpecialistController::class, 'crearDerivacion']);
    Route::post('/especialista/derivaciones/{id}/aceptar', [SpecialistController::class, 'aceptarDerivacion']);
    Route::post('/especialista/derivaciones/{id}/rechazar', [SpecialistController::class, 'rechazarDerivacion']);
    Route::post('/especialista/derivaciones/{id}/reagendar', [SpecialistController::class, 'reagendarDerivacion']);
    Route::post('/especialista/aceptar/{id}', [SpecialistController::class, 'aceptarPaciente']);
    Route::post('/especialista/derivar/{id}', [SpecialistController::class, 'derivarPaciente']);
    Route::get('/especialidades', [SpecialtyController::class, 'index'])->name('especialidades');
    Route::get('/especialidades/{id}', [SpecialtyController::class, 'show'])->name('especialidades.show');
    Route::post('/especialidades/derivar/{patientId}', [SpecialtyController::class, 'derivar'])->name('especialidades.derivar');
    Route::get('/dashboard', [DoctorController::class, 'dashboard'])->name('dashboard');
    Route::get('/seleccionar', [DoctorController::class, 'seleccionar'])->name('seleccionar');
    Route::post('/seleccionar-perfil', [DoctorController::class, 'seleccionarPerfil'])->name('seleccionarPerfil');
    Route::post('/validar-pin', [DoctorController::class, 'validarPin'])->name('validarPin');
    Route::post('/pin-verify', [DoctorController::class, 'validarPin'])->name('pin.verify');
    Route::get('/verify-profile', [DoctorController::class, 'seleccionar'])->name('verify.profile');

    // PACIENTES
    Route::get('/pacientes', [DoctorController::class, 'pacientes'])->name('pacientes');
    Route::post('/pacientes/{id}/alta', [NurseController::class, 'darAlta'])->name('darAlta');
    Route::post('/pacientes/{id}/asignar', [DoctorController::class, 'asignarPaciente'])->name('asignarPaciente');
    Route::get('/registrar-paciente', [DoctorController::class, 'registrarPaciente'])->name('registrarPaciente');
    Route::post('/registrar-paciente', [DoctorController::class, 'storeNuevoPaciente'])->name('storeNuevoPaciente');
    Route::get('/pacientes/{id}/editar', [DoctorController::class, 'editarPaciente'])->name('editarPaciente');
    Route::put('/pacientes/{id}', [DoctorController::class, 'actualizarPaciente'])->name('actualizarPaciente');
    Route::post('/pacientes/{id}/alta', [DoctorController::class, 'darAlta'])->name('darAlta');
    Route::post('/pacientes/{id}/defuncion', [DoctorController::class, 'registrarDefuncion'])->name('registrarDefuncion');
    Route::post('/pacientes/{id}/derivar', [DoctorController::class, 'derivarPaciente'])->name('derivarPaciente');

    // CONSULTA
    Route::get('/consulta', [DoctorController::class, 'consulta'])->name('consulta');
    Route::post('/consulta', [DoctorController::class, 'storeConsulta'])->name('storeConsulta');

    // DIAGNÓSTICOS
    Route::get('/diagnosticos', [DoctorController::class, 'diagnosticos'])->name('diagnosticos');
    Route::post('/diagnosticos', [DoctorController::class, 'storeDiagnostico'])->name('storeDiagnostico');

    // RECETAS
    Route::get('/recetas', [DoctorController::class, 'recetas'])->name('recetas');
    Route::post('/recetas', [DoctorController::class, 'storeReceta'])->name('storeReceta');
    Route::put('/recetas/{id}/cancelar', [DoctorController::class, 'cancelarReceta'])->name('cancelarReceta');

    // SIGNOS VITALES
    Route::get('/signos-vitales', [DoctorController::class, 'signosVitales'])->name('signos');

    // ESTUDIOS
    Route::get('/estudios', [DoctorController::class, 'estudios'])->name('estudios');
    Route::post('/estudios', [DoctorController::class, 'storeEstudio'])->name('storeEstudio');
    Route::put('/estudios/{id}/resultado', [DoctorController::class, 'resultadoEstudio'])->name('resultadoEstudio');
    Route::delete('/estudios/{id}', [DoctorController::class, 'eliminarEstudio'])->name('eliminarEstudio');

    // HOSPITALIZACIÓN
    Route::get('/hospitalizacion', [DoctorController::class, 'hospitalizacion'])->name('hospitalizacion');
    Route::post('/hospitalizacion', [DoctorController::class, 'storeHospitalizacion'])->name('storeHospitalizacion');
    Route::post('/hospitalizacion/{id}/alta', [DoctorController::class, 'altaHospitalizacion'])->name('altaHospitalizacion');

    // TRATAMIENTOS
    Route::get('/tratamientos', [DoctorController::class, 'tratamientos'])->name('tratamientos');
    Route::post('/tratamientos', [DoctorController::class, 'storeTratamiento'])->name('storeTratamiento');

    // EVOLUCIÓN
    Route::get('/api/pacientes-camas', [AppHttpControllersApiPatientController::class, 'pacientesParaCamas']);
    Route::get('/mapa-camas', [NurseController::class, 'mapaCamas'])->name('mapaCamas');
    Route::post('/camas/{id}/asignar', [NurseController::class, 'asignarCama'])->name('asignarCama');
    Route::post('/camas/{id}/liberar', [NurseController::class, 'liberarCama'])->name('liberarCama');
    Route::get('/evolucion', [DoctorController::class, 'evolucion'])->name('evolucion');
    Route::post('/evolucion', [NurseController::class, 'storeEvolucion'])->name('storeEvolution');
    Route::post('/evolucion', [DoctorController::class, 'storeEvolucion'])->name('storeEvolucion');
    Route::put('/evolucion/{id}', [DoctorController::class, 'actualizarEvolucion'])->name('actualizarEvolucion');
    Route::delete('/evolucion/{id}', [DoctorController::class, 'eliminarEvolucion'])->name('eliminarEvolucion');

    // CAMAS
    Route::get('/camas', [DoctorController::class, 'camas'])->name('camas');

    // SERVICIOS
    Route::get('/servicios', [DoctorController::class, 'servicios'])->name('servicios');
    Route::post('/servicios', [DoctorController::class, 'storeServicio'])->name('storeServicio');
    Route::put('/servicios/{id}/cancelar', [DoctorController::class, 'cancelarServicio'])->name('cancelarServicio');

    // FARMACIA / INSUMOS
    Route::get('/farmacia-stock', [DoctorController::class, 'farmaciaStock'])->name('farmaciaStock');
    Route::get('/insumos', [DoctorController::class, 'insumos'])->name('insumos');

    // DEFUNCIONES
    Route::get('/defunciones', [DoctorController::class, 'defunciones'])->name('defunciones');
    Route::get('/defunciones/{id}', [DoctorController::class, 'verDefuncion'])->name('verDefuncion');
    Route::get('/defunciones/{id}/pdf', [DoctorController::class, 'certificadoDefuncionPDF'])->name('certificadoDefuncion');

    // ALERTAS
    Route::get('/alertas', [DoctorController::class, 'alertas'])->name('alertas');
    Route::post('/alertas/{id}/read', [NurseController::class, 'markAlertRead'])->name('markAlertRead');
    Route::post('/alertas/{id}/read', [DoctorController::class, 'markAlertRead'])->name('markAlertRead');
    Route::delete('/alertas/{id}', [DoctorController::class, 'eliminarAlerta'])->name('eliminarAlerta');

    // REPORTES
    Route::get('/reportes', [DoctorController::class, 'reportes'])->name('reportes');
    Route::get('/reportes/pdf', [DoctorController::class, 'exportReportesPDF'])->name('reportes.pdf');

    // SOLO MÉDICO A
    Route::get('/uci', [DoctorController::class, 'uci'])->name('uci');
    Route::get('/quirofano', [DoctorController::class, 'quirofano'])->name('quirofano');
    Route::get('/controlados', [DoctorController::class, 'controlados'])->name('controlados');
    Route::get('/ia-medica', [DoctorController::class, 'iaMedica'])->name('iaMedica');
    Route::get('/hospitalizados', [SpecialistController::class, 'hospitalizados']);
    Route::get('/derivaciones', [SpecialistController::class, 'derivaciones']);
    Route::get('/ambulancias', [\App\Http\Controllers\AmbulanceController::class, 'index']);
    Route::post('/ambulancias/despachar', [\App\Http\Controllers\AmbulanceController::class, 'despachar']);
    Route::post('/ambulancias/{id}/llegada', [\App\Http\Controllers\AmbulanceController::class, 'llegada']);
    Route::get('/ambulancias/{id}/iot', [\App\Http\Controllers\AmbulanceController::class, 'actualizarIot']);
    Route::get('/hospital-live', [\App\Http\Controllers\AmbulanceController::class, 'hospitalLive']);
    Route::get('/pacientes', [SpecialistController::class, 'misPacientes'])->name('especialista.pacientes');
    Route::post('/derivaciones/crear', [SpecialistController::class, 'crearDerivacion'])->name('especialista.derivaciones.crear');
    Route::post('/derivaciones/{id}/aceptar', [SpecialistController::class, 'aceptarDerivacion'])->name('especialista.derivaciones.aceptar');
    Route::post('/derivaciones/{id}/rechazar', [SpecialistController::class, 'rechazarDerivacion'])->name('especialista.derivaciones.rechazar');
    Route::post('/derivaciones/{id}/reagendar', [SpecialistController::class, 'reagendarDerivacion'])->name('especialista.derivaciones.reagendar');
    Route::get('/derivaciones/{id}/pdf', [DoctorController::class, 'exportDerivacionPDF'])->name('derivacion.pdf');    Route::get('/auditoria/export/pdf', [SuperAdminController::class, 'exportAuditPDF'])->name('auditoria.export.pdf');
    Route::get('/auditoria/export/csv', [SuperAdminController::class, 'exportAuditCSV'])->name('auditoria.export.csv');
    Route::get('/auditoria/export/json', [SuperAdminController::class, 'exportAuditJSON'])->name('auditoria.export.json');
});

// ==========================================
// ENFERMERÍA
// ==========================================
Route::middleware(['auth', 'verified', 'role:Enfermera A,Enfermera B,Enfermera C'])->prefix('enfermeria')->name('enfermeria.')->group(function () {
    Route::get('/dashboard', [NurseController::class, 'dashboard'])->name('dashboard');
    Route::get('/triage', [NurseController::class, 'triage'])->name('triage');
    Route::post('/triage', [NurseController::class, 'storeTriage'])->name('storeTriage');
    Route::get('/signos-vitales', [NurseController::class, 'signosVitales'])->name('signos');
    Route::post('/signos-vitales', [NurseController::class, 'storeSignos'])->name('storeSignos');
    Route::post('/signos-vitales/store', [NurseController::class, 'storeSignos'])->name('storeVitals');
    Route::get('/hospitalizacion', [NurseController::class, 'hospitalizacion'])->name('hospitalizacion');
    Route::post('/hospitalizacion', [NurseController::class, 'storeHospitalization'])->name('storeHospitalization');
    Route::get('/evolucion', [NurseController::class, 'evolucion'])->name('evolucion');
    Route::post('/evolucion', [NurseController::class, 'storeEvolucion'])->name('storeEvolution');
    Route::get('/documentacion', [NurseController::class, 'documentacion'])->name('documentacion');
    Route::get('/pacientes', [NurseController::class, 'pacientes'])->name('pacientes');
    Route::post('/pacientes/{id}/alta', [NurseController::class, 'darAlta'])->name('darAlta');
    Route::match(['PUT', 'POST'], '/pacientes/{id}/reasignar', [NurseController::class, 'reasignarPaciente'])->name('reasignar');
    Route::get('/alertas', [NurseController::class, 'alertas'])->name('alertas');
    Route::post('/alertas/{id}/read', [NurseController::class, 'markAlertRead'])->name('markAlertRead');
    Route::get('/reportes', [NurseController::class, 'reportes'])->name('reportes');
    Route::get('/medicamentos', [NurseController::class, 'medicamentos'])->name('medicamentos');
    Route::get('/solicitudes-farmacia', [NurseController::class, 'solicitudesFarmacia'])->name('solicitudesFarmacia');
    Route::get('/mapa-camas', [NurseController::class, 'mapaCamas'])->name('mapaCamas');
    Route::post('/camas/{id}/asignar', [NurseController::class, 'asignarCama'])->name('asignarCama');
    Route::post('/camas/{id}/liberar', [NurseController::class, 'liberarCama'])->name('liberarCama');
    Route::get('/api/pacientes-camas', [NurseController::class, 'pacientesParaCamas']);
});

// ==========================================
// FARMACIA
// ==========================================
Route::middleware(['auth', 'verified', 'role:Farmacéutico,Admin Farmacia'])->prefix('farmacia')->name('farmacia.')->group(function () {
    // Principal
    Route::get('/dashboard', [PharmacyController::class, 'dashboard'])->name('dashboard');
    Route::get('/inventario', [PharmacyController::class, 'inventory'])->name('inventory');
    Route::post('/inventario', [PharmacyController::class, 'storeMedication'])->name('storeMedication');
    Route::put('/inventario/{id}', [PharmacyController::class, 'updateMedication'])->name('updateMedication');
    Route::get('/controlados', [PharmacyController::class, 'controlled'])->name('controlled');
    Route::get('/enfermera-meds', [PharmacyController::class, 'enfermeraMeds'])->name('enfermeraMeds');

    // Operacion
    Route::get('/dispensacion', [PharmacyController::class, 'dispensacion'])->name('dispensacion');
    Route::post('/dispensacion', [PharmacyController::class, 'dispenseMedication'])->name('dispense');
    Route::get('/crash-carts', [PharmacyController::class, 'crashCarts'])->name('crashCarts');
    Route::post('/crash-carts/{id}/verificar', [PharmacyController::class, 'checkCart'])->name('verificarCart');
    Route::post('/crash-carts/{id}/check', [PharmacyController::class, 'checkCart'])->name('checkCart');
    Route::get('/traspasos', [PharmacyController::class, 'traspasos'])->name('traspasos');
    Route::post('/traspaso', [PharmacyController::class, 'storeTraspaso'])->name('traspaso');

    // Compras
    Route::get('/proveedores', [PharmacyController::class, 'proveedores'])->name('proveedores');
    Route::post('/proveedores', [PharmacyController::class, 'storeProveedor'])->name('storeProveedor');
    Route::get('/ordenes', [PharmacyController::class, 'ordenes'])->name('ordenes');
    Route::get('/ordenes/crear', [PharmacyController::class, 'crearOrden'])->name('crearOrden');
    Route::post('/ordenes', [PharmacyController::class, 'storeOrden'])->name('storeOrden');
    Route::post('/ordenes/{id}/recibir', [PharmacyController::class, 'recibirOrden'])->name('recibirOrden');

    // Analisis
    Route::get('/anomalias', [PharmacyController::class, 'anomalias'])->name('anomalias');
    Route::get('/consumo', [PharmacyController::class, 'consumo'])->name('consumo');
    Route::get('/desabasto', [PharmacyController::class, 'desabasto'])->name('desabasto');
    Route::post('/restock', [PharmacyController::class, 'requestRestock'])->name('requestRestock');
    Route::post('/restock/{id}/approve', [PharmacyController::class, 'approveRestock'])->name('approveRestock');
    Route::get('/alternativas/{id}', [PharmacyController::class, 'getAlternatives'])->name('alternatives');

    // Documentacion
    Route::get('/exportar', [PharmacyController::class, 'exportar'])->name('exportar');
    Route::get('/export/pdf', [PharmacyController::class, 'exportInventoryPDF'])->name('export.pdf');
    Route::get('/export/csv', [PharmacyController::class, 'exportInventoryCSV'])->name('export.csv');
    Route::get('/carga', [PharmacyController::class, 'carga'])->name('carga');
    Route::post('/carga', [PharmacyController::class, 'uploadCSV'])->name('uploadCSV');
    Route::get('/movimientos', [PharmacyController::class, 'movimientos'])->name('movimientos');
    Route::get('/historial-paciente/{id}', [PharmacyController::class, 'pacienteHistorial'])->name('historialPaciente');

    // Rutas legacy que buscan los blades
    Route::get('/alertas-stock', [PharmacyController::class, 'anomalias'])->name('alertasStock');
    Route::get('/vencimientos', [PharmacyController::class, 'desabasto'])->name('vencimientos');
    Route::get('/interacciones', [PharmacyController::class, 'anomalias'])->name('interacciones');
    Route::get('/reportes', [PharmacyController::class, 'consumo'])->name('reportes');
    Route::get('/recetas-pendientes', [PharmacyController::class, 'dispensacion'])->name('recetasPendientes');
});

// ==========================================
// SUPERADMIN
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
    Route::post('/pacientes/{id}/alta', [NurseController::class, 'darAlta'])->name('darAlta');
    Route::get('/urgencias', [SuperAdminController::class, 'urgencias'])->name('urgencias');
    Route::post('/urgencias', [SuperAdminController::class, 'storeTriage'])->name('storeTriage');
    Route::put('/urgencias/{triage}/vitals', [SuperAdminController::class, 'updateVitals'])->name('updateVitals');
    Route::put('/urgencias/{triage}/derive', [SuperAdminController::class, 'derivePatient'])->name('derivePatient');
    Route::get('/urgencias/{triage}/pase-salida', [SuperAdminController::class, 'paseSalida'])->name('paseSalida');
    Route::get('/farmacia', [SuperAdminController::class, 'farmacia'])->name('farmacia');
    Route::post('/farmacia/prescribe', [SuperAdminController::class, 'prescribe'])->name('farmacia.prescribe');
                Route::post('/farmacia', [SuperAdminController::class, 'storeMedication'])->name('storeMedication');
    Route::post('/finanzas/facturas', [SuperAdminController::class, 'storeInvoice'])->name('finanzas.storeInvoice');
    Route::put('/finanzas/facturas/{id}', [SuperAdminController::class, 'updateInvoice'])->name('finanzas.updateInvoice');
    Route::delete('/finanzas/facturas/{id}', [SuperAdminController::class, 'deleteInvoice'])->name('finanzas.deleteInvoice');
    Route::post('/finanzas/seguros', [SuperAdminController::class, 'storeInsurance'])->name('finanzas.storeInsurance');
    Route::put('/finanzas/seguros/{id}', [SuperAdminController::class, 'updateInsurance'])->name('finanzas.updateInsurance');
    Route::delete('/finanzas/seguros/{id}', [SuperAdminController::class, 'deleteInsurance'])->name('finanzas.deleteInsurance');
    Route::get('/finanzas/auth', [SuperAdminController::class, 'finanzasAuth'])->name('finanzas.auth');
    Route::post('/finanzas/verify', [SuperAdminController::class, 'finanzasVerify'])->name('finanzas.verify');
    Route::post('/finanzas/lock', [SuperAdminController::class, 'finanzasLock'])->name('finanzas.lock');
    Route::get('/finanzas', [SuperAdminController::class, 'finanzas'])->name('finanzas');
    Route::put('/finanzas/{invoice}/cancel', [SuperAdminController::class, 'cancelInvoice'])->name('finanzas.cancel');
    Route::get('/finanzas/export/pdf', [SuperAdminController::class, 'exportFinancePDF'])->name('finanzas.export.pdf');
    Route::get('/finanzas/export/csv', [SuperAdminController::class, 'exportFinanceCSV'])->name('finanzas.export.csv');
    Route::get('/auditoria/export/pdf', [SuperAdminController::class, 'exportAuditPDF'])->name('auditoria.export.pdf');
    Route::get('/auditoria/export/csv', [SuperAdminController::class, 'exportAuditCSV'])->name('auditoria.export.csv');
    Route::get('/auditoria/export/json', [SuperAdminController::class, 'exportAuditJSON'])->name('auditoria.export.json');
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
    Route::put('/proveedores/{id}/status', [SuperAdminController::class, 'toggleStatus'])->name('toggleProviderStatus');
    Route::delete('/proveedores/{id}', [SuperAdminController::class, 'deleteUser'])->name('deleteProvider');
    Route::put('/camas/{id}/status', [SuperAdminController::class, 'toggleStatus'])->name('updateBedStatus');
    Route::get('/mapa-calor', [SuperAdminController::class, 'mapaCalor'])->name('mapaCalor');
    Route::get('/camas', [SuperAdminController::class, 'camas'])->name('camas');
    Route::post('/camas', [SuperAdminController::class, 'storeBed'])->name('storeBed');
    Route::post('/proveedores', [SuperAdminController::class, 'storeProvider'])->name('storeProvider');
    Route::get('/reportes', [SuperAdminController::class, 'dashboard'])->name('reportes');
    Route::get('/reportes/camas', [SuperAdminController::class, 'reportCamas'])->name('report.camas');
    Route::get('/reportes/farmacia', [SuperAdminController::class, 'reportFarmacia'])->name('report.farmacia');
    Route::get('/reportes/personal', [SuperAdminController::class, 'reportPersonal'])->name('report.personal');
});

// Profile routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [SuperAdminController::class, 'dashboard'])->name('profile.edit');
    Route::put('/profile', function () { return back(); })->name('profile.update');
    Route::delete('/profile', function () { return redirect('/'); })->name('profile.destroy');
});

require __DIR__.'/auth.php';
