@extends('superadmin.layout')
@section('title', 'Gestión de Personal y Roles')
@section('nav-roles', 'active')

@section('content')
<style>
    :root { --rp-primary: #E85D3A; --rp-primary-dark: #C2410C; --rp-bg-warm: #FFF7ED; --rp-border: #FED7AA; --rp-ok: #2D9E6A; --rp-bad: #C7291C; --rp-text: #1E1A17; --rp-text-sec: #736860; }
    .rp-card { background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.04); margin-bottom: 1.5rem; overflow: hidden; }
    .rp-card-pad { padding: 1.5rem; }
    .rp-filters { display: flex; gap: 1rem; align-items: center; flex-wrap: wrap; margin-bottom: 1.5rem; }
    .rp-filter-select { padding: 0.6rem 1rem; border: 1.5px solid var(--rp-border); border-radius: 9px; font-size: 0.85rem; font-weight: 700; color: var(--rp-text); background: #fff; cursor: pointer; min-width: 200px; }
    .rp-table { width: 100%; border-collapse: collapse; }
    .rp-table th { background: var(--rp-text); color: white; text-align: left; padding: 0.85rem 1rem; font-size: 0.7rem; text-transform: uppercase; letter-spacing: .4px; }
    .rp-table td { padding: 0.85rem 1rem; border-bottom: 1px solid #F1EFED; font-size: 0.85rem; vertical-align: middle; }
    .rp-table tr:hover td { background: var(--rp-bg-warm); }
    .rp-name { font-weight: 800; color: var(--rp-text); }
    .rp-sub { font-size: 0.75rem; color: var(--rp-text-sec); font-weight: 600; }
    .rp-pill { font-size: 0.65rem; font-weight: 800; padding: 0.2rem 0.7rem; border-radius: 20px; background: var(--rp-bg-warm); color: var(--rp-primary-dark); white-space: nowrap; }
    .rp-btn { border: none; border-radius: 8px; padding: 0.45rem 0.8rem; font-size: 0.72rem; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 0.3rem; transition: all .15s ease; margin-right: 0.3rem; margin-bottom: 0.3rem; }
    .rp-btn:hover { transform: translateY(-1px); }
    .rp-btn-edit { background: #EFF6FF; color: #1D4ED8; }
    .rp-btn-pin { background: #FFF7ED; color: var(--rp-primary-dark); }
    
    .rp-overlay { display: none; position: fixed; inset: 0; background: rgba(30,26,23,.55); z-index: 9998; align-items: center; justify-content: center; padding: 1rem; }
    .rp-overlay.show { display: flex; }
    .rp-modal { background: #fff; border-radius: 16px; width: 100%; max-width: 450px; box-shadow: 0 30px 60px -20px rgba(0,0,0,.4); }
    .rp-modal-head { background: linear-gradient(120deg, var(--rp-primary), #FB923C); color: #fff; padding: 1.1rem 1.3rem; display: flex; justify-content: space-between; align-items: center; }
    .rp-modal-head h3 { margin: 0; font-size: 1rem; font-weight: 800; }
    .rp-modal-close { background: rgba(255,255,255,.25); border: none; color: #fff; width: 26px; height: 26px; border-radius: 50%; cursor: pointer; }
    .rp-modal-body { padding: 1.3rem; }
    .rp-field { margin-bottom: 1rem; }
    .rp-field label { display: block; font-size: 0.7rem; font-weight: 800; color: var(--rp-text-sec); text-transform: uppercase; margin-bottom: .35rem; }
    .rp-field input, .rp-field select { width: 100%; padding: .6rem .8rem; border: 1.5px solid var(--rp-border); border-radius: 9px; font-size: .85rem; font-weight: 600; color: var(--rp-text); box-sizing: border-box; }
    .rp-field input:focus, .rp-field select:focus { outline: none; border-color: var(--rp-primary); box-shadow: 0 0 0 3px rgba(232,93,58,.12); }
    .rp-submit { width: 100%; background: linear-gradient(120deg, var(--rp-primary), var(--rp-primary-dark)); color: #fff; border: none; padding: .7rem; border-radius: 10px; font-weight: 800; font-size: .85rem; cursor: pointer; margin-top: .3rem; }
    .rp-submit:hover { opacity: .9; }
    .rp-empty { text-align: center; padding: 3rem; color: var(--rp-text-sec); display: none; }
    .rp-msg { padding: 0.5rem; border-radius: 8px; font-size: 0.8rem; font-weight: 700; margin-top: 0.5rem; display: none; }
    .rp-msg.ok { background: #DCFCE7; color: #166534; display: block; }
    .rp-msg.err { background: #FEE2E2; color: #991B1B; display: block; }
</style>

<div class="rp-card rp-card-pad">
    <h2 style="font-weight: 800; color: var(--rp-text); margin: 0;">Directorio de Personal</h2>
    <p style="color: var(--rp-text-sec); font-size: 0.85rem; margin: .25rem 0 0;">Administra datos y Pines de acceso de los usuarios del sistema.</p>
</div>

<div class="rp-card rp-card-pad" style="padding: 1rem 1.5rem;">
    <div class="rp-filters">
        <i class="fas fa-filter" style="color: var(--rp-primary);"></i>
        <select id="rp-role-filter" class="rp-filter-select" onchange="filterStaff()">
            <option value="all">Todos los Roles</option>
            @foreach($roles as $role)
                <option value="{{ $role }}">{{ $role }}</option>
            @endforeach
        </select>
        <span id="rp-count" class="rp-sub">Mostrando: {{ count($staff) }} usuarios</span>
    </div>
</div>

<div class="rp-card" style="overflow-x: auto;">
    <table class="rp-table">
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Rol</th>
                <th>Datos Clave</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($staff as $user)
            <tr class="staff-row" data-role="{{ $user->role }}">
                <td>
                    <div class="rp-name">{{ $user->name }}</div>
                    <div class="rp-sub">{{ $user->email }}</div>
                </td>
                <td><span class="rp-pill">{{ $user->role }}</span></td>
                <td>
                    <div class="rp-sub"><strong>CURP:</strong> {{ $user->curp ?? 'Sin asignar' }}</div>
                    <div class="rp-sub"><strong>Cédula:</strong> {{ $user->license_number ?? 'Sin asignar' }}</div>
                </td>
                <td>
                    <span class="rp-pill" style="background: #DCFCE7; color: #166534;">{{ $user->status ?? 'Activo' }}</span>
                </td>
                <td>
                    <button class="rp-btn rp-btn-edit" onclick="openEdit({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->email }}', '{{ $user->curp ?? '' }}', '{{ $user->license_number ?? '' }}', '{{ $user->role }}')">
                        <i class="fas fa-user-pen"></i> Editar
                    </button>
                    @if(strpos($user->role, 'Médico') !== false || strpos($user->role, 'Especialista') !== false)
                    <button class="rp-btn rp-btn-pin" onclick="openPin({{ $user->id }}, '{{ addslashes($user->name) }}')">
                        <i class="fas fa-key"></i> PIN
                    </button>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div id="rp-empty-state" class="rp-empty"><i class="fas fa-user-slash" style="font-size:2rem;display:block;margin-bottom:1rem;"></i>No se encontraron usuarios con este rol.</div>
</div>

<!-- MODAL EDITAR -->
<div class="rp-overlay" id="rp-overlay-edit">
    <div class="rp-modal">
        <div class="rp-modal-head"><h3>Editar Información</h3><button class="rp-modal-close" onclick="closeModal('rp-overlay-edit')">✕</button></div>
        <div class="rp-modal-body">
            <form onsubmit="return submitEdit(event)">
                <input type="hidden" id="edit-id">
                <div class="rp-field"><label>Nombre Completo</label><input type="text" id="edit-name" required></div>
                <div class="rp-field"><label>Correo</label><input type="email" id="edit-email" required></div>
                <div class="rp-field"><label>CURP</label><input type="text" id="edit-curp"></div>
                <div class="rp-field"><label>Cédula Profesional</label><input type="text" id="edit-cedula"></div>
                <div class="rp-field"><label>Rol</label>
                    <select id="edit-role" required>
                        @foreach($roles as $role)<option value="{{ $role }}">{{ $role }}</option>@endforeach
                    </select>
                </div>
                <button type="submit" class="rp-submit"><i class="fas fa-save"></i> Guardar</button>
            </form>
        </div>
    </div>
</div>

<!-- MODAL PIN -->
<div class="rp-overlay" id="rp-overlay-pin">
    <div class="rp-modal">
        <div class="rp-modal-head"><h3>Cambiar PIN de Acceso</h3><button class="rp-modal-close" onclick="closeModal('rp-overlay-pin')">✕</button></div>
        <div class="rp-modal-body">
            <p style="font-size:0.85rem;color:var(--rp-text-sec);margin-bottom:1rem;">Usuario: <strong id="pin-user-name"></strong></p>
            <form onsubmit="return submitPin(event)">
                <input type="hidden" id="pin-id">
                <div class="rp-field">
                    <label>Nuevo PIN (4 dígitos)</label>
                    <input type="text" id="pin-value" maxlength="4" pattern="\d{4}" required inputmode="numeric" placeholder="Ej. 1234">
                </div>
                <div id="pin-msg" class="rp-msg"></div>
                <button type="submit" class="rp-submit" id="pin-btn"><i class="fas fa-key"></i> Actualizar PIN</button>
            </form>
        </div>
    </div>
</div>

<script>
function filterStaff() {
    var f = document.getElementById('rp-role-filter').value;
    var rows = document.querySelectorAll('.staff-row');
    var c = 0;
    rows.forEach(function(r) { if (f === 'all' || r.dataset.role === f) { r.style.display = ''; c++; } else { r.style.display = 'none'; } });
    document.getElementById('rp-count').textContent = 'Mostrando: ' + c + ' usuarios';
    document.getElementById('rp-empty-state').style.display = c === 0 ? 'block' : 'none';
}

function openEdit(id, name, email, curp, cedula, role) {
    document.getElementById('edit-id').value = id;
    document.getElementById('edit-name').value = name;
    document.getElementById('edit-email').value = email;
    document.getElementById('edit-curp').value = curp;
    document.getElementById('edit-cedula').value = cedula;
    document.getElementById('edit-role').value = role;
    document.getElementById('rp-overlay-edit').classList.add('show');
}

function openPin(id, name) {
    document.getElementById('pin-id').value = id;
    document.getElementById('pin-user-name').textContent = name;
    document.getElementById('pin-value').value = '';
    document.getElementById('pin-msg').className = 'rp-msg';
    document.getElementById('rp-overlay-pin').classList.add('show');
}

function closeModal(id) { document.getElementById(id).classList.remove('show'); }

function submitEdit(e) {
    e.preventDefault();
    alert('Vista preparada. Falta crear la ruta PUT para guardar datos del usuario.');
    closeModal('rp-overlay-edit');
    return false;
}

async function submitPin(e) {
    e.preventDefault();
    var btn = document.getElementById('pin-btn');
    var msg = document.getElementById('pin-msg');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    msg.className = 'rp-msg';
    
    try {
        var res = await fetch('/superadmin/staff/' + document.getElementById('pin-id').value + '/pin', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ pin: document.getElementById('pin-value').value })
        });
        var data = await res.json();
        if (!res.ok) throw new Error(data.message || 'Error al guardar');
        msg.textContent = data.message;
        msg.className = 'rp-msg ok';
        setTimeout(function(){ closeModal('rp-overlay-pin'); }, 1500);
    } catch (err) {
        msg.textContent = err.message;
        msg.className = 'rp-msg err';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-key"></i> Actualizar PIN';
    }
}
</script>
@endsection
