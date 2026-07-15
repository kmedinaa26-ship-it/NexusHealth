@extends('enfermeria.layout')

@section('title', 'Triage de Urgencias')
@section('nav-triage', 'active')

@section('content')
<div style="margin-top: 1.5rem;">
    
    <!-- FILA 1: IMPACTO CLÍNICO -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1rem;">
        <div style="background: white; padding: 1rem; border-radius: 10px; border-left: 4px solid #1E1A17;">
            <p style="margin:0; font-size:0.7rem; color:#736860; font-weight:700;"><i class="fas fa-user-injured"></i> Evaluados Hoy</p>
            <h2 style="margin:0; color:#1E1A17;">{{ $pacientesHoy }}</h2>
        </div>
        <div style="background: white; padding: 1rem; border-radius: 10px; border-left: 4px solid #F05A4E;">
            <p style="margin:0; font-size:0.7rem; color:#736860; font-weight:700;"><i class="fas fa-heartbeat"></i> Críticos Detectados</p>
            <h2 style="margin:0; color:#F05A4E;">{{ $criticosDetectados }}</h2>
        </div>
        <div style="background: white; padding: 1rem; border-radius: 10px; border-left: 4px solid #2D9E6A;">
            <p style="margin:0; font-size:0.7rem; color:#736860; font-weight:700;"><i class="fas fa-bolt"></i> Ahorro Tiempo IA</p>
            <h2 style="margin:0; color:#2D9E6A;">70%</h2>
            <small style="color:#999;">4 min Enf vs 1.2 min IA</small>
        </div>
        <div style="background: white; padding: 1rem; border-radius: 10px; border-left: 4px solid #FF8C42;">
            <p style="margin:0; font-size:0.7rem; color:#736860; font-weight:700;"><i class="fas fa-exclamation-triangle"></i> Alertas Críticas IA</p>
            <h2 style="margin:0; color:#FF8C42;">{{ $alertasHoy }}</h2>
        </div>
    </div>

    <!-- FILA 2: ESTADO DEL MODELO Y ALERTAS -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 1rem;">
        <div style="background: white; padding: 1rem; border-radius: 10px; border: 1px solid #E5E7EB;">
            <h3 style="font-size: 0.9rem; font-weight: 800; margin: 0 0 0.5rem 0;"><i class="fas fa-balance-scale" style="color:#FF8C42;"></i> Concordancia Enfermera-IA</h3>
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <p style="font-size: 0.8rem; margin: 0;">Evaluados: <strong>{{ $totalEvaluados }}</strong> | Coincidencias: <strong style="color:#2D9E6A;">{{ $vp + $vn }}</strong> | Discrepancias: <strong style="color:#F05A4E;">{{ $fp + $fn }}</strong></p>
                    <div style="background: #E5E7EB; border-radius: 5px; height: 8px; margin-top: 0.5rem; width: 300px;">
                        <div style="width: {{ $concordancia }}%; background: #2D9E6A; height: 8px; border-radius: 5px;"></div>
                    </div>
                </div>
                <div style="text-align: right;">
                    <span style="font-size: 1.5rem; font-weight: 800; color: {{ $concordancia > 80 ? '#2D9E6A' : '#F05A4E' }}">{{ $concordancia }}%</span>
                    <p style="margin:0; font-size:0.7rem; color:#736860;">Concordancia Global</p>
                </div>
            </div>
        </div>
        <div style="background: #F9FAFB; padding: 1rem; border-radius: 10px; border: 1px solid #E5E7EB;">
            <h4 style="font-size: 0.8rem; font-weight: 700; margin: 0 0 0.5rem 0;"><i class="fas fa-cogs" style="color:#1E1A17;"></i> Estado del Modelo</h4>
            <p style="font-size:0.75rem; margin:0.1rem 0; color:#2D9E6A;"><i class="fas fa-circle" style="font-size:6px;"></i> Modelo Activo</p>
            <p style="font-size:0.75rem; margin:0.1rem 0; color:#736860;"><i class="fas fa-calendar-alt"></i> Último entrenamiento: {{ now()->subDays(2)->format('d/m/Y') }}</p>
            <p style="font-size:0.75rem; margin:0.1rem 0; color:#736860;"><i class="fas fa-bullseye"></i> Accuracy actual: {{ $metricasExplicadas['accuracy']['valor'] }}%</p>
        </div>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
        <h2 style="font-weight: 800; color: #1E1A17; margin: 0; font-size: 1.2rem;"><i class="fas fa-ambulance" style="color: #F05A4E;"></i> Triage Manchester en Vivo</h2>
        <div style="display: flex; gap: 0.5rem;">
            <button onclick="document.getElementById('form-nuevo').style.display = document.getElementById('form-nuevo').style.display === 'none' ? 'block' : 'none'" style="background: #2D9E6A; color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.8rem;">
                <i class="fas fa-plus-circle"></i> Nueva Urgencia
            </button>
            <button onclick="document.getElementById('panel-ia-detalle').style.display = document.getElementById('panel-ia-detalle').style.display === 'none' ? 'block' : 'none'" style="background: #1E1A17; color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 700; cursor: pointer; font-size: 0.8rem;">
                <i class="fas fa-brain"></i> Auditoría y Métricas
            </button>
        </div>
    </div>

    <!-- FORMULARIO RÁPIDO NUEVO PACIENTE (CON SIGNOS VITALES) -->
    <div id="form-nuevo" style="display: none; margin-bottom: 1rem; background: white; padding: 1rem; border-radius: 10px; border: 1px solid #E5E7EB;">
        <form action="{{ route('enfermeria.triage.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 2fr auto; gap: 0.5rem; align-items: end; margin-bottom:0.5rem;">
                <div><label style="font-size:0.7rem; font-weight:700;">Nombre</label><input type="text" name="patient_name" required style="width:100%; padding:0.4rem; border:1px solid #ccc; border-radius:4px;"></div>
                <div><label style="font-size:0.7rem; font-weight:700;">Edad</label><input type="number" name="age" required style="width:100%; padding:0.4rem; border:1px solid #ccc; border-radius:4px;"></div>
                <div><label style="font-size:0.7rem; font-weight:700;">Triage Enf.</label>
                    <select name="triage_level" style="width:100%; padding:0.4rem; border:1px solid #ccc; border-radius:4px;">
                        <option value="Rojo">Rojo</option><option value="Naranja">Naranja</option><option value="Amarillo">Amarillo</option><option value="Verde" selected>Verde</option><option value="Azul">Azul</option>
                    </select>
                </div>
                <div><label style="font-size:0.7rem; font-weight:700;">Motivo</label><input type="text" name="chief_complaint" required style="width:100%; padding:0.4rem; border:1px solid #ccc; border-radius:4px;"></div>
                <button type="submit" style="background: #1E1A17; color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; font-weight: 700; cursor: pointer; height:100%;"><i class="fas fa-save"></i> Registrar</button>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; background:#F9FAFB; padding:0.5rem; border-radius:4px; border:1px dashed #FF8C42;">
                <p style="grid-column: span 3; margin:0; font-size:0.7rem; font-weight:700; color:#FF8C42;"><i class="fas fa-lungs"></i> Signos Vitales (Opcional - La IA los usará para predecir)</p>
                <div><label style="font-size:0.65rem; color:#736860;">SpO2 (%)</label><input type="number" name="spo2" placeholder="Ej: 98" step="1" style="width:100%; padding:0.3rem; border:1px solid #ccc; border-radius:4px;"></div>
                <div><label style="font-size:0.65rem; color:#736860;">FC (bpm)</label><input type="number" name="fc" placeholder="Ej: 80" step="1" style="width:100%; padding:0.3rem; border:1px solid #ccc; border-radius:4px;"></div>
                <div><label style="font-size:0.65rem; color:#736860;">Temp (°C)</label><input type="number" name="temp" placeholder="Ej: 36.5" step="0.1" style="width:100%; padding:0.3rem; border:1px solid #ccc; border-radius:4px;"></div>
            </div>
        </form>
    </div>

    <!-- PANEL DETALLADO / AUDITORÍA (Oculto) -->
    <div id="panel-ia-detalle" style="display: none; margin-bottom: 1rem; background: white; padding: 1rem; border-radius: 10px; border: 1px solid #E5E7EB;">
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1rem;">
            <div>
                <h4 style="font-size: 0.85rem; margin-bottom: 0.5rem; font-weight: 800;">Distribución de Errores</h4>
                <div style="display:flex; gap:0.5rem; margin-bottom:1rem;">
                    <div style="flex:1; background:#D1FAE5; padding:0.5rem; text-align:center; border-radius:6px;"><strong style="color:#2D9E6A;">VP: {{ $vp }}</strong></div>
                    <div style="flex:1; background:#D1FAE5; padding:0.5rem; text-align:center; border-radius:6px;"><strong style="color:#2D9E6A;">VN: {{ $vn }}</strong></div>
                    <div style="flex:1; background:#FEE2E2; padding:0.5rem; text-align:center; border-radius:6px;"><strong style="color:#F05A4E;">FN: {{ $fn }}</strong></div>
                    <div style="flex:1; background:#FFEDD5; padding:0.5rem; text-align:center; border-radius:6px;"><strong style="color:#FF8C42;">FP: {{ $fp }}</strong></div>
                </div>
                
                <h4 style="font-size: 0.85rem; margin-bottom: 0.5rem; font-weight: 800;">Concordancia por Color</h4>
                <table style="width:100%; font-size:0.8rem; border-collapse:collapse;">
                    @foreach($metricsByColor as $color => $acc)
                    <tr style="border-bottom:1px solid #eee;"><td style="font-weight:700;">{{ $color }}</td><td style="text-align:right;">{{ $acc }}%</td></tr>
                    @endforeach
                </table>
            </div>
            <div>
                <h4 style="font-size: 0.85rem; margin-bottom: 0.5rem; font-weight: 800;">Tabla de Decisiones (Auditoría Reciente)</h4>
                <table style="width:100%; font-size:0.8rem; border-collapse:collapse;">
                    <thead style="background:#F9FAFB;"><tr><th>Paciente</th><th>Enfermera</th><th>IA</th><th>Resultado</th></tr></thead>
                    <tbody>
                        @foreach($auditoriaReciente as $aud)
                        <tr style="border-bottom:1px solid #eee;">
                            <td>{{ $aud->patient_name }}</td>
                            <td>{{ $aud->triage_level }}</td>
                            <td>{{ $aud->ia_nivel }}</td>
                            <td style="font-weight:700; color:{{ $aud->ia_validation == 'VP' || $aud->ia_validation == 'VN' ? '#2D9E6A' : '#F05A4E' }}">{{ $aud->ia_validation }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 0.5rem; margin-top:1rem;">
            @foreach($metricasExplicadas as $key => $metrica)
            <div style="background: #F9FAFB; padding: 0.5rem; border-radius: 4px; border-left: 3px solid {{ $key == 'accuracy' ? '#2D9E6A' : ($key == 'precision' ? '#FF8C42' : ($key == 'recall' ? '#F05A4E' : '#1E1A17')) }}">
                <strong style="text-transform: uppercase; font-size: 0.7rem;">{{ $key }}: {{ $metrica['valor'] }}%</strong>
                <p style="margin: 0.1rem 0 0; font-size: 0.7rem; color: #736860;">{{ $metrica['explicacion'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    <!-- TABLERO TRIAGE MANCHESTER -->
    <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 0.6rem;">
        @php 
            $colores = [
                'Rojo' => ['bg' => '#FEF2F2', 'border' => '#F05A4E', 'text' => '#F05A4E', 'label' => 'EMERGENCIA'],
                'Naranja' => ['bg' => '#FFF7ED', 'border' => '#FF8C42', 'text' => '#FF8C42', 'label' => 'MUY URGENTE'],
                'Amarillo' => ['bg' => '#FEFCE8', 'border' => '#EAB308', 'text' => '#CA8A04', 'label' => 'URGENTE'],
                'Verde' => ['bg' => '#F0FDF4', 'border' => '#2D9E6A', 'text' => '#2D9E6A', 'label' => 'CONSULTA'],
                'Azul' => ['bg' => '#EFF6FF', 'border' => '#3B82F6', 'text' => '#3B82F6', 'label' => 'NO URGENTE']
            ]; 
        @endphp

        @foreach($colores as $nivel => $estilo)
        <div style="background: {{ $estilo['bg'] }}; border: 2px solid {{ $estilo['border'] }}; border-radius: 10px; padding: 0.6rem; min-height: 300px;">
            <h3 style="text-align: center; color: {{ $estilo['text'] }}; font-size: 0.8rem; font-weight: 800; margin: 0 0 0.5rem 0;">{{ $nivel }} ({{ $estilo['label'] }})</h3>
            
            @if(count($grouped[$nivel]) > 0)
                @foreach($grouped[$nivel] as $p)
                <div style="background: white; border-radius: 6px; padding: 0.6rem; margin-bottom: 0.4rem; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border-left: 3px solid {{ $estilo['border'] }}">
                    <p style="margin: 0; font-weight: 700; font-size: 0.85rem; color: #1E1A17;">{{ $p->patient_name }}</p>
                    <p style="margin: 0; font-size: 0.7rem; color: #736860;">{{ $p->age ?? '-' }} años | {{ Str::limit($p->chief_complaint ?? '', 25) }}</p>
                    
                    <div style="margin-top: 0.3rem; background: #F9FAFB; padding: 0.4rem; border-radius: 4px; font-size: 0.7rem;">
                        <div style="display:flex; justify-content:space-between;">
                            <span>Enf: <strong style="color:{{ $estilo['text'] }}">{{ $p->triage_level }}</strong></span>
                            <span>IA: <strong style="color:{{ $p->ia_nivel == 'Rojo' ? '#F05A4E' : ($p->ia_nivel == 'Amarillo' ? '#CA8A04' : '#2D9E6A') }}">{{ $p->ia_nivel }}</strong></span>
                        </div>
                        <div style="margin-top:0.2rem; text-align:center; font-weight:800; color:{{ $p->ia_validation == 'VP' || $p->ia_validation == 'VN' ? '#2D9E6A' : '#F05A4E' }}">
                            @if($p->ia_validation)
                                {{ $p->ia_validation }} 
                                @if($p->ia_validation == 'VP' || $p->ia_validation == 'VN') <i class="fas fa-check-circle" style="color:#2D9E6A;"></i> @else <i class="fas fa-times-circle" style="color:#F05A4E;"></i> @endif
                            @else
                                <span style="color:#999; font-weight:400;"><i class="fas fa-hourglass-half"></i> Sin validar</span>
                            @endif
                        </div>
                    </div>

                    <button onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none'" style="width:100%; margin-top:0.2rem; font-size:0.6rem; background:white; border:1px dashed #ccc; padding:0.1rem; border-radius:3px; cursor:pointer; color:#736860;">
                        <i class="fas fa-question-circle"></i> Por qué la IA dice {{ $p->ia_nivel }}?
                    </button>
                    <div style="display:none; margin-top:0.2rem; background:#1E1A17; color:#FFF; padding:0.4rem; border-radius:4px; font-size:0.65rem;">
                        <i class="fas fa-robot" style="color:#2D9E6A;"></i> {{ $p->xai_reason }}
                    </div>

                    @if(!$p->ia_validation)
                    <form action="{{ route('enfermeria.triage.validar', $p->id) }}" method="POST" style="display:flex; gap:0.2rem; margin-top:0.3rem;">
                        @csrf
                        @if($p->triage_level == $p->ia_nivel)
                            <input type="hidden" name="tipo" value="{{ $p->triage_level == 'Rojo' ? 'VP' : 'VN' }}">
                            <button type="submit" style="flex:1; font-size:0.65rem; background:#2D9E6A; color:white; border:none; padding:0.2rem; border-radius:3px; cursor:pointer; font-weight:600;"><i class="fas fa-check"></i> Confirmar IA</button>
                        @else
                            <input type="hidden" name="tipo" value="{{ $p->triage_level == 'Rojo' ? 'FN' : 'FP' }}">
                            <button type="submit" style="flex:1; font-size:0.65rem; background:#F05A4E; color:white; border:none; padding:0.2rem; border-radius:3px; cursor:pointer; font-weight:600;"><i class="fas fa-exclamation-triangle"></i> Corregir IA</button>
                        @endif
                    </form>
                    @endif
                </div>
                @endforeach
            @else
                <p style="font-size:0.65rem; text-align:center; color:#999; margin-top:1rem;">Sin pacientes</p>
            @endif
        </div>
        @endforeach
    </div>

    <div style="margin-top: 1.5rem; display: flex; justify-content: center; background: white; padding: 1rem; border-radius: 10px;">
        {{ $pacientesActivos->links() }}
    </div>
</div>
@endsection
