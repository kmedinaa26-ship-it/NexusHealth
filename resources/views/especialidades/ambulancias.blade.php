@extends('especialidades.layout')

@section('content')
<div style="padding:1.5rem">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
        <h2 style="font-weight:900;color:#9A3412"><i class="fas fa-truck-medical" style="color:#EA580C"></i> Ambulancias y Traslados</h2>
        <div style="display:flex;gap:0.8rem">
            <a href="{{ url('/medico/hospital-live') }}" style="padding:0.4rem 1rem;background:linear-gradient(135deg,#EA580C,#DC2626);color:white;border-radius:8px;text-decoration:none;font-weight:800;font-size:0.8rem"><i class="fas fa-tower-broadcast"></i> Hospital Live</a>
        </div>
    </div>

    @if(session('success'))
    <div style="background:#DCFCE7;color:#16A34A;padding:0.8rem;border-radius:8px;margin-bottom:1rem;font-weight:700">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <!-- STATS -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem">
        <div style="background:linear-gradient(135deg,#EA580C,#C2410C);border-radius:14px;padding:1.2rem;color:white;text-align:center">
            <div style="font-size:2rem;font-weight:900">{{ $disponibles }}</div>
            <div style="font-size:0.7rem;font-weight:800;opacity:0.9">Disponibles</div>
        </div>
        <div style="background:linear-gradient(135deg,#F97316,#EA580C);border-radius:14px;padding:1.2rem;color:white;text-align:center">
            <div style="font-size:2rem;font-weight:900">{{ $activas }}</div>
            <div style="font-size:0.7rem;font-weight:800;opacity:0.9">En Ruta</div>
        </div>
        <div style="background:linear-gradient(135deg,#DC2626,#B91C1C);border-radius:14px;padding:1.2rem;color:white;text-align:center">
            <div style="font-size:2rem;font-weight:900">{{ $criticosEnRuta }}</div>
            <div style="font-size:0.7rem;font-weight:800;opacity:0.9">Criticos En Ruta</div>
        </div>
        <div style="background:linear-gradient(135deg,#DC2626,#991B1B);border-radius:14px;padding:1.2rem;color:white;text-align:center">
            <div style="font-size:2rem;font-weight:900">{{ $pacientesCriticos->count() }}</div>
            <div style="font-size:0.7rem;font-weight:800;opacity:0.9">Pacientes Criticos</div>
        </div>
    </div>

    <!-- DESPACHAR AMBULANCIA -->
    <div style="background:white;border-radius:16px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.05);margin-bottom:1.5rem;border-top:4px solid #EA580C">
        <h3 style="font-weight:900;color:#9A3412;margin-bottom:1rem"><i class="fas fa-paper-plane" style="color:#EA580C"></i> Despachar Ambulancia</h3>
        <form method="POST" action="{{ url('/medico/ambulancias/despachar') }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:1rem;margin-bottom:1rem">
                <div>
                    <label style="font-weight:800;color:#9A3412;font-size:0.8rem;display:block;margin-bottom:0.3rem">Ambulancia</label>
                    <select name="ambulance_id" required style="width:100%;padding:0.5rem;border:2px solid #FDBA74;border-radius:8px;font-size:0.85rem">
                        <option value="">Seleccionar...</option>
                        @foreach($ambulancias->where('status','Disponible') as $a)
                        <option value="{{ $a->id }}">{{ $a->code }} - {{ $a->type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-weight:800;color:#9A3412;font-size:0.8rem;display:block;margin-bottom:0.3rem">Origen</label>
                    <input type="text" name="origin" required placeholder="Ubicacion origen" style="width:100%;padding:0.5rem;border:2px solid #FDBA74;border-radius:8px;font-size:0.85rem">
                </div>
                <div>
                    <label style="font-weight:800;color:#9A3412;font-size:0.8rem;display:block;margin-bottom:0.3rem">Destino</label>
                    <input type="text" name="destination" required placeholder="Hospital destino" style="width:100%;padding:0.5rem;border:2px solid #FDBA74;border-radius:8px;font-size:0.85rem">
                </div>
                <div>
                    <label style="font-weight:800;color:#9A3412;font-size:0.8rem;display:block;margin-bottom:0.3rem">Prioridad</label>
                    <select name="priority" required style="width:100%;padding:0.5rem;border:2px solid #FDBA74;border-radius:8px;font-size:0.85rem">
                        <option value="Normal">Normal</option>
                        <option value="Urgente">Urgente</option>
                        <option value="Critica">Critica</option>
                    </select>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:1rem">
                <div>
                    <label style="font-weight:800;color:#9A3412;font-size:0.8rem;display:block;margin-bottom:0.3rem">Paciente (opcional)</label>
                    <select name="patient_id" style="width:100%;padding:0.5rem;border:2px solid #FDBA74;border-radius:8px;font-size:0.85rem">
                        <option value="">Sin paciente asignado</option>
                        @foreach($pacientesCriticos as $p)
                        <option value="{{ $p->id }}">{{ $p->patient_name }} - {{ $p->triage_level }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-weight:800;color:#9A3412;font-size:0.8rem;display:block;margin-bottom:0.3rem">Conductor</label>
                    <select name="driver_id" style="width:100%;padding:0.5rem;border:2px solid #FDBA74;border-radius:8px;font-size:0.85rem">
                        <option value="">Sin asignar</option>
                        @foreach($conductores as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-weight:800;color:#9A3412;font-size:0.8rem;display:block;margin-bottom:0.3rem">Notas</label>
                    <input type="text" name="notes" placeholder="Notas del traslado" style="width:100%;padding:0.5rem;border:2px solid #FDBA74;border-radius:8px;font-size:0.85rem">
                </div>
            </div>
            <button type="submit" style="padding:0.5rem 1.5rem;background:#EA580C;color:white;border:none;border-radius:8px;font-weight:800;cursor:pointer"><i class="fas fa-paper-plane"></i> Despachar</button>
        </form>
    </div>

    <!-- AMBULANCIAS ACTIVAS - CON IoT SIMULADO -->
    <div style="background:white;border-radius:16px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.05);margin-bottom:1.5rem;border-top:4px solid #DC2626">
        <h3 style="font-weight:900;color:#DC2626;margin-bottom:1rem"><i class="fas fa-satellite-dish" style="color:#DC2626"></i> Ambulancias Activas - Monitoreo IoT</h3>
        @php $activasList = $ambulancias->where('status', 'En Ruta'); @endphp
        @if($activasList->count() > 0)
        <div style="display:grid;gap:1rem">
            @foreach($activasList as $a)
            <div id="amb-{{ $a->id }}" style="background:linear-gradient(135deg,#FFF7ED,#FFEDD5);border-radius:16px;padding:1.5rem;border:2px solid #FDBA74">
                <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:1rem">
                    <div>
                        <div style="font-weight:900;color:#9A3412;font-size:1.1rem"><i class="fas fa-truck-medical" style="color:#EA580C"></i> {{ $a->code }}</div>
                        <div style="color:#A8A29E;font-size:0.8rem">{{ $a->type }}</div>
                    </div>
                    <div style="display:flex;gap:0.5rem;align-items:center">
                        <span style="background:{{ $a->priority == 'Critica' ? '#FEE2E2' : ($a->priority == 'Urgente' ? '#FFEDD5' : '#FEF9C3') }};color:{{ $a->priority == 'Critica' ? '#DC2626' : ($a->priority == 'Urgente' ? '#EA580C' : '#CA8A04') }};padding:0.3rem 0.8rem;border-radius:20px;font-weight:800;font-size:0.75rem">
                            {{ $a->priority }}
                        </span>
                        <span style="background:#FFEDD5;color:#EA580C;padding:0.3rem 0.6rem;border-radius:20px;font-weight:800;font-size:0.7rem"><i class="fas fa-signal"></i> LIVE</span>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.8rem;margin-bottom:1rem">
                    <div style="background:white;border-radius:8px;padding:0.6rem">
                        <div style="font-size:0.7rem;color:#A8A29E;font-weight:700">Origen</div>
                        <div style="font-weight:800;color:#9A3412;font-size:0.85rem">{{ $a->origin }}</div>
                    </div>
                    <div style="background:white;border-radius:8px;padding:0.6rem">
                        <div style="font-size:0.7rem;color:#A8A29E;font-weight:700">Destino</div>
                        <div style="font-weight:800;color:#9A3412;font-size:0.85rem">{{ $a->destination }}</div>
                    </div>
                </div>

                <!-- IoT PANEL -->
                <div style="background:#431407;border-radius:12px;padding:1rem;margin-bottom:1rem">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.8rem">
                        <span style="color:#FDBA74;font-weight:800;font-size:0.8rem"><i class="fas fa-microchip"></i> TELEMETRIA IoT</span>
                        <span style="color:#4ADE80;font-size:0.7rem"><i class="fas fa-circle" style="font-size:0.4rem;animation:blink 1s infinite"></i> Conectado</span>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:0.5rem">
                        <div style="text-align:center">
                            <div style="color:#A8A29E;font-size:0.6rem;font-weight:700">VELOCIDAD</div>
                            <div id="speed-{{ $a->id }}" style="color:#FDBA74;font-size:1.2rem;font-weight:900">{{ $a->speed }}</div>
                            <div style="color:#78716C;font-size:0.55rem">km/h</div>
                        </div>
                        <div style="text-align:center">
                            <div style="color:#A8A29E;font-size:0.6rem;font-weight:700">COMBUSTIBLE</div>
                            <div id="fuel-{{ $a->id }}" style="color:#FBBF24;font-size:1.2rem;font-weight:900">{{ $a->fuel }}</div>
                            <div style="color:#78716C;font-size:0.55rem">%</div>
                        </div>
                        <div style="text-align:center">
                            <div style="color:#A8A29E;font-size:0.6rem;font-weight:700">FC PACIENTE</div>
                            <div id="hr-{{ $a->id }}" style="color:{{ $a->heart_rate > 100 ? '#F87171' : '#4ADE80' }};font-size:1.2rem;font-weight:900">{{ $a->heart_rate }}</div>
                            <div style="color:#78716C;font-size:0.55rem">bpm</div>
                        </div>
                        <div style="text-align:center">
                            <div style="color:#A8A29E;font-size:0.6rem;font-weight:700">O2</div>
                            <div id="o2-{{ $a->id }}" style="color:{{ $a->oxygen < 90 ? '#F87171' : '#4ADE80' }};font-size:1.2rem;font-weight:900">{{ $a->oxygen }}</div>
                            <div style="color:#78716C;font-size:0.55rem">%</div>
                        </div>
                        <div style="text-align:center">
                            <div style="color:#A8A29E;font-size:0.6rem;font-weight:700">TEMP</div>
                            <div id="temp-{{ $a->id }}" style="color:{{ $a->temp > 38 ? '#F87171' : '#4ADE80' }};font-size:1.2rem;font-weight:900">{{ $a->temp }}</div>
                            <div style="color:#78716C;font-size:0.55rem">C</div>
                        </div>
                    </div>
                </div>

                <!-- MAPA SIMULADO -->
                <div style="background:#7F1D1D;border-radius:12px;padding:1rem;margin-bottom:1rem;position:relative;height:120px;overflow:hidden">
                    <div style="color:#FDBA74;font-size:0.7rem;font-weight:700;margin-bottom:0.5rem"><i class="fas fa-map-marker-alt" style="color:#FBBF24"></i> RASTREO GPS</div>
                    <div style="position:relative;height:80px">
                        <div style="position:absolute;top:10px;left:10%;color:#4ADE80;font-size:0.6rem"><i class="fas fa-hospital"></i> {{ $a->origin }}</div>
                        <div style="position:absolute;top:10px;right:10%;color:#FDBA74;font-size:0.6rem"><i class="fas fa-hospital"></i> {{ $a->destination }}</div>
                        <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%)">
                            <div id="truck-{{ $a->id }}" style="font-size:1.5rem;color:#FBBF24;animation:pulse 2s infinite"><i class="fas fa-truck-medical"></i></div>
                        </div>
                        <svg style="position:absolute;top:40%;left:15%;width:70%;height:2px">
                            <line x1="0" y1="1" x2="100%" y2="1" stroke="#FDBA74" stroke-width="2" stroke-dasharray="8,4"/>
                        </svg>
                    </div>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center">
                    <div style="font-size:0.75rem;color:#A8A29E">
                        Despachada: {{ $a->dispatched_at ? $a->dispatched_at->diffForHumans() : 'N/A' }}
                    </div>
                    <form method="POST" action="{{ url('/medico/ambulancias/' . $a->id . '/llegada') }}">
                        @csrf
                        <button type="submit" style="padding:0.4rem 1rem;background:#16A34A;color:white;border:none;border-radius:8px;font-weight:800;font-size:0.8rem;cursor:pointer"><i class="fas fa-flag-checkered"></i> Registrar Llegada</button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p style="text-align:center;color:#EA580C;padding:2rem;font-weight:700"><i class="fas fa-check-circle"></i> Sin ambulancias en ruta</p>
        @endif
    </div>

    <!-- AMBULANCIAS DISPONIBLES -->
    <div style="background:white;border-radius:16px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.05);margin-bottom:1.5rem;border-top:4px solid #EA580C">
        <h3 style="font-weight:900;color:#EA580C;margin-bottom:1rem"><i class="fas fa-check-circle" style="color:#EA580C"></i> Ambulancias Disponibles</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1rem">
            @foreach($ambulancias->where('status','Disponible') as $a)
            <div style="border:2px solid #FDBA74;border-radius:12px;padding:1rem;text-align:center;background:#FFF7ED">
                <i class="fas fa-truck-medical" style="font-size:1.8rem;color:#EA580C"></i>
                <div style="font-weight:900;color:#9A3412;margin-top:0.3rem">{{ $a->code }}</div>
                <div style="font-size:0.75rem;color:#A8A29E">{{ $a->type }}</div>
                <div style="font-size:0.7rem;color:#78716C;margin-top:0.3rem"><i class="fas fa-map-pin"></i> {{ $a->location }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- PACIENTES CRITICOS PARA TRASLADO -->
    <div style="background:white;border-radius:16px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.05);border-top:4px solid #DC2626">
        <h3 style="font-weight:900;color:#DC2626;margin-bottom:1rem"><i class="fas fa-heart-pulse" style="color:#DC2626"></i> Pacientes Criticos - Posible Traslado</h3>
        @if($pacientesCriticos->count() > 0)
        <table style="width:100%;border-collapse:collapse;font-size:0.85rem">
            <thead><tr style="background:#FEF2F2"><th style="padding:0.6rem;text-align:left;color:#991B1B">Paciente</th><th style="padding:0.6rem;color:#991B1B">Triage</th><th style="padding:0.6rem;color:#991B1B">Estado</th><th style="padding:0.6rem;color:#991B1B">Sintomas</th><th style="padding:0.6rem;color:#991B1B">Tiempo</th></tr></thead>
            <tbody>
            @foreach($pacientesCriticos as $p)
            <tr style="border-bottom:1px solid #FEE2E2">
                <td style="padding:0.5rem;font-weight:700;color:#7F1D1D">{{ $p->patient_name }}</td>
                <td style="padding:0.5rem"><span style="background:#FEE2E2;color:#DC2626;padding:0.15rem 0.5rem;border-radius:4px;font-size:0.75rem;font-weight:800">{{ $p->triage_level }}</span></td>
                <td style="padding:0.5rem;color:#991B1B;font-size:0.8rem">{{ $p->status }}</td>
                <td style="padding:0.5rem;color:#A8A29E">{{ Str::limit($p->symptoms, 40) }}</td>
                <td style="padding:0.5rem;font-size:0.75rem;color:#DC2626;font-weight:700">{{ $p->created_at->diffForHumans() }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
        @else
        <p style="text-align:center;color:#16A34A;padding:1.5rem;font-weight:700"><i class="fas fa-shield-heart"></i> Sin pacientes criticos pendientes</p>
        @endif
    </div>
</div>

<style>
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }
@keyframes pulse { 0%,100%{transform:translate(-50%,-50%) scale(1)} 50%{transform:translate(-50%,-50%) scale(1.15)} }
</style>

<script>
setInterval(function() {
    @foreach($activasList as $a)
    fetch('/medico/ambulancias/{{ $a->id }}/iot')
        .then(r => r.json())
        .then(data => {
            document.getElementById('speed-{{ $a->id }}').textContent = data.speed;
            document.getElementById('fuel-{{ $a->id }}').textContent = data.fuel;
            document.getElementById('hr-{{ $a->id }}').textContent = data.heart_rate;
            document.getElementById('hr-{{ $a->id }}').style.color = data.heart_rate > 100 ? '#F87171' : '#4ADE80';
            document.getElementById('o2-{{ $a->id }}').textContent = data.oxygen;
            document.getElementById('o2-{{ $a->id }}').style.color = data.oxygen < 90 ? '#F87171' : '#4ADE80';
            document.getElementById('temp-{{ $a->id }}').textContent = data.temperature;
            document.getElementById('temp-{{ $a->id }}').style.color = data.temperature > 38 ? '#F87171' : '#4ADE80';
        })
        .catch(e => console.log('IoT error {{ $a->id }}'));
    @endforeach
}, 5000);
</script>
@endsection
