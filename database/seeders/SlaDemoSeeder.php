<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\ServiceLog;
use Carbon\Carbon;
class SlaDemoSeeder extends Seeder {
    public function run(): void {
        $data = [];
        // Generar 40 cirugías normales (30-60 mins) en horas normales (8am - 6pm)
        for ($i = 1; $i <= 40; $i++) {
            $hour = rand(8, 18);
            $duration = rand(30, 60);
            $startedAt = Carbon::now()->subDays(rand(0, 30))->setHour($hour)->setMinute(0);
            $endedAt = $startedAt->copy()->addMinutes($duration);
            $data[] = [
                'module' => 'quirofano', 'event_type' => 'cirugia', 'user_id' => null,
                'patient_identifier' => 'PAC-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'started_at' => $startedAt, 'ended_at' => $endedAt,
                'duration_minutes' => $duration, 'start_hour' => $hour,
                'status' => 'completed', 'is_outlier' => false, 'outlier_z_score' => null,
                'created_at' => now(), 'updated_at' => now()
            ];
        }
        // GENERAR 3 ANOMALÍAS (Ejemplo del apunte: 180 mins a las 3am)
        $anomalies = [
            ['day' => 5, 'hour' => 3, 'dur' => 180, 'pac' => 'PAC-ANOM-1'],
            ['day' => 15, 'hour' => 2, 'dur' => 190, 'pac' => 'PAC-ANOM-2'],
            ['day' => 25, 'hour' => 4, 'dur' => 175, 'pac' => 'PAC-ANOM-3'],
        ];
        foreach ($anomalies as $a) {
            $startedAt = Carbon::now()->subDays($a['day'])->setHour($a['hour'])->setMinute(0);
            $endedAt = $startedAt->copy()->addMinutes($a['dur']);
            $data[] = [
                'module' => 'quirofano', 'event_type' => 'cirugia', 'user_id' => null,
                'patient_identifier' => $a['pac'],
                'started_at' => $startedAt, 'ended_at' => $endedAt,
                'duration_minutes' => $a['dur'], 'start_hour' => $a['hour'],
                'status' => 'completed', 'is_outlier' => false, 'outlier_z_score' => null, // El controlador lo calcula
                'created_at' => now(), 'updated_at' => now()
            ];
        }
        ServiceLog::insert($data);
        $this->command->info('Datos de prueba del SLA insertados (40 normales + 3 anomalías a las 3am).');
    }
}
