<?php
// 1. REPARAR HOSPITALIZADOS (Quitar el código roto que movió el menú)
 $h_file = 'resources/views/especialidades/hospitalizados.blade.php';
 $h = file_get_contents($h_file);

// Eliminar el bloque @php que inyectamos mal al inicio
 $h = preg_replace('/@php \$pendientes = .*? @endphp\s*/s', '', $h);

// Parchear variables de forma segura SIN romper el layout
 $h = str_replace(['{{ $pendientes', '@foreach($pendientes'], ['{{ ($pendientes ?? [])', '@foreach(($pendientes ?? [])'], $h);
 $h = str_replace(['{{ $todosMedicos', '@foreach($todosMedicos'], ['{{ ($todosMedicos ?? [])', '@foreach(($todosMedicos ?? [])'], $h);
 $h = str_replace(['{{ $camas', '@foreach($camas'], ['{{ ($camas ?? [])', '@foreach(($camas ?? [])'], $h);

file_put_contents($h_file, $h);
echo "1. Hospitalizados reparado (El menú vuelve a la izquierda).\n";

// 2. CAMBIAR COLORES DEL MENÚ LATERAL (De Azul a Naranja)
 $layout_files = [
    'resources/views/layouts/medico.blade.php',
    'resources/views/especialidades/layout.blade.php',
    'resources/views/layouts/app.blade.php' 
];

 $azules_hex = [
    '#EFF6FF' => '#FFF7ED', '#DBEAFE' => '#FFEDD5', '#BFDBFE' => '#FED7AA',
    '#93C5FD' => '#FDBA74', '#60A5FA' => '#FB923C', '#3B82F6' => '#F97316',
    '#2563EB' => '#EA580C', '#1D4ED8' => '#C2410C', '#1E40AF' => '#9A3412',
    '#1E3A8A' => '#7C2D12'
];

foreach ($layout_files as $file) {
    if (file_exists($file)) {
        $c = file_get_contents($file);
        $original = $c;
        // Cambiar colores hexadecimales
        $c = str_ireplace($azules_hex, $c);
        // Cambiar clases de Tailwind (bg-blue-500 -> bg-orange-500, etc)
        $c = preg_replace('/\b(bg|text|border|ring|from|to|via|divide|placeholder|focus|hover|active)-blue-(50|100|200|300|400|500|600|700|800|900)\b/i', '$1-orange-$2', $c);
        
        if ($c !== $original) {
            file_put_contents($file, $c);
            echo "2. Menú lateral actualizado a Naranja en: $file\n";
        }
    }
}
echo "¡Listo! Recarga para ver los cambios.\n";
?>
