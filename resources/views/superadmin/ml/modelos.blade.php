@extends('superadmin.layout')
@section('title', 'Modelos ML')
@section('nav-ml-modelos', 'active')

@section('content')
<div style="background:white;border-radius:14px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:4px solid #7C3AED;margin-bottom:1.5rem;">
    <h3 style="font-weight:900;color:#1E1A17;margin-bottom:0.5rem;"><i class="fas fa-sitemap" style="color:#7C3AED"></i> Catalogo de Algoritmos</h3>
    <p style="font-size:0.8rem;color:#78716C;margin-bottom:1.5rem;">Modelos disponibles para el sistema predictivo - Basado en tus notas de clase</p>
    
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <!-- Regresion Logistica -->
        <div style="border:1px solid #E5E7EB;border-radius:10px;padding:1.2rem;border-left:4px solid #7C3AED;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                <h4 style="font-weight:800;color:#1E1A17;font-size:0.95rem;">Regresion Logistica</h4>
                <span style="background:#F0FDF4;color:#16A34A;padding:0.15rem 0.5rem;border-radius:10px;font-size:0.65rem;font-weight:800;">ACTIVO</span>
            </div>
            <p style="font-size:0.78rem;color:#78716C;margin-bottom:0.8rem;">Predice probabilidad de clase (0-1). Ideal para mortalidad si/no. Formula: P(Y=1) = 1 / (1 + e<sup>-(b0 + b1X1 + ...)</sup>)</p>
            <div style="display:flex;gap:0.5rem;">
                <span style="background:#F5F3FF;color:#7C3AED;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.65rem;font-weight:700;">Clasificacion</span>
                <span style="background:#FFF7ED;color:#EA580C;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.65rem;font-weight:700;">Binaria</span>
            </div>
        </div>

        <!-- Arbol de Decision -->
        <div style="border:1px solid #E5E7EB;border-radius:10px;padding:1.2rem;border-left:4px solid #F97316;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                <h4 style="font-weight:800;color:#1E1A17;font-size:0.95rem;">Arbol de Decision</h4>
                <span style="background:#F0FDF4;color:#16A34A;padding:0.15rem 0.5rem;border-radius:10px;font-size:0.65rem;font-weight:800;">ACTIVO</span>
            </div>
            <p style="font-size:0.78rem;color:#78716C;margin-bottom:0.8rem;">Particion recursiva: Raiz → Nodos internos → Hojas. Criterios: Entropia o Indice Gini. Gini = 1 - Sum(p<sub>i</sub><sup>2</sup>)</p>
            <div style="display:flex;gap:0.5rem;">
                <span style="background:#F5F3FF;color:#7C3AED;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.65rem;font-weight:700;">Clasificacion</span>
                <span style="background:#EFF6FF;color:#2563EB;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.65rem;font-weight:700;">Interpretable</span>
            </div>
        </div>

        <!-- Random Forest -->
        <div style="border:1px solid #E5E7EB;border-radius:10px;padding:1.2rem;border-left:4px solid #059669;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                <h4 style="font-weight:800;color:#1E1A17;font-size:0.95rem;">Random Forest (Bosque Aleatorio)</h4>
                <span style="background:#FFF7ED;color:#EA580C;padding:0.15rem 0.5rem;border-radius:10px;font-size:0.65rem;font-weight:800;">ENTRENANDO</span>
            </div>
            <p style="font-size:0.78rem;color:#78716C;margin-bottom:0.8rem;">Ensemble de multiples arboles. Mayor precision, menor overfitting. Cada arbol ve un subconjunto diferente de datos.</p>
            <div style="display:flex;gap:0.5rem;">
                <span style="background:#F5F3FF;color:#7C3AED;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.65rem;font-weight:700;">Ensemble</span>
                <span style="background:#FEF2F2;color:#DC2626;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.65rem;font-weight:700;">Alta Precision</span>
            </div>
        </div>

        <!-- SVM -->
        <div style="border:1px solid #E5E7EB;border-radius:10px;padding:1.2rem;border-left:4px solid #2563EB;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                <h4 style="font-weight:800;color:#1E1A17;font-size:0.95rem;">SVM (Maquinas de Soporte Vectorial)</h4>
                <span style="background:#F5F5F5;color:#78716C;padding:0.15rem 0.5rem;border-radius:10px;font-size:0.65rem;font-weight:800;">DISPONIBLE</span>
            </div>
            <p style="font-size:0.78rem;color:#78716C;margin-bottom:0.8rem;">Encuentra el hiperplano optimo que maximiza la distancia entre clases. Lineal o no lineal (kernel RBF, polinomial).</p>
            <div style="display:flex;gap:0.5rem;">
                <span style="background:#F5F3FF;color:#7C3AED;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.65rem;font-weight:700;">Clasificacion</span>
                <span style="background:#FFF7ED;color:#EA580C;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.65rem;font-weight:700;">Margen Maximo</span>
            </div>
        </div>

        <!-- Regresion Lineal -->
        <div style="border:1px solid #E5E7EB;border-radius:10px;padding:1.2rem;border-left:4px solid #DC2626;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                <h4 style="font-weight:800;color:#1E1A17;font-size:0.95rem;">Regresion Lineal / Multiple</h4>
                <span style="background:#F5F5F5;color:#78716C;padding:0.15rem 0.5rem;border-radius:10px;font-size:0.65rem;font-weight:800;">DISPONIBLE</span>
            </div>
            <p style="font-size:0.78rem;color:#78716C;margin-bottom:0.8rem;">Predice valores numericos (costos, dias). Y = b0 + b1X1 + b2X2 + ... Metricas: MSE, RMSE, MAE.</p>
            <div style="display:flex;gap:0.5rem;">
                <span style="background:#EFF6FF;color:#2563EB;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.65rem;font-weight:700;">Regresion</span>
                <span style="background:#FEF2F2;color:#DC2626;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.65rem;font-weight:700;">Costos</span>
            </div>
        </div>

        <!-- Regresion Lineal Multiple -->
        <div style="border:1px solid #E5E7EB;border-radius:10px;padding:1.2rem;border-left:4px solid #0284C7;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">
                <h4 style="font-weight:800;color:#1E1A17;font-size:0.95rem;">Regresion Multiple (Ventas/Publicidad)</h4>
                <span style="background:#F5F5F5;color:#78716C;padding:0.15rem 0.5rem;border-radius:10px;font-size:0.65rem;font-weight:800;">DISPONIBLE</span>
            </div>
            <p style="font-size:0.78rem;color:#78716C;margin-bottom:0.8rem;">Extension de regresion lineal con multiples variables independientes. Ideal para predecir costos con muchas variables.</p>
            <div style="display:flex;gap:0.5rem;">
                <span style="background:#EFF6FF;color:#2563EB;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.65rem;font-weight:700;">Regresion</span>
                <span style="background:#F0FDF4;color:#16A34A;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.65rem;font-weight:700;">Multivariable</span>
            </div>
        </div>
    </div>
</div>
@endsection
