@extends('superadmin.layout')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h4><i class="fas fa-file-invoice-dollar mr-2"></i>Cuenta: {{ $cuenta->folio }}</h4>
        </div>
        <div class="col-md-6 text-right">
            <span class="badge badge-{{ $cuenta->estado === 'pagada' ? 'success' : 'warning' }} p-2">
                {{ strtoupper($cuenta->estado) }}
            </span>
        </div>
    </div>

    <div class="row">
        <!-- Resumen Financiero -->
        <div class="col-md-4">
            <div class="card border-left-primary">
                <div class="card-body">
                    <h6 class="text-muted">Costo Real (Hospital)</h6>
                    <h3 class="text-primary">${{ number_format($cuenta->subtotal_costos, 2) }}</h3>
                    <small class="text-muted">Insumos, papel, honorarios</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-success">
                <div class="card-body">
                    <h6 class="text-muted">Margen de Utilidad</h6>
                    <h3 class="text-success">${{ number_format($cuenta->margen, 2) }}</h3>
                    <small class="text-muted">Diferencia antes de IVA</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-danger">
                <div class="card-body">
                    <h6 class="text-muted">Total a Cobrar (Incl. IVA)</h6>
                    <h3 class="text-danger">${{ number_format($cuenta->total_cobro, 2) }}</h3>
                    <small class="text-muted">Monto final para el paciente</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Detalles -->
    <div class="card mt-4">
        <div class="card-header"><i class="fas fa-list-alt mr-2"></i>Desglose de Conceptos</div>
        <div class="card-body p-0">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th class="text-right">Costo Real</th>
                        <th class="text-right">Precio Cobro</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detalles as $detalle)
                    <tr>
                        <td>{{ $detalle->descripcion }}</td>
                        <td class="text-right text-muted">${{ number_format($detalle->costo_real, 2) }}</td>
                        <td class="text-right font-weight-bold">${{ number_format($detalle->precio_cobro, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="text-center text-muted py-3">No hay detalles registrados aun.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
