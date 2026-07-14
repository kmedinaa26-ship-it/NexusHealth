@extends('medico.layout')
@section('title', 'Costos de Evento')
@section('nav-costos', 'active')

@section('content')
<h2 style="font-weight:900;color:#9A3412;margin-bottom:1.5rem"><i class="fas fa-receipt" style="color:#F97316"></i> Costos de Evento</h2>

<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(249,115,22,0.08);border-top:4px solid #F97316;margin-bottom:1.5rem;">
    <h3 style="font-weight:900;color:#9A3412;margin-bottom:1rem;"><i class="fas fa-plus-circle" style="color:#F97316"></i> Registrar Costo</h3>
    <form action="{{ route('medico.costos.guardar') }}" method="POST" style="display:grid;grid-template-columns:1fr 2fr 1fr 1fr 1fr auto;gap:1rem;align-items:end;">
        @csrf
        <div>
            <label style="display:block;font-size:0.75rem;font-weight:700;color:#78716C;margin-bottom:0.3rem;">ID Paciente</label>
            <input type="number" name="patient_id" placeholder="Opcional" style="width:100%;padding:0.5rem;border:1px solid #E7E5E4;border-radius:6px;font-size:0.85rem;outline:none;">
        </div>
        <div>
            <label style="display:block;font-size:0.75rem;font-weight:700;color:#78716C;margin-bottom:0.3rem;">Descripcion</label>
            <input type="text" name="descripcion" placeholder="Ej: Suturas absorbibles 3-0" style="width:100%;padding:0.5rem;border:1px solid #E7E5E4;border-radius:6px;font-size:0.85rem;outline:none;" required>
        </div>
        <div>
            <label style="display:block;font-size:0.75rem;font-weight:700;color:#78716C;margin-bottom:0.3rem;">Cantidad</label>
            <input type="number" name="cantidad" value="1" min="1" style="width:100%;padding:0.5rem;border:1px solid #E7E5E4;border-radius:6px;font-size:0.85rem;outline:none;" required>
        </div>
        <div>
            <label style="display:block;font-size:0.75rem;font-weight:700;color:#78716C;margin-bottom:0.3rem;">Costo Unit. ($)</label>
            <input type="number" name="costo_unitario" step="0.01" placeholder="0.00" style="width:100%;padding:0.5rem;border:1px solid #E7E5E4;border-radius:6px;font-size:0.85rem;outline:none;" required>
        </div>
        <div>
            <label style="display:block;font-size:0.75rem;font-weight:700;color:#78716C;margin-bottom:0.3rem;">Tipo</label>
            <select name="tipo" style="width:100%;padding:0.5rem;border:1px solid #E7E5E4;border-radius:6px;font-size:0.85rem;outline:none;background:white;">
                <option value="insumo">Insumo</option>
                <option value="papel">Papel</option>
                <option value="gas_medico">Gas Medico</option>
                <option value="otro">Otro</option>
            </select>
        </div>
        <button type="submit" style="padding:0.5rem 1.2rem;background:linear-gradient(135deg,#F97316,#EA580C);color:white;border:none;border-radius:6px;font-weight:800;font-size:0.85rem;cursor:pointer;"><i class="fas fa-save"></i></button>
    </form>
</div>

<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(249,115,22,0.08);border-top:4px solid #EA580C;">
    <h3 style="font-weight:900;color:#9A3412;margin-bottom:1rem;"><i class="fas fa-list" style="color:#EA580C"></i> Costos Registrados</h3>
    @if($costos->count() > 0)
    <table style="width:100%;border-collapse:collapse;font-size:0.85rem;">
        <thead>
            <tr style="background:#FFF7ED">
                <th style="padding:0.6rem;text-align:left;color:#9A3412;">Paciente</th>
                <th style="padding:0.6rem;color:#9A3412;">Descripcion</th>
                <th style="padding:0.6rem;color:#9A3412;">Tipo</th>
                <th style="padding:0.6rem;color:#9A3412;">Cant.</th>
                <th style="padding:0.6rem;color:#9A3412;">Unit.</th>
                <th style="padding:0.6rem;color:#9A3412;">Total</th>
                <th style="padding:0.6rem;color:#9A3412;">Fecha</th>
            </tr>
        </thead>
        <tbody>
        @foreach($costos as $c)
        <?php $fecha = is_object($c->created_at) ? $c->created_at->format('d/m H:i') : substr($c->created_at, 0, 16); ?>
        <tr style="border-bottom:1px solid #FFF0E0;">
            <td style="padding:0.5rem;font-weight:700;">{{ $c->patient_id ?? 'N/A' }}</td>
            <td style="padding:0.5rem;color:#57534E;">{{ $c->descripcion }}</td>
            <td style="padding:0.5rem;"><span style="background:#FFEDD5;color:#9A3412;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.7rem;font-weight:800;">{{ $c->tipo }}</span></td>
            <td style="padding:0.5rem;color:#57534E;">{{ $c->cantidad }}</td>
            <td style="padding:0.5rem;color:#57534E;">${{ number_format($c->costo_unitario, 2) }}</td>
            <td style="padding:0.5rem;font-weight:800;color:#9A3412;">${{ number_format($c->costo_total, 2) }}</td>
            <td style="padding:0.5rem;color:#A8A29E;font-size:0.75rem;">{{ $fecha }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
    @else
    <p style="text-align:center;color:#D97706;padding:2rem;font-weight:700;">Sin costos registrados</p>
    @endif
</div>
@endsection
