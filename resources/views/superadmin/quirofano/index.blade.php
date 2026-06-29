@extends('superadmin.layout')

@section('title', 'Carga de Quirófano')
@section('nav-quirofano', 'active')

@section('content')
<div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04); margin-top: 1.5rem;">
    <h3 style="font-weight: 800; margin-bottom: 1.5rem; color: #1E1A17;">
        <i class="fas fa-user-md" style="color: #F05A4E; margin-right: 0.5rem;"></i> Carga de Cirugías e Insumos
    </h3>

    @if(session('success'))
        <div style="background: #D1FAE5; color: #2D9E6A; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; font-weight: 600;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('quirofano.cargar') }}" method="POST">
        @csrf
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
            <div>
                <label style="font-size: 0.8rem; color: #736860; font-weight: 700; text-transform: uppercase;">Paciente</label>
                <select name="patient_id" style="width: 100%; padding: 0.8rem; border: 1px solid #E5E7EB; border-radius: 8px; margin-top: 0.3rem;" required>
                    @foreach($pacientes as $p)
                    <option value="{{ $p->id }}">{{ $p->patient_name }} (ID: {{ $p->id }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size: 0.8rem; color: #736860; font-weight: 700; text-transform: uppercase;">Tipo de Cirugía</label>
                <select name="cirugia" style="width: 100%; padding: 0.8rem; border: 1px solid #E5E7EB; border-radius: 8px; margin-top: 0.3rem;" required>
                    <option value="Colecistectomía">Colecistectomía (Vesícula)</option>
                    <option value="Apendicectomía">Apendicectomía (Apéndice)</option>
                    <option value="Cesárea">Cesárea</option>
                    <option value="Hernioplastia">Hernioplastia (Hernia)</option>
                </select>
            </div>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="font-size: 0.8rem; color: #736860; font-weight: 700; text-transform: uppercase;">Horas de Quirófano (Ej: 2.5)</label>
            <input type="number" step="0.1" name="horas_or" min="0.5" value="2" style="width: 100%; padding: 0.8rem; border: 1px solid #E5E7EB; border-radius: 8px; margin-top: 0.3rem;" required>
            <small style="color: #736860;">* Se cobrarán $5,000 MXN por hora automáticamente.</small>
        </div>

        <div style="border-top: 2px dashed #E5E7EB; padding-top: 1.5rem;">
            <h4 style="font-weight: 700; margin-bottom: 1rem; color: #1E1A17;"><i class="fas fa-syringe"></i> Insumos Utilizados (Variables)</h4>
            <div id="insumos-container" style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                <div class="insumo-row" style="display: grid; grid-template-columns: 3fr 1fr auto; gap: 1rem; align-items: center;">
                    <select name="insumos[0][id]" class="insumo-select" style="padding: 0.8rem; border: 1px solid #E5E7EB; border-radius: 8px;">
                        <option value="">Selecciona un insumo...</option>
                        @foreach($insumos as $med)
                        <option value="{{ $med->id }}">{{ $med->name }} (Stock: {{ $med->stock }})</option>
                        @endforeach
                    </select>
                    <input type="number" name="insumos[0][quantity]" placeholder="Cant." min="1" style="padding: 0.8rem; border: 1px solid #E5E7EB; border-radius: 8px;">
                    <button type="button" onclick="addInsumo()" style="background: #1E1A17; color: white; border: none; padding: 0.8rem 1.2rem; border-radius: 8px; cursor: pointer; font-weight: 700;"><i class="fas fa-plus"></i></button>
                </div>
            </div>
        </div>

        <div style="margin-top: 2rem; text-align: right;">
            <button type="submit" style="background: #2D9E6A; color: white; padding: 1rem 2rem; border-radius: 8px; font-weight: 700; border: none; cursor: pointer;">
                <i class="fas fa-file-invoice-dollar"></i> Cargar a Cuenta del Paciente
            </button>
        </div>
    </form>
</div>

<script>
    let insumoIndex = 0;
    function addInsumo() {
        insumoIndex++;
        const container = document.getElementById('insumos-container');
        const firstRow = container.querySelector('.insumo-row');
        const newRow = firstRow.cloneNode(true);
        
        newRow.querySelector('select').name = `insumos[${insumoIndex}][id]`;
        newRow.querySelector('select').value = "";
        newRow.querySelector('input').name = `insumos[${insumoIndex}][quantity]`;
        newRow.querySelector('input').value = "";
        
        // Change the plus button to a minus button
        const btn = newRow.querySelector('button');
        btn.innerHTML = '<i class="fas fa-trash"></i>';
        btn.style.background = '#C7291C';
        btn.onclick = () => newRow.remove();
        
        container.appendChild(newRow);
    }
</script>
@endsection
