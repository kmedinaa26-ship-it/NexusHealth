<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $userRole = strtolower(trim(auth()->user()->role));

        // Super Admin tiene acceso a TODO
        if (in_array($userRole, ['super admin', 'superadmin', 'administrador hospitalario', 'administrador'])) {
            return $next($request);
        }

        // Extraer la letra del rol (ej: "médico a" → "a", "enfermera b" → "b")
        $userGrade = $this->extractGrade($userRole);

        // Comparar contra cada rol permitido
        foreach ($roles as $role) {
            $roleLower = strtolower(trim($role));
            $roleGrade = $this->extractGrade($role);

            if ($userGrade === $roleGrade) {
                return $next($request);
            }

            // Si no tiene grado (ej: "enfermera", "farmacia"), comparar directo
            if ($roleGrade === '' && $userRole === $roleLower) {
                return $next($request);
            }
        }

        abort(403, 'No tienes permiso para acceder a esta pagina.');
    }

    /**
     * Extrae la letra del rol
     * "Médico A" → "a", "Enfermera B" → "b", "Farmacéutico" → ""
     */
    private function extractGrade(string $role): string
    {
        $role = strtolower(trim($role));

        // Si contiene letras sueltas al final (A, B, C, D), extraer la última
        if (preg_match('/\b([a-z])\s*$/', $role, $matches)) {
            return $matches[1];
        }

        // Si no tiene grado, devolver vacío
        return '';
    }
}
