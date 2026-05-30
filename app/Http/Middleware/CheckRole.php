<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Si no está logueado, redirigir al login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Si su rol no está en la lista de roles permitidos, acceso denegado
        if (!in_array(auth()->user()->role, $roles)) {
            abort(403, 'ACCESO DENEGADO. No tienes los permisos necesarios para ingresar a este módulo del hospital.');
        }

        return $next($request);
    }
}
