@extends('superadmin.layout')

@section('title', 'IA Médica - Motor Predictivo')
@section('nav-ml', 'active')

@section('content')
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">

    <!-- COLUMNA IZQUIERDA: LISTADO DINÁMICO Y PAGINADO -->
    <div>
        <div style="background: white; padding: 1.5rem; border-radius: 12px; margin-bottom: 1.5rem;">
            <h3 style="font-weight: 800; color: #1E1A17; margin-bottom: 1rem;">
                <i class="fas fa-brain" style="color: #F05A4E;"></i> Motor Predictivo en Tiempo Real
            </h3>
            
            <!-- FILTROS RÁPIDOS -->
            <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
                <button onclick="filterRisk('Critico')" style="background: #FEF2F2; border: 1px solid #F05A4E; color: #F05A4E; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 700; font-size: 0.8rem; cursor: pointer;">Crítico</button>
                <button onclick="filterRisk('Moderado')" style="background: #FFF7ED; border: 1px solid #FF8C42; color: #FF8C42; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 700; font-size: 0.8rem; cursor: pointer;">Moderado</button>
                <button onclick="filterRisk('Estable')" style="background: #F0FDF4; border: 1px solid #2D9E6A; color: #2D9E6A; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 700; font-size: 0.8rem; cursor: pointer;">Estable</button>
                <button onclick="filterRisk('all')" style="background: #F9FAFB; border: 1px solid #E5E7EB; color: #1E1A17; padding: 0.5rem 1rem; border-radius: 20px; font-weight: 700; font-size: 0.8rem; cursor: pointer;"><i class="fas fa-list"></i> Todos</button>
            </div>

            <!-- LISTA DE PACIENTES ITERADA -->
            @foreach($pacientesAnalizados as $p)
                @php
                    // Determinar color y riesgo basado en el Arbol de Decisión
                    $riesgo = $p['arbol']['clase'] ?? 'Estable';
                    $ruta = $p['arbol']['ruta'] ?? 'Sin evaluación';
                    
                    if($riesgo == 'Crítico' || $riesgo == 'UCI') { $color = '#F05A4E'; $riskLabel = 'Critico'; }
                    elseif($riesgo == 'Moderado' || $riesgo == 'Observación') { $color = '#FF8C42'; $riskLabel = 'Moderado'; }
                    else { $color = '#2D9E6A'; $riskLabel = 'Estable'; }

                    // Color de barra UCI
                    if($p['probUCI'] > 60) $barColor = '#F05A4E';
                    elseif($p['probUCI'] > 30) $barColor = '#FF8C42';
                    else $barColor = '#2D9E6A';
                @endphp

                <div data-risk="{{ $riskLabel }}" class="patient-card" style="background: white; padding: 1.2rem; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.03); margin-bottom: 1rem; border-left: 5px solid {{ $color }};">
                    
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h4 style="font-weight: 700; color: #1E1A17; margin: 0; font-size: 0.95rem;">{{ $p['nombre'] }}</h4>
                            <small style="color: #736860;">{{ $p['edad'] }} años | FC: {{ $p['fc'] }} bpm | SpO2: {{ $p['spo2'] }}% | Temp: {{ $p['temp'] }}°C</small>
                        </div>
                        <span style="background: {{ $color }}22; color: {{ $color }}; padding: 0.3rem 0.7rem; border-radius: 15px; font-weight: 700; font-size: 0.75rem;">{{ $riesgo }}</span>
                    </div>

                    <!-- BARRA DE PROBABILIDAD UCI -->
                    <div style="margin-top: 0.8rem;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 0.2rem;">
                            <span style="font-weight: 600; color: #736860;">Riesgo UCI (Sigmoide)</span>
                            <span style="font-weight: 800; color: {{ $barColor }};">{{ $p['probUCI'] }}%</span>
                        </div>
                        <div style="width: 100%; background: #E5E7EB; border-radius: 10px; height: 6px;">
                            <div style="width: {{ min(100, $p['probUCI']) }}%; background: {{ $barColor }}; height: 6px; border-radius: 10px;"></div>
                        </div>
                    </div>

                    <!-- ÁRBOL DE DECISIÓN COLAPSABLE -->
                    <div style="margin-top: 0.8rem;">
                        <button onclick="toggleTree(this)" style="font-size: 0.75rem; color: #1E1A17; font-weight: 600; background: none; border: none; cursor: pointer; padding: 0;">
                            <i class="fas fa-project-diagram" style="color: #FF8C42;"></i> Ver Ruta de Decisión
                        </button>
                        <div class="tree-path" style="display: none; margin-top: 0.5rem; background: #F9FAFB; padding: 0.8rem; border-radius: 6px; font-family: monospace; font-size: 0.7rem; color: #736860; border: 1px dashed #E5E7EB;">
                            {!! str_replace('->', '<br>->', $ruta) !!}
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- PAGINACIÓN -->
            <div style="margin-top: 1rem; display: flex; justify-content: center;">
                {{ $triageRecords->links() }}
            </div>
        </div>
    </div>

    <!-- COLUMNA DERECHA: TEORÍA IA Y MÉTRICAS HISTÓRICAS -->
    <div style="height: fit-content; position: sticky; top: 1.5rem;">
        
        <!-- TEORÍA IA -->
        <div style="background: #1E1A17; padding: 1.5rem; border-radius: 12px; color: white; margin-bottom: 1rem;">
            <h3 style="font-weight: 800; margin-bottom: 1rem; color: #FF8C42;">Regresión Logística</h3>
            <p style="font-size: 0.8rem; line-height: 1.4; opacity: 0.9;">
                Calcula la probabilidad de ingresar a UCI usando la función Sigmoide:
            </p>
            <div style="background: rgba(255,255,255,0.1); padding: 0.8rem; border-radius: 8px; margin-top: 0.5rem; font-family: monospace; text-align: center; font-size: 0.8rem;">
                P(UCI) = 1 / (1 + e<sup>-z</sup>)<br>
                <small style="opacity:0.7;">Donde z = β₀ + β₁(Edad) - β₂(SpO2) + β₃(FC)</small>
            </div>
        </div>

        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.04); margin-bottom: 1rem;">
            <h3 style="font-weight: 800; margin-bottom: 1rem; color: #1E1A17; font-size: 0.95rem;">Árbol de Decisión</h3>
            <p style="font-size: 0.8rem; color: #736860; line-height: 1.4;">Estructura lógica de clasificación:</p>
            <ul style="font-size: 0.8rem; color: #1E1A17; padding-left: 1.2rem; margin-top: 0.5rem;">
                <li><strong style="color: #F05A4E;">Raíz:</strong> SpO2 (Oxigenación)</li>
                <li><strong style="color: #FF8C42;">Ramas:</strong> FC y Temperatura</li>
                <li><strong style="color: #2D9E6A;">Hojas:</strong> Clasificación final de riesgo</li>
            </ul>
        </div>

        <!-- MATRIZ DE CONFUSIÓN HISTÓRICA -->
        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
            <h3 style="font-weight: 800; margin-bottom: 1rem; color: #1E1A17; font-size: 0.95rem;">Matriz de Confusión (Histórico)</h3>
            <table style="width: 100%; border-collapse: collapse; font-size: 0.8rem; text-align: center;">
                <tr style="background: #F9FAFB;">
                    <td></td>
                    <td style="padding: 0.5rem; font-weight: 700;">Pred: UCI</td>
                    <td style="padding: 0.5rem; font-weight: 700;">Pred: Estable</td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem; font-weight: 700; background: #F9FAFB;">Real: UCI</td>
                    <td style="padding: 0.5rem; background: #F0FDF4; color: #2D9E6A; font-weight: 700;">VP: {{ $matriz['vp'] ?? 0 }}</td>
                    <td style="padding: 0.5rem; background: #FEF2F2; color: #F05A4E; font-weight: 700;">FN: {{ $matriz['fn'] ?? 0 }}</td>
                </tr>
                <tr>
                    <td style="padding: 0.5rem; font-weight: 700; background: #F9FAFB;">Real: Estable</td>
                    <td style="padding: 0.5rem; background: #FFF7ED; color: #FF8C42; font-weight: 700;">FP: {{ $matriz['fp'] ?? 0 }}</td>
                    <td style="padding: 0.5rem; background: #F0FDF4; color: #2D9E6A; font-weight: 700;">VN: {{ $matriz['vn'] ?? 0 }}</td>
                </tr>
            </table>
            <div style="margin-top: 0.8rem; font-size: 0.75rem; color: #736860;">
                <p><strong>Accuracy:</strong> {{ number_format($metrics['accuracy'] * 100, 1) }}% | <strong>F1-Score:</strong> {{ number_format($metrics['f1'] * 100, 1) }}%</p>
            </div>
        </div>
    </div>
</div>

<script>
    // Filtrar por nivel de riesgo sin recargar
    function filterRisk(type) {
        const cards = document.querySelectorAll('.patient-card');
        cards.forEach(card => {
            if(type === 'all' || card.dataset.risk === type) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Acordeón del Árbol de Decisión
    function toggleTree(button) {
        const treeDiv = button.nextElementSibling;
        treeDiv.style.display = treeDiv.style.display === 'none' ? 'block' : 'none';
    }
</script>
@endsection
