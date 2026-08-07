@extends('superadmin.layout')

@section('title', 'Carga de Quirófano')
@section('nav-quirofano', 'active')

@section('content')
<style>
    :root {
        --qf-primary: #F05A4E;
        --qf-primary-light: #FCE4E1;
        --qf-success: #2D9E6A;
        --qf-success-light: #D1FAE5;
        --qf-danger: #C7291C;
        --qf-dark: #1E1A17;
        --qf-text-secondary: #736860;
        --qf-border: #E5E7EB;
        --qf-bg-warm: #FFF9F8;
    }

    #qf-page { font-family: 'Inter', system-ui, sans-serif; }

    @keyframes qfFadeInUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes qfSlideDown { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }

    #qf-card { background: #fff; padding: 2rem; border-radius: 18px; box-shadow: 0 10px 30px -12px rgba(240,90,78,.18); margin-top: 1.5rem; border: 1px solid #FBE4E1; animation: qfFadeInUp .4s ease both; }

    #qf-card h3 { font-weight: 800; margin: 0 0 1.6rem; color: var(--qf-dark); font-size: 1.3rem; display: flex; align-items: center; gap: .6rem; }
    #qf-card h3 .qf-icon-badge { width: 42px; height: 42px; border-radius: 12px; background: var(--qf-primary-light); color: var(--qf-primary); display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }

    .qf-alert-success { background: var(--qf-success-light); color: var(--qf-success); padding: .9rem 1.2rem; border-radius: 12px; margin-bottom: 1.3rem; font-weight: 700; display: flex; align-items: center; gap: 8px; animation: qfSlideDown .35s ease; }

    .qf-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem; }

    .qf-label { font-size: .74rem; color: var(--qf-text-secondary); font-weight: 800; text-transform: uppercase; letter-spacing: .4px; display: block; margin-bottom: .4rem; }

    .qf-input, .qf-select {
        width: 100%; padding: .8rem 1rem; border: 1.5px solid var(--qf-border); border-radius: 10px;
        font-size: .9rem; color: var(--qf-dark); transition: border-color .2s ease, box-shadow .2s ease; background: #fff;
    }
    .qf-input:focus, .qf-select:focus { outline: none; border-color: var(--qf-primary); box-shadow: 0 0 0 3px rgba(240,90,78,.12); }

    .qf-hint { color: var(--qf-text-secondary); font-size: .78rem; margin-top: .4rem; display: block; }

    /* Buscador de pacientes */
    .qf-patient-search { position: relative; }
    .qf-patient-search .qf-input-icon-wrap { position: relative; }
    .qf-patient-search .qf-input-icon-wrap i.fa-search { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--qf-text-secondary); font-size: .85rem; pointer-events: none; }
    .qf-patient-search input[type="text"] { padding-left: 2.6rem; }
    .qf-patient-search .qf-clear-btn { position: absolute; right: .7rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--qf-text-secondary); cursor: pointer; font-size: .85rem; display: none; padding: 4px; }
    .qf-patient-search.has-value .qf-clear-btn { display: block; }

    .qf-dropdown { position: absolute; top: calc(100% + 6px); left: 0; right: 0; background: #fff; border: 1px solid var(--qf-border); border-radius: 12px; box-shadow: 0 16px 32px -12px rgba(0,0,0,.18); max-height: 260px; overflow-y: auto; z-index: 40; display: none; animation: qfSlideDown .18s ease; }
    .qf-dropdown.open { display: block; }
    .qf-dropdown-item { padding: .75rem 1rem; cursor: pointer; display: flex; justify-content: space-between; align-items: center; gap: 8px; border-bottom: 1px solid #FBEFED; }
    .qf-dropdown-item:last-child { border-bottom: none; }
    .qf-dropdown-item:hover, .qf-dropdown-item.qf-active { background: var(--qf-primary-light); }
    .qf-dropdown-item .qf-pname { font-weight: 700; font-size: .87rem; color: var(--qf-dark); }
    .qf-dropdown-item .qf-pid { font-size: .72rem; color: var(--qf-text-secondary); font-weight: 700; background: var(--qf-bg-warm); padding: 2px 8px; border-radius: 20px; flex-shrink: 0; }
    .qf-dropdown-empty { padding: 1rem; text-align: center; color: var(--qf-text-secondary); font-size: .85rem; }
    .qf-selected-chip { margin-top: .6rem; display: none; align-items: center; gap: 8px; background: var(--qf-success-light); color: var(--qf-success); font-weight: 700; font-size: .8rem; padding: .5rem .8rem; border-radius: 10px; }
    .qf-selected-chip.show { display: inline-flex; }

    /* Insumos */
    .qf-insumos-section { border-top: 2px dashed var(--qf-border); padding-top: 1.6rem; margin-top: .5rem; }
    .qf-insumos-section h4 { font-weight: 800; margin: 0 0 1.1rem; color: var(--qf-dark); font-size: 1rem; display: flex; align-items: center; gap: .5rem; }
    .qf-insumos-section h4 i { color: var(--qf-primary); }

    #insumos-container { display: grid; grid-template-columns: 1fr; gap: .9rem; }
    .insumo-row { display: grid; grid-template-columns: 3fr 1fr auto; gap: 1rem; align-items: center; background: var(--qf-bg-warm); border: 1px solid #FBE4E1; border-radius: 12px; padding: .7rem; animation: qfFadeInUp .3s ease both; }

    .qf-btn-add { background: var(--qf-dark); color: #fff; border: none; padding: .8rem 1rem; border-radius: 10px; cursor: pointer; font-weight: 700; transition: transform .15s ease, opacity .15s ease; }
    .qf-btn-add:hover { opacity: .88; transform: translateY(-1px); }
    .qf-btn-remove { background: var(--qf-danger); color: #fff; border: none; padding: .8rem 1rem; border-radius: 10px; cursor: pointer; font-weight: 700; transition: transform .15s ease, opacity .15s ease; }
    .qf-btn-remove:hover { opacity: .88; transform: translateY(-1px); }

    .qf-footer { margin-top: 2.2rem; text-align: right; }
    .qf-submit-btn { background: linear-gradient(135deg, var(--qf-success), #22935F); color: #fff; padding: 1rem 2.1rem; border-radius: 12px; font-weight: 800; font-size: .92rem; border: none; cursor: pointer; box-shadow: 0 12px 24px -8px rgba(45,158,106,.5); transition: transform .2s ease, box-shadow .2s ease; }
    .qf-submit-btn:hover { transform: translateY(-2px); box-shadow: 0 16px 28px -8px rgba(45,158,106,.6); }

    @media (max-width: 720px) {
        .qf-grid-2 { grid-template-columns: 1fr; }
        .insumo-row { grid-template-columns: 1fr; }
    }
</style>

<div id="qf-page">
    <div id="qf-card">
        <h3><span class="qf-icon-badge"><i class="fas fa-user-md"></i></span> Carga de Cirugías e Insumos</h3>

        @if(session('success'))
            <div class="qf-alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
        @endif

        <form action="{{ route('quirofano.cargar') }}" method="POST">
            @csrf

            <div class="qf-grid-2">
                <div class="qf-patient-search" id="qf-patient-search">
                    <label class="qf-label">Paciente</label>
                    <div class="qf-input-icon-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" id="qf-patient-input" class="qf-input" placeholder="Busca por nombre o ID..." autocomplete="off">
                        <button type="button" class="qf-clear-btn" id="qf-patient-clear" title="Limpiar"><i class="fas fa-times-circle"></i></button>
                    </div>
                    <input type="hidden" name="patient_id" id="qf-patient-id" required>
                    <div class="qf-dropdown" id="qf-patient-dropdown"></div>
                    <div class="qf-selected-chip" id="qf-patient-chip"><i class="fas fa-check-circle"></i> <span id="qf-patient-chip-text"></span></div>
                </div>

                <div>
                    <label class="qf-label">Tipo de Cirugía</label>
                    <select name="cirugia" class="qf-select" required>
                        <option value="Colecistectomía">Colecistectomía (Vesícula)</option>
                        <option value="Apendicectomía">Apendicectomía (Apéndice)</option>
                        <option value="Cesárea">Cesárea</option>
                        <option value="Hernioplastia">Hernioplastia (Hernia)</option>
                    </select>
                </div>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label class="qf-label">Horas de Quirófano (Ej: 2.5)</label>
                <input type="number" step="0.1" name="horas_or" min="0.5" value="2" class="qf-input" required>
                <small class="qf-hint"><i class="fas fa-circle-info"></i> Se cobrarán $5,000 MXN por hora automáticamente.</small>
            </div>

            <div class="qf-insumos-section">
                <h4><i class="fas fa-syringe"></i> Insumos Utilizados (Variables)</h4>
                <div id="insumos-container">
                    <div class="insumo-row">
                        <select name="insumos[0][id]" class="insumo-select qf-select">
                            <option value="">Selecciona un insumo...</option>
                            @foreach($insumos as $med)
                            <option value="{{ $med->id }}">{{ $med->name }} (Stock: {{ $med->stock }})</option>
                            @endforeach
                        </select>
                        <input type="number" name="insumos[0][quantity]" placeholder="Cant." min="1" class="qf-input">
                        <button type="button" class="qf-btn-add" onclick="addInsumo()"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
            </div>

            <div class="qf-footer">
                <button type="submit" class="qf-submit-btn">
                    <i class="fas fa-file-invoice-dollar"></i> Cargar a Cuenta del Paciente
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // ---------- Insumos dinámicos (misma lógica original) ----------
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

        // Cambia el botón de + a un botón de eliminar
        const btn = newRow.querySelector('button');
        btn.innerHTML = '<i class="fas fa-trash"></i>';
        btn.classList.remove('qf-btn-add');
        btn.classList.add('qf-btn-remove');
        btn.onclick = () => newRow.remove();

        container.appendChild(newRow);
    }

    // ---------- Buscador de pacientes (combobox) ----------
    (function () {
        const pacientes = @json($pacientes->map(function ($p) {
            return ['id' => $p->id, 'name' => $p->patient_name];
        }));

        const input = document.getElementById('qf-patient-input');
        const hiddenId = document.getElementById('qf-patient-id');
        const dropdown = document.getElementById('qf-patient-dropdown');
        const wrap = document.getElementById('qf-patient-search');
        const clearBtn = document.getElementById('qf-patient-clear');
        const chip = document.getElementById('qf-patient-chip');
        const chipText = document.getElementById('qf-patient-chip-text');

        let activeIndex = -1;
        let currentMatches = [];

        function normalize(str) {
            return (str || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        }

        function renderList(matches) {
            currentMatches = matches;
            activeIndex = -1;
            if (matches.length === 0) {
                dropdown.innerHTML = '<div class="qf-dropdown-empty"><i class="fas fa-user-slash"></i> Sin coincidencias</div>';
            } else {
                dropdown.innerHTML = matches.map(function (p, i) {
                    return '<div class="qf-dropdown-item" data-index="' + i + '" data-id="' + p.id + '" data-name="' + p.name.replace(/"/g, '&quot;') + '">' +
                        '<span class="qf-pname">' + p.name + '</span>' +
                        '<span class="qf-pid">ID ' + p.id + '</span>' +
                    '</div>';
                }).join('');
            }
            dropdown.classList.add('open');
        }

        function openList(query) {
            const q = normalize(query);
            const matches = q === ''
                ? pacientes
                : pacientes.filter(function (p) {
                    return normalize(p.name).indexOf(q) !== -1 || String(p.id).indexOf(q) !== -1;
                });
            renderList(matches.slice(0, 30));
        }

        function closeList() {
            dropdown.classList.remove('open');
        }

        function selectPatient(id, name) {
            hiddenId.value = id;
            input.value = name;
            wrap.classList.add('has-value');
            chip.classList.add('show');
            chipText.textContent = 'Paciente seleccionado: ' + name + ' (ID ' + id + ')';
            closeList();
        }

        input.addEventListener('focus', function () { openList(input.value); });
        input.addEventListener('input', function () {
            hiddenId.value = '';
            chip.classList.remove('show');
            wrap.classList.toggle('has-value', input.value.length > 0);
            openList(input.value);
        });

        input.addEventListener('keydown', function (e) {
            if (!dropdown.classList.contains('open')) return;
            const items = dropdown.querySelectorAll('.qf-dropdown-item');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                items.forEach(function (it, i) { it.classList.toggle('qf-active', i === activeIndex); });
                if (items[activeIndex]) items[activeIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                items.forEach(function (it, i) { it.classList.toggle('qf-active', i === activeIndex); });
                if (items[activeIndex]) items[activeIndex].scrollIntoView({ block: 'nearest' });
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIndex >= 0 && currentMatches[activeIndex]) {
                    const p = currentMatches[activeIndex];
                    selectPatient(p.id, p.name);
                }
            } else if (e.key === 'Escape') {
                closeList();
            }
        });

        dropdown.addEventListener('click', function (e) {
            const item = e.target.closest('.qf-dropdown-item');
            if (!item) return;
            selectPatient(item.getAttribute('data-id'), item.getAttribute('data-name'));
        });

        clearBtn.addEventListener('click', function () {
            input.value = '';
            hiddenId.value = '';
            wrap.classList.remove('has-value');
            chip.classList.remove('show');
            input.focus();
            openList('');
        });

        document.addEventListener('click', function (e) {
            if (!wrap.contains(e.target)) closeList();
        });
    })();
</script>
@endsection