<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PhenotypeDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('vital_signs')->truncate();
        DB::table('dispensations')->truncate();
        DB::table('lab_studies')->truncate();
        DB::table('imaging_studies')->truncate();
        DB::table('prescriptions')->truncate();
        DB::table('admissions')->truncate();
        DB::table('patients')->truncate();
        DB::table('beds')->truncate();
        DB::table('staff')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ===== STAFF =====
        $staffData = [
            ['user_id' => 1, 'role' => 'Médico A', 'name' => 'Dr. Roberto Méndez', 'level' => 'A', 'specialty' => 'Medicina Interna', 'experience_years' => 15, 'status' => 'active', 'is_verified' => 1, 'documents_complete' => 1],
            ['user_id' => 2, 'role' => 'Médico B', 'name' => 'Dra. Laura Patiño', 'level' => 'B', 'specialty' => 'Cirugía General', 'experience_years' => 10, 'status' => 'active', 'is_verified' => 1, 'documents_complete' => 1],
            ['user_id' => 3, 'role' => 'Médico C', 'name' => 'Dr. Carlos Vega', 'level' => 'C', 'specialty' => 'Pediatría', 'experience_years' => 5, 'status' => 'active', 'is_verified' => 1, 'documents_complete' => 1],
            ['user_id' => 4, 'role' => 'Enfermera', 'name' => 'Enf. Patricia López', 'level' => 'A', 'specialty' => 'UCI', 'experience_years' => 8, 'status' => 'active', 'is_verified' => 1, 'documents_complete' => 1],
            ['user_id' => 5, 'role' => 'Enfermera', 'name' => 'Enf. Gabriela Ruiz', 'level' => 'B', 'specialty' => 'General', 'experience_years' => 4, 'status' => 'active', 'is_verified' => 1, 'documents_complete' => 1],
            ['user_id' => 6, 'role' => 'Farmacéutico', 'name' => 'QFB. Miguel Ángel Torres', 'level' => 'A', 'specialty' => 'Farmacia Hospitalaria', 'experience_years' => 12, 'status' => 'active', 'is_verified' => 1, 'documents_complete' => 1],
        ];
        foreach ($staffData as &$s) {
            $s['cedula_profesional'] = 'CED-' . rand(100000, 999999);
            $s['trust_score'] = rand(80, 100);
            $s['created_at'] = now();
            $s['updated_at'] = now();
        }
        DB::table('staff')->insert($staffData);
        $staffIds = DB::table('staff')->pluck('id')->toArray();
        $doctorIds = DB::table('staff')->whereIn('role', ['Médico A', 'Médico B', 'Médico C'])->pluck('id')->toArray();
        $nurseIds = DB::table('staff')->where('role', 'Enfermera')->pluck('id')->toArray();

        // ===== CAMAS =====
        $beds = [];
        $zones = ['General', 'General', 'General', 'Pediatría', 'UCI', 'UCI', 'Quirófano', 'Observación'];
        for ($i = 1; $i <= 30; $i++) {
            $zone = $zones[array_rand($zones)];
            $beds[] = [
                'code' => 'CAM-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'room_number' => str_pad(ceil($i / 2), 2, '0', STR_PAD_LEFT),
                'bed_number' => ($i % 2 === 0) ? 'B' : 'A',
                'status' => 'disponible',
                'section' => $zone,
                'floor' => $zone === 'UCI' ? '2' : '1',
                'zone' => $zone,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('beds')->insert($beds);
        $bedIds = DB::table('beds')->pluck('id')->toArray();

        // ===== NOMBRES Y DATOS =====
        $nombres = [
            'María García', 'Juan Hernández', 'Carmen Martínez', 'José Rodríguez',
            'Ana López', 'Miguel Torres', 'Rosa Flores', 'Pedro Jiménez',
            'Laura Ramírez', 'Carlos Mendoza', 'Sofia Vargas', 'Fernando Morales',
            'Patricia Guzmán', 'Roberto Sánchez', 'Gabriela Medina', 'Alberto Paredes',
            'Diana Estrada', 'Rafael Campos', 'Isabel Reyes', 'Eduardo Fuentes',
            'Silvia Rangel', 'Óscar Barrios', 'Andrea Villanueva', 'Diego Huerta',
            'Verónica Soto', 'Marco Ayala', 'Leticia Duarte', 'Héctor Gallegos',
            'Natalia Ochoa', 'Ricardo Peña'
        ];

        $diagnosticos = [
            'Neumonía adquirida en comunidad', 'Insuficiencia cardíaca aguda',
            'Infarto agudo al miocardio', 'Accidente cerebrovascular isquémico',
            'EPOC agudizada', 'Infección urinaria complicada', 'Pancreatitis aguda',
            'Colecistitis aguda', 'Fractura de cadera', 'Neumotórax',
            'Sepsis de origen abdominal', 'Insuficiencia renal aguda',
            'Crisis hipertensiva', 'Diabetes descompensada', 'Tromboembolia pulmonar'
        ];

        $medicamentos = [
            'Amoxicilina 500mg', 'Omeprazol 20mg', 'Metformina 850mg',
            'Enalapril 10mg', 'Amlodipino 5mg', 'Atorvastatina 20mg',
            'Ibuprofeno 400mg', 'Paracetamol 500mg', 'Diclofenaco 50mg',
            'Ceftriaxona 1g IV', 'Azitromicina 500mg', 'Pantoprazol 40mg IV',
            'Furosemida 40mg IV', 'Heparina 5000UI SC', 'Clopidogrel 75mg',
            'Metoprolol 50mg', 'Insulina Glargina', 'Salbutamol neb',
            'Prednisona 20mg', 'Tramadol 50mg', 'Morfina 10mg IV',
            'Dopamina 200mg IV', 'Noradrenalina 4mg IV', 'Midazolam 5mg IV',
            'Vancomicina 1g IV', 'Meropenem 1g IV', 'Linezolid 600mg IV'
        ];

        $labNames = ['Biometría hemática', 'Química sanguínea', 'Perfil hepático', 'PCR cuantitativa', 'Procalcitonina', 'Gasometría arterial', 'Cultivo de sangre', 'Urianálisis', 'Electrolitos séricos', 'Coagulación'];
        $imgNames = ['Rayos X de tórax', 'TAC de tórax', 'TAC de abdomen', 'Rx abdomen simple', 'Ecografía abdominal', 'Resonancia magnética', 'Ecodoppler vascular', 'TAC de cráneo'];

        // ===== FENOTIPOS =====
        $fenotypes = [
            [
                'name' => 'CRÓNICO COMPLEJO', 'color' => 'rojo', 'pct' => 0.20,
                'estancia_hrs' => [240, 720], 'fc_mean' => [85, 110], 'fc_std' => [15, 35],
                'temp_mean' => [37.5, 39.0], 'temp_std' => [0.8, 2.0],
                'n_meds' => [8, 15], 'n_vitals' => [20, 45], 'n_labs' => [8, 15],
                'n_img' => [3, 6], 'n_disp' => [6, 12], 'triage' => ['rojo', 'negro'], 'zone' => 'UCI',
            ],
            [
                'name' => 'RESPONDEDOR RÁPIDO', 'color' => 'verde', 'pct' => 0.40,
                'estancia_hrs' => [8, 48], 'fc_mean' => [70, 85], 'fc_std' => [2, 6],
                'temp_mean' => [36.5, 37.2], 'temp_std' => [0.1, 0.4],
                'n_meds' => [1, 3], 'n_vitals' => [2, 5], 'n_labs' => [1, 3],
                'n_img' => [0, 2], 'n_disp' => [1, 3], 'triage' => ['verde', 'amarillo'], 'zone' => 'Observación',
            ],
            [
                'name' => 'INESTABLE OCULTO', 'color' => 'amarillo', 'pct' => 0.25,
                'estancia_hrs' => [72, 240], 'fc_mean' => [80, 100], 'fc_std' => [20, 40],
                'temp_mean' => [37.0, 38.5], 'temp_std' => [1.0, 2.5],
                'n_meds' => [4, 8], 'n_vitals' => [12, 25], 'n_labs' => [4, 8],
                'n_img' => [1, 3], 'n_disp' => [3, 7], 'triage' => ['amarillo', 'rojo'], 'zone' => 'General',
            ],
            [
                'name' => 'ESTABLE LARGO', 'color' => 'azul', 'pct' => 0.15,
                'estancia_hrs' => [168, 480], 'fc_mean' => [68, 80], 'fc_std' => [1, 4],
                'temp_mean' => [36.4, 36.9], 'temp_std' => [0.05, 0.3],
                'n_meds' => [2, 4], 'n_vitals' => [6, 12], 'n_labs' => [2, 4],
                'n_img' => [1, 2], 'n_disp' => [1, 3], 'triage' => ['verde', 'amarillo'], 'zone' => 'General',
            ],
        ];

        // ===== GENERAR 200 PACIENTES =====
        $totalPacientes = 200;
        $fenotypeCounter = ['rojo' => 0, 'verde' => 0, 'amarillo' => 0, 'azul' => 0];

        for ($i = 0; $i < $totalPacientes; $i++) {
            $fenotype = $this->pickFenotype($fenotypes, $i, $totalPacientes);
            $fenotypeCounter[$fenotype['color']]++;

            $age = $fenotype['color'] === 'verde' ? rand(18, 45) : rand(35, 89);
            $patientId = DB::table('patients')->insertGetId([
                'name' => $nombres[$i % count($nombres)] . ' ' . rand(1, 99),
                'status' => 'discharged',
                'blood_type' => ['O+', 'A+', 'B+', 'O-', 'A-'][array_rand(['O+', 'A+', 'B+', 'O-', 'A-'])],
                'is_verified' => true,
                'trust_score' => rand(60, 100),
                'triage_level' => $fenotype['triage'][array_rand($fenotype['triage'])],
                'symptoms' => $diagnosticos[array_rand($diagnosticos)],
                'entered_at' => Carbon::now()->subDays(rand(30, 120))->subHours(rand(0, 72)),
                'er_status' => 'processed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $zoneBeds = DB::table('beds')->where('zone', $fenotype['zone'])->pluck('id')->toArray();
            if (empty($zoneBeds)) $zoneBeds = $bedIds;
            $bedId = $zoneBeds[array_rand($zoneBeds)];

            $estanciaHrs = rand($fenotype['estancia_hrs'][0], $fenotype['estancia_hrs'][1]);
            $admissionDate = Carbon::now()->subHours($estanciaHrs)->subMinutes(rand(0, 120));
            $dischargeDate = $admissionDate->copy()->addHours($estanciaHrs);

            DB::table('admissions')->insertGetId([
                'patient_id' => $patientId,
                'bed_id' => $bedId,
                'staff_id' => $nurseIds[array_rand($nurseIds)],
                'assigned_doctor_id' => $doctorIds[array_rand($doctorIds)],
                'reason' => $diagnosticos[array_rand($diagnosticos)],
                'status' => 'discharged',
                'created_at' => $admissionDate,
                'updated_at' => $dischargeDate,
            ]);

            DB::table('beds')->where('id', $bedId)->update([
                'status' => 'ocupada', 'patient_id' => $patientId, 'occupied_at' => $admissionDate,
            ]);

            // SIGNOS VITALES
            $nVitals = rand($fenotype['n_vitals'][0], $fenotype['n_vitals'][1]);
            $fcMean = rand($fenotype['fc_mean'][0], $fenotype['fc_mean'][1]);
            $fcStd = rand($fenotype['fc_std'][0], $fenotype['fc_std'][1]);
            $tempMean = $this->randFloat($fenotype['temp_mean'][0], $fenotype['temp_mean'][1]);
            $tempStd = $this->randFloat($fenotype['temp_std'][0], $fenotype['temp_std'][1]);

            for ($v = 0; $v < $nVitals; $v++) {
                $vitalTime = $admissionDate->copy()->addHours(round(($estanciaHrs / max($nVitals, 1)) * $v));
                $fc = max(40, min(200, round($fcMean + $this->randGauss() * $fcStd)));
                $temp = max(34.0, min(42.0, round($tempMean + $this->randGauss() * $tempStd, 1)));
                $sys = max(80, min(200, round(120 + $this->randGauss() * 15)));
                $dia = max(40, min(120, round(80 + $this->randGauss() * 10)));

                DB::table('vital_signs')->insert([
                    'patient_id' => $patientId, 'staff_id' => $nurseIds[array_rand($nurseIds)],
                    'temperature' => $temp, 'heart_rate' => $fc, 'blood_pressure' => "$sys/$dia",
                    'created_at' => $vitalTime, 'updated_at' => $vitalTime,
                ]);
            }

            // PRESCRIPCIONES + DISPENSACIONES
            $nMeds = rand($fenotype['n_meds'][0], $fenotype['n_meds'][1]);
            $usedMeds = [];
            $nDisp = rand($fenotype['n_disp'][0], $fenotype['n_disp'][1]);
            $dispCount = 0;

            for ($m = 0; $m < $nMeds; $m++) {
                $med = $medicamentos[array_rand($medicamentos)];
                while (in_array($med, $usedMeds) && count($usedMeds) < count($medicamentos)) {
                    $med = $medicamentos[array_rand($medicamentos)];
                }
                $usedMeds[] = $med;

                $prescId = DB::table('prescriptions')->insertGetId([
                    'patient_id' => $patientId,
                    'medico_c_id' => $doctorIds[array_rand($doctorIds)],
                    'medicamento' => $med,
                    'dosis' => ['Cada 8hrs', 'Cada 12hrs', 'Cada 24hrs', 'Cada 6hrs', 'SOS'][array_rand(['Cada 8hrs', 'Cada 12hrs', 'Cada 24hrs', 'Cada 6hrs', 'SOS'])],
                    'status' => 'dispensed', 'in_cuadro_basico' => rand(0, 1),
                    'created_at' => $admissionDate->copy()->addMinutes(rand(10, 120)), 'updated_at' => now(),
                ]);

                if ($dispCount < $nDisp) {
                    DB::table('dispensations')->insert([
                        'prescription_id' => $prescId, 'staff_id' => $staffIds[5],
                        'status' => 'dispensed', 'received_by_name' => 'Enfermería',
                        'created_at' => $admissionDate->copy()->addHours(rand(1, 4)), 'updated_at' => now(),
                    ]);
                    $dispCount++;
                }
            }

            // LABORATORIO
            $nLabs = rand($fenotype['n_labs'][0], $fenotype['n_labs'][1]);
            $usedLabs = [];
            for ($l = 0; $l < $nLabs; $l++) {
                $lab = $labNames[array_rand($labNames)];
                while (in_array($lab, $usedLabs) && count($usedLabs) < count($labNames)) {
                    $lab = $labNames[array_rand($labNames)];
                }
                $usedLabs[] = $lab;
                DB::table('lab_studies')->insert([
                    'patient_id' => $patientId, 'study_name' => $lab,
                    'results' => json_encode(['hemoglobina' => rand(8, 16), 'leucocitos' => rand(4000, 15000), 'glucosa' => rand(70, 300)]),
                    'status' => 'completed',
                    'created_at' => $admissionDate->copy()->addHours(rand(1, max(1, (int)($estanciaHrs * 0.3)))), 'updated_at' => now(),
                ]);
            }

            // IMAGEN
            $nImg = rand($fenotype['n_img'][0], $fenotype['n_img'][1]);
            $usedImg = [];
            for ($im = 0; $im < $nImg; $im++) {
                $img = $imgNames[array_rand($imgNames)];
                while (in_array($img, $usedImg) && count($usedImg) < count($imgNames)) {
                    $img = $imgNames[array_rand($imgNames)];
                }
                $usedImg[] = $img;
                DB::table('imaging_studies')->insert([
                    'patient_id' => $patientId, 'study_name' => $img,
                    'results' => 'Hallazgos compatibles con cuadro clínico', 'status' => 'completed',
                    'created_at' => $admissionDate->copy()->addHours(rand(2, max(2, (int)($estanciaHrs * 0.5)))), 'updated_at' => now(),
                ]);
            }

            if (($i + 1) % 50 === 0) echo "Generados " . ($i + 1) . " pacientes...\n";
        }

        DB::table('beds')->update(['status' => 'disponible', 'patient_id' => null]);

        echo "\n✅ Datos de fenotipado generados:\n";
        foreach ($fenotypeCounter as $color => $count) {
            $name = $fenotypes[array_search($color, array_column($fenotypes, 'color'))]['name'];
            echo "  $color ($name): $count pacientes\n";
        }
        echo "  TOTAL: $totalPacientes pacientes\n";
    }

    private function pickFenotype(array $fenotypes, int $index, int $total): array
    {
        $cumulative = 0;
        foreach ($fenotypes as $i => $f) {
            $cumulative += $f['pct'];
            if (($index / $total) < $cumulative) return $fenotypes[$i];
        }
        return $fenotypes[count($fenotypes) - 1];
    }

    private function randFloat(float $min, float $max): float
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }

    private function randGauss(): float
    {
        $u1 = mt_rand() / mt_getrandmax();
        $u2 = mt_rand() / mt_getrandmax();
        return sqrt(-2 * log(max($u1, 0.0001))) * cos(2 * pi() * $u2);
    }
}
