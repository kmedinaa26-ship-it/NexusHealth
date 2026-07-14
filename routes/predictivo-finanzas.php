<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Medico\PredictivoController;
use App\Http\Controllers\Medico\CostosController;
use App\Http\Controllers\Superadmin\Ml\ModeloController;
use App\Http\Controllers\Superadmin\Ml\DatasetController;
use App\Http\Controllers\Superadmin\Ml\EvaluacionController;
use App\Http\Controllers\Superadmin\Ml\ExplicacionController;
use App\Http\Controllers\Superadmin\Ml\DriftController;
use App\Http\Controllers\Superadmin\Ml\RetrainController;
use App\Http\Controllers\Superadmin\Finanzas\CostosController as FinCostosController;
use App\Http\Controllers\Superadmin\Finanzas\CobrosController;
use App\Http\Controllers\Superadmin\Finanzas\UtilidadController;
use App\Http\Controllers\Superadmin\Finanzas\IncobrablesController;
use App\Http\Controllers\Superadmin\AlertasMlController;

// MEDICO: Predictivo
Route::middleware(['auth', 'verified'])->prefix('medico/predictivo')->name('medico.predictivo.')->group(function () {
    Route::get('/simular', [PredictivoController::class, 'simular'])->name('simular');
    Route::get('/', [PredictivoController::class, 'index'])->name('index');
    Route::post('/crear', [PredictivoController::class, 'crear'])->name('crear');
    Route::get('/resultados', [PredictivoController::class, 'resultados'])->name('resultados');
    Route::post('/resultados/guardar', [PredictivoController::class, 'guardarResultado'])->name('guardarResultado');
    Route::get('/graficas', [PredictivoController::class, 'graficas'])->name('graficas');
});

// MEDICO: Costos
Route::middleware(['auth', 'verified'])->prefix('medico/costos')->name('medico.costos.')->group(function () {
    Route::get('/', [CostosController::class, 'index'])->name('index');
    Route::post('/guardar', [CostosController::class, 'guardar'])->name('guardar');
});

// SUPERADMIN: ML
Route::middleware(['auth', 'verified'])->prefix('superadmin/ml')->name('superadmin.ml.')->group(function () {
    Route::get('/modelos', [ModeloController::class, 'index'])->name('modelos');
    Route::get('/dataset', [DatasetController::class, 'index'])->name('dataset');
    Route::post('/dataset/toggle', [DatasetController::class, 'toggleAprobado'])->name('dataset.toggle');
    Route::get('/dataset/csv', [DatasetController::class, 'exportarCSV'])->name('dataset.csv');
    Route::get('/evaluar', [EvaluacionController::class, 'index'])->name('evaluar');
    Route::get('/explicacion', [ExplicacionController::class, 'index'])->name('explicacion');
    Route::get('/explicacion/{id}', [ExplicacionController::class, 'show'])->name('explicacion.show');
    Route::get('/drift', [DriftController::class, 'index'])->name('drift');
    Route::get('/retrain', [RetrainController::class, 'index'])->name('retrain');
    Route::post('/retrain', [RetrainController::class, 'execute'])->name('retrain.execute');
});

// SUPERADMIN: Finanzas
Route::middleware(['auth', 'verified'])->prefix('superadmin/finanzas')->name('superadmin.finanzas.')->group(function () {
    Route::get('/costos', [FinCostosController::class, 'index'])->name('costos');
    Route::get('/cobros', [CobrosController::class, 'index'])->name('cobros');
    Route::get('/utilidad', [UtilidadController::class, 'index'])->name('utilidad');
    Route::get('/incobrables', [IncobrablesController::class, 'index'])->name('incobrables');
});

// SUPERADMIN: Alertas ML
Route::middleware(['auth', 'verified'])->get('/superadmin/alertas-ml', [AlertasMlController::class, 'index'])->name('superadmin.alertas-ml');
