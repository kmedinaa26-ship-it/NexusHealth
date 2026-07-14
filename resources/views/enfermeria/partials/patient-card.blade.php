<div style="background: white; border-radius: 8px; padding: 0.6rem; margin-bottom: 0.5rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border-left: 3px solid {{ $color }}">
    <p style="margin: 0; font-weight: 700; font-size: 0.8rem; color: #1E1A17;">{{ $p->patient_name }}</p>
    <p style="margin: 0; font-size: 0.7rem; color: #736860;">{{ $p->age ?? '-' }} años | {{ $p->chief_complaint ?? 'Sin síntoma' }}</p>
    
    <div style="margin-top: 0.3rem; display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.65rem; color: {{ $p->ai_match ? '#2D9E6A' : '#F05A4E' }}; font-weight: 700;">
            <i class="fas fa-robot"></i> IA: {{ $p->ai_nivel }} 
            @if(!$p->ai_match)<i class="fas fa-exclamation-triangle" style="font-size:0.6rem;"></i>@endif
        </span>
    </div>

    <div style="display: flex; gap: 0.3rem; margin-top: 0.4rem;">
        <button onclick="alert('Registrar signos para: {{ $p->patient_name }}')" style="flex:1; font-size: 0.65rem; background: #F9FAFB; border: 1px solid #E5E7EB; padding: 0.2rem; border-radius: 4px; cursor: pointer; font-weight: 600;">Signos</button>
        <button onclick="alert('Derivar a consulta: {{ $p->patient_name }}')" style="flex:1; font-size: 0.65rem; background: #1E1A17; color: white; border: none; padding: 0.2rem; border-radius: 4px; cursor: pointer; font-weight: 600;">Derivar</button>
    </div>
</div>
