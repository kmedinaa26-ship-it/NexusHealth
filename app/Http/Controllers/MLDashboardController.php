<?php

namespace App\Http\Controllers;

use App\Services\ML\RegressionService;
use App\Services\ML\MetricsService;
use App\Models\Invoice;
use App\Models\Triage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MLDashboardController extends Controller
{
    public function index()
    {
        $regressionService = new RegressionService();
        $metricsService = new MetricsService();

        // =============================================
        // 1. DATOS DIARIOS DESDE INVOICES
        // =============================================
        $dailyInvoices = Invoice::selectRaw("
            DATE(created_at) as fecha,
            SUM(CASE WHEN concept='Medicamentos' THEN amount ELSE 0 END) as medicamentos,
            SUM(CASE WHEN concept='UCI' THEN amount ELSE 0 END) as hospitalizacion,
            SUM(CASE WHEN concept='Cirugia' THEN amount ELSE 0 END) as quirofano,
            SUM(CASE WHEN concept='Consulta Urgencias' THEN amount ELSE 0 END) as consulta,
            SUM(amount) as costo_total,
            COUNT(DISTINCT patient_name) as pacientes_atendidos
        ")->groupBy('fecha')->orderBy('fecha')->get()->keyBy('fecha');

        $dailyTriages = Triage::selectRaw("
            DATE(created_at) as fecha,
            COUNT(*) as total_triages,
            SUM(CASE WHEN triage_level='Rojo' THEN 1 ELSE 0 END) as criticos
        ")->groupBy('fecha')->orderBy('fecha')->get()->keyBy('fecha');

        $allDates = collect(array_unique(array_merge(
            $dailyInvoices->keys()->toArray(),
            $dailyTriages->keys()->toArray()
        )))->sort();

        $ds = [];
        foreach ($allDates as $date) {
            $inv = $dailyInvoices->get($date);
            $tri = $dailyTriages->get($date);
            $c = Carbon::parse($date);
            $ds[] = [
                'fecha' => $date,
                'med' => $inv ? (float)$inv->medicamentos : 0,
                'hosp' => $inv ? (float)$inv->hospitalizacion : 0,
                'quir' => $inv ? (float)$inv->quirofano : 0,
                'cons' => $inv ? (float)$inv->consulta : 0,
                'total' => $inv ? (float)$inv->costo_total : 0,
                'pac' => $inv ? (int)$inv->pacientes_atendidos : ($tri ? (int)$tri->total_triages : 0),
                'crit' => $tri ? (int)$tri->criticos : 0,
                'dow' => $c->dayOfWeek,
                'we' => in_array($c->dayOfWeek, [0,6]) ? 1 : 0,
            ];
        }

        $med = array_column($ds, 'med');
        $hosp = array_column($ds, 'hosp');
        $quir = array_column($ds, 'quir');
        $cons = array_column($ds, 'cons');
        $total = array_column($ds, 'total');
        $fechas = array_column($ds, 'fecha');
        $pacientes = array_column($ds, 'pac');
        $n = count($ds);

        // =============================================
        // 2. MODELO ACTUAL (mismo dia) Train/Test 80/20
        // =============================================
        $sp = (int) floor($n * 0.8);

        $model = $regressionService->multipleRegression(
            array_slice($med, 0, $sp),
            array_slice($hosp, 0, $sp),
            array_slice($quir, 0, $sp),
            array_slice($total, 0, $sp)
        );

        $tMed = array_slice($med, $sp);
        $tHosp = array_slice($hosp, $sp);
        $tQuir = array_slice($quir, $sp);
        $tY = array_slice($total, $sp);
        $tFechas = array_slice($fechas, $sp);
        $tCons = array_slice($cons, $sp);

        $tPred = []; $tErr = [];
        for ($i = 0; $i < count($tY); $i++) {
            $yh = $model['beta']['b0'] + $model['beta']['b1']*$tMed[$i] + $model['beta']['b2']*$tHosp[$i] + $model['beta']['b3']*$tQuir[$i];
            $tPred[] = $yh; $tErr[] = abs($tY[$i] - $yh);
        }
        $tMSE = $regressionService->mse($tY, $tPred);
        $tRMSE = $regressionService->rmse($tY, $tPred);
        $tMAE = $regressionService->mae($tY, $tPred);
        $tYm = array_sum($tY) / max(count($tY),1);
        $ssR=0; $ssT=0;
        for ($i=0;$i<count($tY);$i++) { $ssR+=pow($tY[$i]-$tPred[$i],2); $ssT+=pow($tY[$i]-$tYm,2); }
        $tR2 = $ssT > 0 ? 1-($ssR/$ssT) : 0;

        $tabla = [];
        for ($i=0;$i<count($tY);$i++) $tabla[] = [
            'fecha'=>$tFechas[$i],'med'=>$tMed[$i],'hosp'=>$tHosp[$i],
            'quir'=>$tQuir[$i],'cons'=>$tCons[$i],'real'=>$tY[$i],
            'pred'=>$tPred[$i],'error'=>$tErr[$i],
        ];

        // =============================================
        // 3. MODELO PREDICTIVO (LAG) - dia anterior
        // =============================================
        $lMed = array_slice($med, 0, -1);
        $lHosp = array_slice($hosp, 0, -1);
        $lQuir = array_slice($quir, 0, -1);
        $lY = array_slice($total, 1);
        $lFechas = array_slice($fechas, 1);
        $lN = count($lY);
        $lSp = (int) floor($lN * 0.8);

        $lagModel = $regressionService->multipleRegression(
            array_slice($lMed, 0, $lSp), array_slice($lHosp, 0, $lSp),
            array_slice($lQuir, 0, $lSp), array_slice($lY, 0, $lSp)
        );

        $ltMed = array_slice($lMed, $lSp);
        $ltHosp = array_slice($lHosp, $lSp);
        $ltQuir = array_slice($lQuir, $lSp);
        $ltY = array_slice($lY, $lSp);
        $ltFechas = array_slice($lFechas, $lSp);

        $ltPred=[]; $ltErr=[];
        for ($i=0;$i<count($ltY);$i++) {
            $yh = $lagModel['beta']['b0']+$lagModel['beta']['b1']*$ltMed[$i]+$lagModel['beta']['b2']*$ltHosp[$i]+$lagModel['beta']['b3']*$ltQuir[$i];
            $ltPred[]=$yh; $ltErr[]=abs($ltY[$i]-$yh);
        }
        $lMSE = $regressionService->mse($ltY, $ltPred);
        $lRMSE = $regressionService->rmse($ltY, $ltPred);
        $lMAE = $regressionService->mae($ltY, $ltPred);
        $lYm = array_sum($ltY)/max(count($ltY),1);
        $lsR=0;$lsT=0;
        for ($i=0;$i<count($ltY);$i++){ $lsR+=pow($ltY[$i]-$ltPred[$i],2);$lsT+=pow($ltY[$i]-$lYm,2);}
        $lR2 = $lsT>0 ? 1-($lsR/$lsT) : 0;

        $lagTabla = [];
        for ($i=0;$i<count($ltY);$i++) $lagTabla[] = [
            'fecha'=>$ltFechas[$i],'real'=>$ltY[$i],'pred'=>$ltPred[$i],'error'=>$ltErr[$i],
        ];

        // =============================================
        // 4. PREDICCION FUTURA 7 DIAS
        // =============================================
        $last = end($ds);
        $futuro = [];
        $lm = (float)$last['med']; $lh = (float)$last['hosp']; $lq = (float)$last['quir'];
        for ($d=1;$d<=7;$d++) {
            $fd = Carbon::parse($last['fecha'])->addDays($d);
            $pred = $lagModel['beta']['b0']+$lagModel['beta']['b1']*$lm+$lagModel['beta']['b2']*$lh+$lagModel['beta']['b3']*$lq;
            $futuro[] = ['fecha'=>$fd->format('Y-m-d'),'pred'=>$pred,'dia'=>$fd->locale('es')->dayName];
        }

        // =============================================
        // 5. EXPLICABILIDAD
        // =============================================
        $mMed = array_sum($med)/$n;
        $mHosp = array_sum($hosp)/$n;
        $mQuir = array_sum($quir)/$n;
        $cMed = abs($model['beta']['b1']*$mMed);
        $cHosp = abs($model['beta']['b2']*$mHosp);
        $cQuir = abs($model['beta']['b3']*$mQuir);
        $cTotal = $cMed+$cHosp+$cQuir;
        $explicabilidad = [
            ['concepto'=>'Medicamentos','pct'=>$cTotal>0?round(($cMed/$cTotal)*100,1):0,'color'=>'#2D9E6A'],
            ['concepto'=>'Hospitalizacion','pct'=>$cTotal>0?round(($cHosp/$cTotal)*100,1):0,'color'=>'#FF8C42'],
            ['concepto'=>'Quirofano','pct'=>$cTotal>0?round(($cQuir/$cTotal)*100,1):0,'color'=>'#F05A4E'],
        ];

        // =============================================
        // 6. ALERTAS
        // =============================================
        $alertas = [];
        $avgCost = array_sum($total)/$n;
        $lastCost = (float)$last['total'];

        if ($lastCost > $avgCost * 1.5) {
            $alertas[] = ["tipo"=>"critico","icono"=>"fa-exclamation-triangle","titulo"=>"Costo diario excedido",
                "msg"=>"El costo del ultimo dia ($" . number_format($lastCost,0) . ") supera 1.5x el promedio ($" . number_format($avgCost,0) . ")."];
        }
        $last5Err = array_slice($tErr, -5);
        foreach ($last5Err as $idx => $err) {
            if ($err > $tMAE * 2) {
                $di = count($tErr) - 5 + $idx;
                $fechaStr = isset($tFechas[$di]) ? $tFechas[$di] : "N/A";
                $alertas[] = ["tipo"=>"advertencia","icono"=>"fa-chart-line","titulo"=>"Error de prediccion alto",
                    "msg"=>"El " . $fechaStr . " tuvo error de $" . number_format($err,0) . ", superior a 2x MAE."];
            }
        }
        if ($n >= 14) {
            $w1 = array_sum(array_slice($total,-7));
            $w2 = array_sum(array_slice($total,-14,7));
            if ($w2 > 0 && (($w1-$w2)/$w2) > 0.2) {
                $alertas[] = ["tipo"=>"advertencia","icono"=>"fa-arrow-trend-up","titulo"=>"Incremento semanal >20%",
                    "msg"=>"Esta semana: $" . number_format($w1,0) . " vs anterior: $" . number_format($w2,0) . "."];
            }
        }
        if (empty($alertas)) {
            $alertas[] = ["tipo"=>"ok","icono"=>"fa-check-circle","titulo"=>"Sin alertas","msg"=>"Todos los indicadores en rango normal."];
        }
// =============================================
        // 7. CLASIFICACION RIESGO FINANCIERO
        // =============================================
        $pc = Invoice::selectRaw("patient_name, SUM(amount) as tc, GROUP_CONCAT(DISTINCT concept) as cx")
            ->groupBy('patient_name')->orderByDesc('tc')->get();
        $costs = $pc->pluck('tc')->map(fn($v)=>(float)$v)->toArray();
        sort($costs);
        $p25 = $costs[(int)floor(count($costs)*0.25)] ?? 0;
        $p75 = $costs[(int)floor(count($costs)*0.75)] ?? 0;
        $reales=[]; $predichos=[];
        foreach ($pc as $p) {
            $t = (float)$p->tc; $cx = $p->cx ?? '';
            $reales[] = $t >= $p75 ? 1 : 0;
            $predichos[] = (str_contains($cx,'UCI') || str_contains($cx,'Cirugia')) ? 1 : 0;
        }
        $matriz = $metricsService->confusionMatrix($predichos, $reales);
        $classM = [
            'accuracy'=>$metricsService->accuracy($matriz),
            'precision'=>$metricsService->precision($matriz),
            'recall'=>$metricsService->recall($matriz),
            'f1'=>$metricsService->f1Score($matriz),
        ];

        // =============================================
        // 8. ESTADISTICAS POR CONCEPTO
        // =============================================
        $cMap = ['Medicamentos'=>'Medicamentos','Consulta Urgencias'=>'Consulta','Cirugia'=>'Quirofano','UCI'=>'Hospitalizacion'];
        $stats = Invoice::selectRaw("concept, COUNT(*) as total, SUM(amount) as suma, ROUND(AVG(amount),2) as promedio, MAX(amount) as maximo, MIN(amount) as minimo")
            ->groupBy('concept')->orderByDesc('suma')->get()->map(fn($i)=>[
                'concepto'=>$cMap[$i->concept]??$i->concept,'total'=>$i->total,
                'suma'=>(float)$i->suma,'promedio'=>(float)$i->promedio,
                'maximo'=>(float)$i->maximo,'minimo'=>(float)$i->minimo,
            ])->values();

        // =============================================
        // 9. DATOS PARA GRAFICOS (JSON)
        // =============================================
        $chartRP = [];
        for ($i=0;$i<count($tFechas);$i++) $chartRP[] = ['f'=>substr($tFechas[$i],5),'r'=>round($tY[$i]),'p'=>round($tPred[$i])];
        $chartErr = [];
        for ($i=0;$i<count($tFechas);$i++) $chartErr[] = ['f'=>substr($tFechas[$i],5),'e'=>round($tErr[$i]),'m'=>round($tMAE)];
        $weekly = []; $tw=[]; $wn=0;
        foreach ($ds as $idx=>$d) { $tw[]=$d['total']; if(count($tw)==7||$idx==$n-1){ $weekly[]=['s'=>'S'.++$wn,'t'=>round(array_sum($tw)),'a'=>round(array_sum($tw)/count($tw))]; $tw=[]; } }

        $mlFeeds = \App\Models\MlFeed::latest()->take(15)->get();
        $lastTraining = now()->format('d/m/Y H:i:s');

        return view('superadmin.ml-dashboard.index', compact(
            'model','tMSE','tRMSE','tMAE','tR2','tabla','matriz','classM','stats',
            'sp','n','p25','p75','avgCost',
            'lagModel','lMSE','lRMSE','lMAE','lR2','lagTabla','futuro',
            'explicabilidad','alertas','lastTraining',
            'chartRP','chartErr','weekly','mlFeeds'
        ));
    }

    public function uploadData(Request $request)
    {
        $request->validate(["archivo" => "required|file|mimes:csv,txt|max:2048"]);
        $path = $request->file("archivo")->getRealPath();
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (count($lines) < 2) {
            return back()->with("ml_error", "El archivo esta vacio o solo tiene encabezado.");
        }
        $header = str_getcsv(array_shift($lines));
        $hMap = [];
        foreach ($header as $i => $h) {
            $h = strtolower(trim($h));
            if (str_contains($h, "nombre") || str_contains($h, "patient") || str_contains($h, "paciente")) $hMap["patient_name"] = $i;
            elseif (str_contains($h, "concept") || str_contains($h, "tipo")) $hMap["concept"] = $i;
            elseif (str_contains($h, "amount") || str_contains($h, "monto") || str_contains($h, "costo") || str_contains($h, "importe")) $hMap["amount"] = $i;
            elseif (str_contains($h, "status") || str_contains($h, "estado")) $hMap["status"] = $i;
        }
        $validConcepts = ["Medicamentos", "Consulta Urgencias", "Cirugia", "UCI"];
        $validStatus = ["Pagado", "Pendiente", "Seguro", "Vencido"];
        $inserted = 0; $errors = 0; $errorLines = [];
        foreach ($lines as $ln => $line) {
            $cols = str_getcsv($line);
            $name = isset($hMap["patient_name"]) ? trim($cols[$hMap["patient_name"]] ?? "") : "Paciente Importado";
            $concept = isset($hMap["concept"]) ? trim($cols[$hMap["concept"]] ?? "") : "";
            $amount = isset($hMap["amount"]) ? floatval(preg_replace("/[^0-9.]/", "", $cols[$hMap["amount"]] ?? "0")) : 0;
            $status = isset($hMap["status"]) ? trim($cols[$hMap["status"]] ?? "Pendiente") : "Pendiente";
            if (empty($name) || empty($concept) || $amount <= 0) {
                $errors++; $errorLines[] = "Linea " . ($ln + 2) . ": datos incompletos";
                continue;
            }
            if (!in_array($concept, $validConcepts)) {
                $errors++; $errorLines[] = "Linea " . ($ln + 2) . ": concepto invalido ($concept)";
                continue;
            }
            if (!in_array($status, $validStatus)) $status = "Pendiente";
            \App\Models\Invoice::create([
                "patient_name" => substr($name, 0, 200),
                "concept" => $concept,
                "amount" => $amount,
                "status" => $status,
            ]);
            $inserted++;
        }
        $msg = "Se insertaron $inserted registros correctamente.";
        if ($errors > 0) {
            $msg .= " $errors errores: " . implode("; ", array_slice($errorLines, 0, 5));
            if ($errors > 5) $msg .= " ...y " . ($errors - 5) . " mas.";
            return back()->with("ml_warning", $msg);
        }
        return back()->with("ml_success", $msg);
    }

    public function generateDemo()
    {
        $names = ["Carlos Mendoza","Ana Rodriguez","Pedro Jimenez","Laura Martinez","Miguel Hernandez","Sofia Lopez","Diego Garcia","Elena Torres","Fernando Ramirez","Carmen Diaz"];
        $concepts = ["Medicamentos","Consulta Urgencias","Cirugia","UCI"];
        $ranges = ["Medicamentos"=>[500,15000],"Consulta Urgencias"=>[500,3000],"Cirugia"=>[15000,60000],"UCI"=>[8000,25000]];
        $statuses = ["Pagado","Pendiente","Seguro","Vencido"];
        $count = 0;
        for ($d = 0; $d < 30; $d++) {
            $date = now()->subDays(29 - $d)->toDateString();
            $n = rand(8, 25);
            for ($i = 0; $i < $n; $i++) {
                $concept = $concepts[array_rand($concepts)];
                $r = $ranges[$concept];
                \App\Models\Invoice::create([
                    "patient_name" => $names[array_rand($names)],
                    "concept" => $concept,
                    "amount" => rand($r[0], $r[1]) + rand(0, 99) / 100,
                    "status" => $statuses[array_rand($statuses)],
                    "created_at" => $date . " " . rand(8, 22) . ":" . str_pad(rand(0, 59), 2, "0", STR_PAD_LEFT) . ":00",
                ]);
                $count++;
            }
        }
        return back()->with("ml_success", "Se generaron $count registros demo (30 dias). El modelo se recalculo automaticamente.");
    }

    public function downloadTemplate()
    {
        $csv = "patient_name,concept,amount,status\n";
        $csv .= "Juan Perez,Medicamentos,3500.00,Pendiente\n";
        $csv .= "Maria Lopez,Cirugia,28000.00,Pagado\n";
        $csv .= "Carlos Ruiz,Consulta Urgencias,1200.00,Seguro\n";
        $csv .= "Ana Torres,UCI,15000.00,Pendiente\n";
        $csv .= "Pedro Gomez,Medicamentos,7800.00,Pagado\n";
        return response($csv, 200)
            ->header("Content-Type", "text/csv")
            ->header("Content-Disposition", "attachment; filename=plantilla_ml_dashboard.csv");
    }
}
