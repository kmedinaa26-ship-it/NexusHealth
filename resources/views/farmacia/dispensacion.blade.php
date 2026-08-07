@extends('farmacia.layout')
@section('title', 'Dispensación')
@section('content')
<style>
    :root {
        --ph-primary: #F97316;
        --ph-primary-dark: #DC2626;
        --ph-primary-light: #FFEDD5;
        --ph-bg-cool: #FFF7ED;
        --ph-success: #16A34A;
        --ph-success-light: #DCFCE7;
        --ph-text: #1F2937;
        --ph-text-secondary: #6B7280;
        --ph-border: #E5E7EB;
    }

    #ph-page { font-family: 'Inter', system-ui, sans-serif; background: var(--ph-bg-cool); min-height: 100vh; padding: 1.5rem; border-radius: 20px; color: var(--ph-text); }

    @keyframes phFadeInUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes phSlideDown { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }

    #ph-header { display: flex; align-items: center; gap: 14px; margin-bottom: 1.7rem; }
    #ph-header .ph-icon-badge { width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, var(--ph-primary), var(--ph-primary-dark)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; box-shadow: 0 10px 20px -8px rgba(249,115,22,.5); }
    #ph-header h1 { font-size: 1.4rem; font-weight: 800; margin: 0; color: var(--ph-text); }
    #ph-header .ph-sub { font-size: .8rem; color: var(--ph-text-secondary); margin-top: 2px; }

    #ph-form-card { max-width: 640px; margin: 0 auto; background: #fff; padding: 2.1rem; border-radius: 18px; box-shadow: 0 12px 30px -14px rgba(249,115,22,.18); border: 1px solid #FDE9D6; animation: phFadeInUp .4s ease both; }

    .ph-field { margin-bottom: 1.6rem; position: relative; }
    .ph-label { display: block; font-size: .78rem; font-weight: 800; color: var(--ph-text-secondary); text-transform: uppercase; letter-spacing: .4px; margin-bottom: .5rem; }
    .ph-hint { font-size: .74rem; color: #9CA3AF; margin-top: .4rem; display: block; }

    .ph-input-icon-wrap { position: relative; }
    .ph-input-icon-wrap i.ph-lead-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--ph-text-secondary); font-size: .85rem; }
    .ph-search-input { width: 100%; padding: .85rem 1rem .85rem 2.6rem; border: 1.5px solid var(--ph-border); border-radius: 10px; font-size: .9rem; color: var(--ph-text); transition: border-color .2s ease, box-shadow .2s ease; background: #fff; }
    .ph-search-input:focus { outline: none; border-color: var(--ph-primary); box-shadow: 0 0 0 3px rgba(249,115,22,.15); }
    .ph-clear-btn { position: absolute; right: .7rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--ph-text-secondary); cursor: pointer; font-size: .85rem; padding: 4px; display: none; }
    .ph-field.has-value .ph-clear-btn { display: block; }

    .ph-dropdown { position: absolute; top: calc(100% + 6px); left: 0; right: 0; background: #fff; border: 1px solid var(--ph-border); border-radius: 12px; box-shadow: 0 18px 34px -14px rgba(0,0,0,.2); max-height: 250px; overflow-y: auto; z-index: 40; display: none; animation: phSlideDown .18s ease; }
    .ph-dropdown.open { display: block; }
    .ph-dropdown-item { padding: .75rem 1rem; cursor: pointer; display: flex; flex-direction: column; gap: 2px; border-bottom: 1px solid #FEF1E4; }
    .ph-dropdown-item:last-child { border-bottom: none; }
    .ph-dropdown-item:hover, .ph-dropdown-item.ph-active { background: var(--ph-primary-light); }
    .ph-dropdown-item .ph-main-line { font-weight: 700; font-size: .85rem; color: var(--ph-text); }
    .ph-dropdown-item .ph-sub-line { font-size: .72rem; color: var(--ph-text-secondary); font-weight: 600; }
    .ph-dropdown-empty { padding: 1rem; text-align: center; color: var(--ph-text-secondary); font-size: .85rem; }

    .ph-chip { margin-top: .55rem; display: none; align-items: center; gap: 8px; background: var(--ph-success-light); color: var(--ph-success); font-weight: 700; font-size: .78rem; padding: .5rem .8rem; border-radius: 10px; }
    .ph-chip.show { display: inline-flex; }

    .ph-qty-input { width: 8rem; padding: .85rem 1rem; border: 1.5px solid var(--ph-border); border-radius: 10px; font-size: .9rem; }
    .ph-qty-input:focus { outline: none; border-color: var(--ph-primary); box-shadow: 0 0 0 3px rgba(249,115,22,.15); }

    .ph-submit-btn { width: 100%; background: linear-gradient(135deg, var(--ph-primary), var(--ph-primary-dark)); color: #fff; font-weight: 800; padding: 1rem 1.5rem; border-radius: 12px; border: none; cursor: pointer; box-shadow: 0 14px 26px -10px rgba(220,38,38,.5); transition: transform .2s ease, box-shadow .2s ease; font-size: .92rem; }
    .ph-submit-btn:hover { transform: translateY(-2px); box-shadow: 0 18px 30px -10px rgba(220,38,38,.55); }

    /* Tabla de últimas dispensaciones */
    #ph-recent { max-width: 640px; margin: 2.4rem auto 0; }
    #ph-recent h3 { font-size: 1rem; font-weight: 800; color: var(--ph-text); margin: 0 0 1rem; display: flex; align-items: center; gap: 8px; }
    #ph-recent h3 .dot { width: 9px; height: 9px; border-radius: 50%; background: var(--ph-primary); }
    .ph-recent-card { background: #fff; border-radius: 16px; box-shadow: 0 10px 22px -12px rgba(249,115,22,.15); border: 1px solid #FDE9D6; overflow: hidden; }
    .ph-table { width: 100%; border-collapse: collapse; font-size: .84rem; }
    .ph-table thead th { text-align: left; background: linear-gradient(135deg, #FFF7ED, #FEF2F2); color: #C2410C; text-transform: uppercase; font-size: .68rem; font-weight: 800; letter-spacing: .4px; padding: .85rem 1.1rem; }
    .ph-table tbody td { padding: .9rem 1.1rem; border-top: 1px solid #FEF1E4; font-weight: 600; color: var(--ph-text); }
    .ph-table tbody tr { animation: phFadeInUp .35s ease both; transition: background .15s ease; }
    .ph-table tbody tr:hover { background: var(--ph-bg-cool); }
    .ph-table .ph-date { color: var(--ph-text-secondary); font-weight: 600; }
    .ph-table .ph-empty-row td { text-align: center; color: var(--ph-text-secondary); padding: 1.5rem; }

    @media (max-width: 640px) {
        #ph-form-card { padding: 1.4rem; }
    }
</style>

<div id="ph-page">
    <div id="ph-header">
        <div class="ph-icon-badge"><i class="fas fa-prescription-bottle-medical"></i></div>
        <div>
            <h1>Dispensar Medicamento</h1>
            <div class="ph-sub">Busca al médico, paciente y medicamento para registrar la entrega</div>
        </div>
    </div>

    <div id="ph-form-card">
        <form action="{{ route('farmacia.dispense') }}" method="POST">
            @csrf

            <!-- BUSCADOR INTELIGENTE: MÉDICO -->
            <div class="ph-field" id="doctor-field">
                <label class="ph-label">Recetado por</label>
                <div class="ph-input-icon-wrap">
                    <i class="fas fa-user-doctor ph-lead-icon"></i>
                    <input type="text" id="doctor-search" class="ph-search-input" placeholder="Escribe el nombre o rol del médico..." autocomplete="off" required>
                    <button type="button" class="ph-clear-btn" data-clear-for="doctor-search"><i class="fas fa-times-circle"></i></button>
                </div>
                <input type="hidden" name="doctor_id" id="doctor-id" required>
                <div class="ph-dropdown" id="doctor-dropdown"></div>
                <div class="ph-chip" id="doctor-chip"><i class="fas fa-check-circle"></i> <span id="doctor-chip-text"></span></div>
                <p class="ph-hint">Ejemplo: "CARLOS" o "Médico C"</p>
            </div>

            <!-- BUSCADOR INTELIGENTE: PACIENTE -->
            <div class="ph-field" id="patient-field">
                <label class="ph-label">Paciente</label>
                <div class="ph-input-icon-wrap">
                    <i class="fas fa-hospital-user ph-lead-icon"></i>
                    <input type="text" id="patient-search" class="ph-search-input" placeholder="Escribe el nombre del paciente..." autocomplete="off" required>
                    <button type="button" class="ph-clear-btn" data-clear-for="patient-search"><i class="fas fa-times-circle"></i></button>
                </div>
                <input type="hidden" name="patient_id" id="patient-id" required>
                <div class="ph-dropdown" id="patient-dropdown"></div>
                <div class="ph-chip" id="patient-chip"><i class="fas fa-check-circle"></i> <span id="patient-chip-text"></span></div>
                <p class="ph-hint">Ejemplo: "Gabriela" o "Axel"</p>
            </div>

            <!-- BUSCADOR INTELIGENTE: MEDICAMENTO -->
            <div class="ph-field" id="med-field">
                <label class="ph-label">Medicamento</label>
                <div class="ph-input-icon-wrap">
                    <i class="fas fa-pills ph-lead-icon"></i>
                    <input type="text" id="med-search" class="ph-search-input" placeholder="Escribe el nombre del medicamento..." autocomplete="off" required>
                    <button type="button" class="ph-clear-btn" data-clear-for="med-search"><i class="fas fa-times-circle"></i></button>
                </div>
                <input type="hidden" name="medication_id" id="med-id" required>
                <div class="ph-dropdown" id="med-dropdown"></div>
                <div class="ph-chip" id="med-chip"><i class="fas fa-check-circle"></i> <span id="med-chip-text"></span></div>
                <p class="ph-hint">Ejemplo: "Acido" o "Paracetamol"</p>
            </div>

            <!-- CANTIDAD -->
            <div class="ph-field">
                <label class="ph-label">Cantidad</label>
                <input type="number" name="quantity" min="1" value="1" class="ph-qty-input" required>
            </div>

            <!-- BOTÓN -->
            <button type="submit" class="ph-submit-btn">
                <i class="fas fa-prescription-bottle-medical"></i> Dispensar Receta
            </button>
        </form>
    </div>

    <!-- ÚLTIMAS DISPENSACIONES -->
    <div id="ph-recent">
        <h3><span class="dot"></span> Últimas Dispensaciones</h3>
        <div class="ph-recent-card">
            <table class="ph-table">
                <thead>
                    <tr>
                        <th>Paciente</th>
                        <th>Medicamento</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent as $r)
                    <tr>
                        <td>{{ $r->patient_name }}</td>
                        <td>{{ $r->medication_name }} (x{{ $r->quantity }})</td>
                        <td class="ph-date">{{ $r->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr class="ph-empty-row"><td colspan="3">Aún no hay dispensaciones registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- LÓGICA DE LOS BUSCADORES: combobox filtrable que llena el input oculto con el ID -->
<script>
    (function () {
        const doctors = @json($doctors->map(function ($d) {
            return ['id' => $d->id, 'main' => $d->name, 'sub' => $d->role];
        }));
        const patients = @json($patients->map(function ($p) {
            return ['id' => $p->id, 'main' => $p->patient_name, 'sub' => 'Triage: ' . $p->triage_level];
        }));
        const medications = @json($medications->map(function ($m) {
            return ['id' => $m->id, 'main' => $m->name, 'sub' => 'Stock: ' . $m->stock . ' | Nivel: ' . $m->required_level];
        }));

        function normalize(str) {
            return (str || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        }

        function setupCombobox(config) {
            const input = document.getElementById(config.inputId);
            const hidden = document.getElementById(config.hiddenId);
            const dropdown = document.getElementById(config.dropdownId);
            const field = document.getElementById(config.fieldId);
            const chip = document.getElementById(config.chipId);
            const chipText = document.getElementById(config.chipId + '-text');
            const data = config.data;

            let activeIndex = -1;
            let currentMatches = [];

            function renderList(matches) {
                currentMatches = matches;
                activeIndex = -1;
                if (matches.length === 0) {
                    dropdown.innerHTML = '<div class="ph-dropdown-empty"><i class="fas fa-magnifying-glass"></i> Sin coincidencias</div>';
                } else {
                    dropdown.innerHTML = matches.map(function (item, i) {
                        return '<div class="ph-dropdown-item" data-index="' + i + '" data-id="' + item.id + '" data-main="' + item.main.replace(/"/g, '&quot;') + '">' +
                            '<span class="ph-main-line">' + item.main + '</span>' +
                            '<span class="ph-sub-line">' + item.sub + '</span>' +
                        '</div>';
                    }).join('');
                }
                dropdown.classList.add('open');
            }

            function openList(query) {
                const q = normalize(query);
                const matches = q === ''
                    ? data
                    : data.filter(function (item) {
                        return normalize(item.main).indexOf(q) !== -1 || normalize(item.sub).indexOf(q) !== -1;
                    });
                renderList(matches.slice(0, 30));
            }

            function closeList() { dropdown.classList.remove('open'); }

            function select(id, main) {
                hidden.value = id;
                input.value = main;
                field.classList.add('has-value');
                chip.classList.add('show');
                chipText.textContent = 'Seleccionado: ' + main;
                closeList();
            }

            input.addEventListener('focus', function () { openList(input.value); });
            input.addEventListener('input', function () {
                hidden.value = '';
                chip.classList.remove('show');
                field.classList.toggle('has-value', input.value.length > 0);
                openList(input.value);
            });

            input.addEventListener('keydown', function (e) {
                if (!dropdown.classList.contains('open')) return;
                const items = dropdown.querySelectorAll('.ph-dropdown-item');
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeIndex = Math.min(activeIndex + 1, items.length - 1);
                    items.forEach(function (it, i) { it.classList.toggle('ph-active', i === activeIndex); });
                    if (items[activeIndex]) items[activeIndex].scrollIntoView({ block: 'nearest' });
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeIndex = Math.max(activeIndex - 1, 0);
                    items.forEach(function (it, i) { it.classList.toggle('ph-active', i === activeIndex); });
                    if (items[activeIndex]) items[activeIndex].scrollIntoView({ block: 'nearest' });
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (activeIndex >= 0 && currentMatches[activeIndex]) {
                        const item = currentMatches[activeIndex];
                        select(item.id, item.main);
                    }
                } else if (e.key === 'Escape') {
                    closeList();
                }
            });

            dropdown.addEventListener('click', function (e) {
                const item = e.target.closest('.ph-dropdown-item');
                if (!item) return;
                select(item.getAttribute('data-id'), item.getAttribute('data-main'));
            });

            document.querySelector('[data-clear-for="' + config.inputId + '"]').addEventListener('click', function () {
                input.value = '';
                hidden.value = '';
                field.classList.remove('has-value');
                chip.classList.remove('show');
                input.focus();
                openList('');
            });

            document.addEventListener('click', function (e) {
                if (!field.contains(e.target)) closeList();
            });
        }

        setupCombobox({ inputId: 'doctor-search', hiddenId: 'doctor-id', dropdownId: 'doctor-dropdown', fieldId: 'doctor-field', chipId: 'doctor-chip', data: doctors });
        setupCombobox({ inputId: 'patient-search', hiddenId: 'patient-id', dropdownId: 'patient-dropdown', fieldId: 'patient-field', chipId: 'patient-chip', data: patients });
        setupCombobox({ inputId: 'med-search', hiddenId: 'med-id', dropdownId: 'med-dropdown', fieldId: 'med-field', chipId: 'med-chip', data: medications });
    })();
</script>
@endsection