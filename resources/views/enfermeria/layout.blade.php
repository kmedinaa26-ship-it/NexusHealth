<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Enfermeria') | HealthNexus</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { display: flex; height: 100vh; background: #F0F4F8; color: #1E1A17; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.6; } }

        .main { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .topbar { background: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.04); border-bottom: 1px solid #E2E8F0; }
        .topbar h1 { font-size: 1.15rem; font-weight: 800; color: #1E1A17; }
        .user-badge { background: linear-gradient(135deg, #3B82F6, #1D4ED8); color: white; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .content { padding: 2rem; animation: fadeIn 0.3s ease-out; flex: 1; }

        .sidebar { width: 270px; background: #FFFFFF; border-left: 1px solid #E2E8F0; display: flex; flex-direction: column; box-shadow: -2px 0 10px rgba(0,0,0,0.03); overflow-y: auto; }
        .sidebar-header { padding: 1.25rem; border-bottom: 1px solid #E2E8F0; text-align: center; }
        .sidebar-header img { max-width: 120px; margin-bottom: 0.25rem; }
        .sidebar-menu { padding: 0.75rem 0; flex: 1; }
        .menu-category { padding: 0.5rem 1.25rem; font-size: 0.65rem; font-weight: 800; color: #3B82F6; text-transform: uppercase; letter-spacing: 1px; margin-top: 0.75rem; }
        .menu-item { display: flex; align-items: center; padding: 0.6rem 1.25rem; color: #64748B; text-decoration: none; font-size: 0.82rem; font-weight: 600; transition: all 0.2s; border-right: 4px solid transparent; }
        .menu-item i { width: 18px; margin-right: 0.6rem; font-size: 0.85rem; }
        .menu-item:hover { background: #EFF6FF; color: #3B82F6; border-right-color: #93C5FD; }
        .menu-item.active { background: #EFF6FF; color: #1D4ED8; border-right-color: #1D4ED8; font-weight: 700; }
        .sidebar-footer { padding: 0.75rem 1.25rem; border-top: 1px solid #E2E8F0; }
        .btn-logout { width: 100%; padding: 0.55rem; background: #FEF2F2; color: #DC2626; border: 1px solid #FECACA; border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.2s; font-size: 0.85rem; }
        .btn-logout:hover { background: #DC2626; color: white; }

        .toast { position: fixed; top: 1rem; right: 1rem; z-index: 9999; padding: 1rem 1.5rem; border-radius: 12px; font-weight: 700; font-size: 0.9rem; animation: slideDown 0.4s ease-out; box-shadow: 0 10px 25px rgba(0,0,0,0.15); max-width: 400px; }
        .toast-success { background: #F0FDF4; color: #166534; border: 2px solid #86EFAC; }
        .toast-error { background: #FEF2F2; color: #991B1B; border: 2px solid #FCA5A5; }
    </style>
</head>
<body>
    <div class="main">
        <div class="topbar">
            <h1>@yield('title', 'Panel de Enfermeria')</h1>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-weight: 600; font-size: 0.9rem;">{{ auth()->user()->name }}</span>
                <span class="user-badge">{{ auth()->user()->role }}</span>
            </div>
        </div>
        <div class="content">
            @if(session('success'))
            <div class="toast toast-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="toast toast-error"><i class="fas fa-times-circle"></i> {{ session('error') }}</div>
            @endif
            @yield('content')
        </div>
    </div>

    <div class="sidebar">
        <div class="sidebar-header">
            <img src="https://z-cdn-media.chatglm.cn/files/e422f718-2f1b-43b1-8d33-abab22ae033a.png?auth_key=1880130553-c96ef22a7ec1475a8024ee420ae894cb-0-01473062212a4e246495206bff72dde3" alt="HealthNexus">
        </div>
        <div class="sidebar-menu">
            <div class="menu-category">Principal</div>
            <a href="{{ route('enfermeria.dashboard') }}" class="menu-item @yield('nav-dashboard')"><i class="fas fa-tachometer-alt"></i> Inicio</a>
            <a href="{{ route('enfermeria.alertas') }}" class="menu-item @yield('nav-alertas')"><i class="fas fa-bell"></i> Alertas LIVE</a>

            <div class="menu-category">Atencion Paciente</div>
            <a href="{{ route('enfermeria.triage') }}" class="menu-item @yield('nav-triage')"><i class="fas fa-heartbeat"></i> Triage Manchester</a>
            <a href="{{ route('enfermeria.signos') }}" class="menu-item @yield('nav-signos')"><i class="fas fa-stethoscope"></i> Signos Vitales</a>
            <a href="{{ route('enfermeria.pacientes') }}" class="menu-item @yield('nav-pacientes')"><i class="fas fa-users"></i> Pacientes</a>

            <div class="menu-category">Hospitalizacion</div>
            <a href="{{ route('enfermeria.hospitalizacion') }}" class="menu-item @yield('nav-hospitalizacion')"><i class="fas fa-bed"></i> Hospitalizacion</a>
            <a href="{{ route('enfermeria.evolucion') }}" class="menu-item @yield('nav-evolucion')"><i class="fas fa-notes-medical"></i> Evolucion</a>
            <a href="{{ route('enfermeria.medicamentos') }}" class="menu-item @yield('nav-meds')"><i class="fas fa-pills"></i> Medicamentos</a>

            <div class="menu-category">Documentacion</div>
            <a href="{{ route('enfermeria.documentacion') }}" class="menu-item @yield('nav-docs')"><i class="fas fa-file-medical"></i> Documentacion</a>
            <a href="{{ route('enfermeria.solicitudesFarmacia') }}" class="menu-item @yield('nav-solicitudes')"><i class="fas fa-prescription-bottle-alt"></i> Solicitudes Farmacia</a>
            <a href="{{ route('enfermeria.reportes') }}" class="menu-item @yield('nav-reportes')"><i class="fas fa-chart-bar"></i> Reportes</a>
        </div>
        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesion</button>
            </form>
        </div>
    </div>
</body>
</html>
