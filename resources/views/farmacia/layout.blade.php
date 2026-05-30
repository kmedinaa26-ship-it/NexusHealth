<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Farmacia') | HealthNexus</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        body { display: flex; height: 100vh; background: #F9FAFB; color: #1E1A17; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
        .sidebar { width: 270px; background: #FFFFFF; border-right: 1px solid #E5E7EB; display: flex; flex-direction: column; box-shadow: 2px 0 10px rgba(0,0,0,0.02); overflow-y: auto; }
        .sidebar-header { padding: 1.5rem; border-bottom: 1px solid #E5E7EB; text-align: center; }
        .sidebar-header img { max-width: 140px; margin-bottom: 0.5rem; }
        .sidebar-role { background: #2D9E6A; color: white; padding: 0.2rem 0.8rem; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .sidebar-menu { padding: 1rem 0; flex: 1; }
        .menu-category { padding: 0.5rem 1.5rem; font-size: 0.65rem; font-weight: 800; color: #2D9E6A; text-transform: uppercase; letter-spacing: 1px; margin-top: 1rem; }
        .menu-item { display: flex; align-items: center; padding: 0.6rem 1.5rem; color: #736860; text-decoration: none; font-size: 0.82rem; font-weight: 600; transition: all 0.2s; border-left: 4px solid transparent; }
        .menu-item i { width: 20px; margin-right: 0.7rem; font-size: 0.85rem; }
        .menu-item:hover { background: #EBF9F2; color: #2D9E6A; border-left-color: #2D9E6A; }
        .menu-item.active { background: #EBF9F2; color: #1B7A4A; border-left-color: #1B7A4A; font-weight: 700; }
        .sidebar-footer { padding: 1rem 1.5rem; border-top: 1px solid #E5E7EB; }
        .btn-logout { width: 100%; padding: 0.6rem; background: #FFF1F0; color: #C7291C; border: 1px solid #FFE0DC; border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.2s; }
        .btn-logout:hover { background: #C7291C; color: white; }
        .main { flex: 1; display: flex; flex-direction: column; overflow-y: auto; }
        .topbar { background: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02); border-bottom: 1px solid #E5E7EB; }
        .topbar h1 { font-size: 1.2rem; font-weight: 800; color: #1E1A17; }
        .user-badge { background: linear-gradient(135deg, #2D9E6A, #34D399); color: white; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; }
        .content { padding: 2rem; animation: fadeIn 0.4s ease-out; flex: 1; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="https://z-cdn-media.chatglm.cn/files/e422f718-2f1b-43b1-8d33-abab22ae033a.png?auth_key=1880135553-c96ef22a7ec1475a8024ee420ae894cb-0-01473062212a4e246495206bff72dde3" alt="HealthNexus">
            <span class="sidebar-role">Farmacia</span>
        </div>
        <div class="sidebar-menu">
            <div class="menu-category">Principal</div>
            <a href="{{ route('farmacia.dashboard') }}" class="menu-item @yield('nav-dashboard')"><i class="fas fa-tachometer-alt"></i> Panel General</a>
            <a href="{{ route('farmacia.inventory') }}" class="menu-item @yield('nav-inventario')"><i class="fas fa-boxes-stacked"></i> Inventario A/B/C/Enf</a>
            <a href="{{ route('farmacia.enfermeraMeds') }}" class="menu-item @yield('nav-enfermera')"><i class="fas fa-user-nurse"></i> Medicamentos Enfermeria</a>

            <div class="menu-category">Operacion</div>
            <a href="{{ route('farmacia.dispensacion') }}" class="menu-item @yield('nav-dispensacion')"><i class="fas fa-prescription"></i> Dispensacion</a>
            <a href="{{ route('farmacia.controlled') }}" class="menu-item @yield('nav-controlados')"><i class="fas fa-lock"></i> Controlados</a>
            <a href="{{ route('farmacia.crashCarts') }}" class="menu-item @yield('nav-crashcarts')"><i class="fas fa-first-aid"></i> Carros Emergencia</a>
            <a href="{{ route('farmacia.traspasos') }}" class="menu-item @yield('nav-traspasos')"><i class="fas fa-exchange-alt"></i> Traspasos</a>

            <div class="menu-category">Compras</div>
            <a href="{{ route('farmacia.proveedores') }}" class="menu-item @yield('nav-proveedores')"><i class="fas fa-truck"></i> Proveedores</a>
            <a href="{{ route('farmacia.ordenes') }}" class="menu-item @yield('nav-ordenes')"><i class="fas fa-file-invoice-dollar"></i> Ordenes de Compra</a>

            <div class="menu-category">Analisis</div>
            <a href="{{ route('farmacia.anomalias') }}" class="menu-item @yield('nav-anomalias')"><i class="fas fa-microscope"></i> Anomalias</a>
            <a href="{{ route('farmacia.consumo') }}" class="menu-item @yield('nav-consumo')"><i class="fas fa-temperature-high"></i> Consumo por Area</a>
            <a href="{{ route('farmacia.desabasto') }}" class="menu-item @yield('nav-desabasto')"><i class="fas fa-chart-bar"></i> Desabasto</a>

            <div class="menu-category">Documentacion</div>
            <a href="{{ route('farmacia.exportar') }}" class="menu-item @yield('nav-exportar')"><i class="fas fa-file-medical-alt"></i> Exportar</a>
            <a href="{{ route('farmacia.carga') }}" class="menu-item @yield('nav-carga')"><i class="fas fa-upload"></i> Carga Masiva</a>
            <a href="{{ route('farmacia.movimientos') }}" class="menu-item @yield('nav-movimientos')"><i class="fas fa-clipboard-list"></i> Movimientos</a>
        </div>
        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesion</button>
            </form>
        </div>
    </div>
    <div class="main">
        <div class="topbar">
            <h1>@yield('title', 'Panel de Farmacia')</h1>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-weight: 600; font-size: 0.9rem;">{{ auth()->user()->name }}</span>
                <span class="user-badge">{{ auth()->user()->role }}</span>
            </div>
        </div>
        <div class="content">
            @yield('content')
        </div>
    </div>
</body>
</html>
