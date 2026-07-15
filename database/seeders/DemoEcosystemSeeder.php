<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class DemoEcosystemSeeder extends Seeder
{
    public function run()
    {
        $docId = DB::table('users')->where('role','medico')->value('id') ?? DB::table('users')->value('id') ?? 1;
        DB::table('alertas_ml')->delete();
        DB::table('costos_evento')->delete();
        DB::table('explicacion_prediccion')->delete();
        DB::table('resultados_reales')->delete();
        DB::table('predicciones_clinicas')->delete();
        DB::table('ml_modelos_versiones')->delete();
        if (DB::table('beds')->count() < 15) {
            $a = ['UCI','Terapia Intermedia','Hospitalizacion General'];
            for ($i=0;$i<15;$i++) DB::table('beds')->insertOrIgnore(['bed_number'=>'C-'.($i+1),'area'=>$a[$i%3],'status'=>'disponible','created_at'=>now(),'updated_at'=>now()]);
        }
        $beds = DB::table('beds')->limit(15)->get();
        $versiones = [['RF v1.0','random_forest','modelos/rf_v1.pkl',0.72,0.68,'inactivo',1,'2024-10-15 12:00:00'],['RF v1.1','random_forest','modelos/rf_v1.1.pkl',0.76,0.71,'inactivo',2,'2024-11-20 12:00:00'],['RF v2.0','random_forest','modelos/rf_v2.pkl',0.82,0.78,'inactivo',3,'2024-12-10 12:00:00'],['RF v2.1','random_forest','modelos/rf_v2.1.pkl',0.85,0.80,'inactivo',4,'2025-01-05 12:00:00'],['RF v3.0','random_forest','modelos/rf_v3.pkl',0.83,0.77,'activo',5,'2025-01-20 12:00:00']];
        $v3Id = null;
        foreach ($versiones as $v) {$vid = DB::table('ml_modelos_versiones')->insertGetId(['nombre'=>$v[0],'algoritmo'=>$v[1],'ruta_archivo'=>$v[2],'metrica_f1'=>$v[3],'metrica_accuracy'=>$v[4],'estado'=>$v[5],'version'=>$v[6],'trained_at'=>$v[7],'created_at'=>$v[7],'updated_at'=>$v[7]]);if($v[0]==='RF v3.0')$v3Id=$vid;}
        $P = [['Maria Garcia Lopez',78,'90/60','110','38.9',82,'Rojo','Dificultad respiratoria severa','J18.9',1,1,28000,8,'fallecio',6,32000,0.92],['Jose Hernandez Ruiz',82,'85/55','120','39.2',78,'Rojo','Shock septico insuficiencia renal','A41.9',1,1,35000,12,'fallecio',4,41000,0.95],['Carmen Morales Diaz',71,'95/65','105','38.5',80,'Rojo','IC aguda edema pulmonar','I50.0',0,1,25000,10,'fallecio',7,29000,0.88],['Roberto Sanchez Vega',69,'100/70','95','37.8',85,'Rojo','EPOC exacerbado disnea','J44.1',0,1,18000,7,'vivo',5,15000,0.82],['Ana Rosa Martinez',73,'110/75','88','37.2',88,'Naranja','Neumonia nosocomial diabetes','J15.9',1,0,20000,9,'vivo',11,23000,0.79],['Pedro Jimenez Castillo',58,'105/70','90','37.5',91,'Naranja','Pancreatitis aguda severa','K85.0',1,0,15000,5,'fallecio',8,22000,0.55],['Laura Patricia Gomez',45,'120/80','78','37.0',96,'Amarillo','Apendicitis aguda dolor FII','K35.8',0,0,8000,3,'vivo',2,7500,0.35],['Miguel Angel Torres',52,'130/85','82','36.8',95,'Amarillo','Colecistitis aguda vomito','K81.0',1,1,10000,4,'vivo',3,9200,0.42],['Sandra Elena Reyes',61,'125/82','85','37.1',94,'Naranja','ICTUS isquemico hemiparesia','I63.9',1,1,18000,7,'vivo',9,21000,0.58],['Fernando Diaz Estrada',48,'115/75','75','36.5',97,'Amarillo','Obstruccion intestinal','K56.5',0,0,12000,5,'vivo',6,13500,0.30],['Gabriela Ramos Flores',28,'110/70','72','36.6',99,'Verde','Colecistitis cronica','K81.1',0,0,6000,2,'vivo',1,5500,0.08],['Carlos Alberto Medina',35,'118/76','70','36.4',98,'Verde','Hernia inguinal derecha','K40.9',0,0,5000,1,'vivo',1,4800,0.05],['Patricia Luna Herrera',32,'105/68','68','36.7',99,'Verde','Apendicitis cronica','K37',0,0,5500,2,'vivo',2,5200,0.10],['Diego Vargas Mendoza',41,'112/74','74','36.5',98,'Amarillo','Ulcera peptica perforada','K25.1',0,0,9000,4,'vivo',5,10500,0.18],['Rosa Elena Fuentes',38,'108/72','70','36.8',98,'Verde','Colelitiasis asintomatica','K80.2',0,0,5000,1,'vivo',1,4500,0.06]];
        $dxMap = ['J18.9'=>'Neumonia severa + Sepsis','A41.9'=>'Sepsis origen urinario','I50.0'=>'IC aguda clase IV','J44.1'=>'EPOC exacerbacion severa','J15.9'=>'Neumonia hospital','K85.0'=>'Pancreatitis aguda grave','K35.8'=>'Apendicitis aguda','K81.0'=>'Colecistitis litiasica','I63.9'=>'Infarto cerebral agudo','K56.5'=>'Obstruccion intestinal','K81.1'=>'Colecistitis cronica','K40.9'=>'Hernia inguinal directa','K37'=>'Apendicitis cronica','K25.1'=>'Ulcera gastrica perforada','K80.2'=>'Colelitiasis'];
        $items = [['Honorario cirujano','otro',5000,8000,false],['Honorario anestesiologo','otro',2000,4000,false],['Suturas','insumo',300,800,false],['Material curacion','insumo',200,600,false],['Medicamentos IV','insumo',1500,5000,false],['Oxigenoterapia','gas_medico',500,2000,false],['Laboratorio','otro',800,2000,false],['RX torax','otro',400,800,false],['Tomografia','otro',2000,5000,false],['UCI (dia)','otro',5000,8000,true],['Cama (dia)','otro',800,1500,true],['Ventilador mecanico','gas_medico',3000,5000,true]];
        foreach ($P as $idx => $p) {
            $bed = $beds[$idx % $beds->count()];$fi = Carbon::now()->subDays(rand(5,60));$fe = $fi->copy()->addDays($p[14]);$triage = $p[6];$area = $triage==='Rojo'?'UCI':($triage==='Naranja'?'Terapia Intermedia':'Hospitalizacion General');$dx = $dxMap[$p[8]] ?? $p[7];
            $tid = DB::table('triages')->insertGetId(['patient_name'=>$p[0],'age'=>$p[1],'triage_level'=>$triage,'symptoms'=>$p[7],'status'=>'Hospitalizado','assigned_area'=>$area,'vitals_ta'=>$p[2],'vitals_fc'=>$p[3],'vitals_temp'=>$p[4],'vitals_spo2'=>strval($p[5]),'is_derived'=>0,'diagnostico'=>$dx,'cie10'=>$p[8],'tratamiento'=>'Protocolo','doctor_notes'=>'Demo ML','assigned_doctor_id'=>$docId,'created_at'=>$fi,'updated_at'=>$fi]);
            DB::table('hospitalizations')->insert(['triage_id'=>$tid,'patient_name'=>$p[0],'bed_id'=>$bed->id,'status'=>$p[13]==='fallecio'?'Fallecido':'Alta Medica','admission_date'=>$fi->format('Y-m-d'),'discharge_date'=>$fe->format('Y-m-d'),'diagnosis'=>$dx,'treatment'=>'Protocolo','assigned_doctor_id'=>$docId,'assigned_nurse_id'=>$docId,'notes'=>'Demo','created_at'=>$fi,'updated_at'=>$fe]);
            $pred = $p[16]>=0.7?'alto_riesgo':($p[16]>=0.3?'medio_riesgo':'bajo_riesgo');$taS = intval(explode('/',$p[2])[0]);$datos = json_encode(['edad'=>$p[1],'frecuencia_cardiaca'=>floatval($p[3]),'presion_sistolica'=>$taS,'temperatura'=>floatval($p[4]),'spo2'=>floatval($p[5]),'diabetes'=>$p[9],'hipertension'=>$p[10],'costo_estimado'=>$p[11],'dias_estimados'=>$p[12]]);
            $pid = DB::table('predicciones_clinicas')->insertGetId(['patient_id'=>$tid,'doctor_id'=>$docId,'modelo_version_id'=>$v3Id,'datos_entrada'=>$datos,'prediccion'=>$pred,'probabilidad'=>$p[16],'score_confianza'=>$p[16]*0.95,'estado'=>'cerrada','aprobado_para_entrenamiento'=>1,'created_at'=>$fi->copy()->addMinutes(30),'updated_at'=>$fe]);
            $spo2V=max(0,(100-$p[5])*2.5);$edadV=max(0,($p[1]-30)*0.6);$fcV=max(0,($p[3]-60)*0.3);$tempV=max(0,($p[4]-36.5)*8);$taV=max(0,(110-$taS)*0.4);$diabV=$p[9]?12:0;$htV=$p[10]?6:0;$tot=$spo2V+$edadV+$fcV+$tempV+$taV+$diabV+$htV;
            if($tot>0){$fd=['SpO2'=>round($spo2V/$tot*100,1),'Edad'=>round($edadV/$tot*100,1),'FC'=>round($fcV/$tot*100,1),'Temp'=>round($tempV/$tot*100,1),'TA'=>round($taV/$tot*100,1),'Diabetes'=>round($diabV/$tot*100,1),'HTA'=>round($htV/$tot*100,1)];}else{$fd=['SpO2'=>15,'Edad'=>15,'FC'=>14,'Temp'=>14,'TA'=>14,'Diabetes'=>14,'HTA'=>14];}
            foreach($fd as $fn=>$fv){DB::table('explicacion_prediccion')->insert(['prediccion_id'=>$pid,'variables'=>json_encode($fd),'variable'=>$fn,'peso'=>$fv/100,'impacto'=>$fv>=20?'alto':($fv>=10?'medio':'bajo'),'created_at'=>$fi->copy()->addMinutes(31)]);}
            DB::table('resultados_reales')->insert(['prediccion_id'=>$pid,'resultado_real'=>$p[13],'dias_hospitalizacion'=>$p[14],'costo_real'=>$p[15],'fecha_cierre'=>$fe->format('Y-m-d'),'notas_doctor'=>$p[13]==='fallecio'?'Fallecio':'Alta','created_at'=>$fe,'updated_at'=>$fe]);
            shuffle($items);for($i=0;$i<rand(3,5);$i++){$it=$items[$i%count($items)];$qty=$it[3]?$p[14]:1;$uc=rand($it[2],$it[3]);DB::table('costos_evento')->insert(['patient_id'=>$tid,'prediccion_id'=>$pid,'tipo'=>$it[1],'descripcion'=>$it[0],'cantidad'=>$qty,'costo_unitario'=>$uc,'costo_total'=>$qty*$uc,'registrado_por'=>$docId,'created_at'=>$fe->copy()->subHours(2),'updated_at'=>$fe->copy()->subHours(2)]);}
        }
        // ALERTAS: leer ENUM real de la columna tipo
        $tipoCol = DB::select("SHOW COLUMNS FROM alertas_ml WHERE Field='tipo'")[0] ?? null;
        $enumVals = [];
        if ($tipoCol && preg_match("/enum\((.*)\)/", $tipoCol->Type, $m)) {
            $enumVals = array_map(function($v){return trim($v, "'");}, explode(',', $m[1]));
        }
        $aCols = array_column(DB::select('SHOW COLUMNS FROM alertas_ml'),'Field');
        $aText = null;
        foreach($aCols as $c){$t=DB::select("SHOW COLUMNS FROM alertas_ml WHERE Field=?",[$c])[0]->Type??'';if((strpos($t,'text')!==false||strpos($t,'varchar')!==false)&&!in_array($c,['tipo','estado'])){$aText=$c;break;}}
        $aEstado = in_array('estado',$aCols);
        // Leer enum de estado tambien
        $estadoVals = [];
        if($aEstado){$ec=DB::select("SHOW COLUMNS FROM alertas_ml WHERE Field='estado'")[0]??null;if($ec&&preg_match("/enum\((.*)\)/",$ec->Type,$em)){$estadoVals=array_map(function($v){return trim($v,"'");},explode(',',$em[1]));}}
        $safeTipo = $enumVals ? $enumVals[0] : 'info';
        $safeEstado = $estadoVals ? $estadoVals[0] : 'pendiente';
        $estadoResuelta = in_array('resuelta',$estadoVals) ? 'resuelta' : (in_array('leida',$estadoVals) ? 'leida' : $safeEstado);
        $estadoPendiente = in_array('pendiente',$estadoVals) ? 'pendiente' : (in_array('activa',$estadoVals) ? 'activa' : $safeEstado);
        $mensajes = ['Riesgo >90% cama C-1 Maria Garcia','Riesgo >90% cama C-2 Jose Hernandez','Caso #3 Real $29k vs Estimado $25k','Caso #6 Real $22k vs Estimado $15k','Modelo v3.0 F1 bajo 85% a 83%','FN caso #6 Predijo 55% pero fallecio','Modelo v3.0 desplegado exitosamente','Re-entrenar modelo sugerido'];
        $estados = [$estadoResuelta,$estadoResuelta,$estadoResuelta,$estadoPendiente,$estadoPendiente,$estadoPendiente,$estadoResuelta,$estadoPendiente];
        $fechas = ['2025-01-20 08:30:00','2025-01-20 09:15:00','2025-01-22 14:00:00','2025-01-25 10:00:00','2025-01-27 07:00:00','2025-01-25 16:00:00','2025-01-20 12:00:00','2025-01-28 06:00:00'];
        for($i=0;$i<count($mensajes);$i++){
            $ins = ['tipo'=>$safeTipo,'created_at'=>$fechas[$i],'updated_at'=>$fechas[$i]];
            if($aText) $ins[$aText] = $mensajes[$i];
            if($aEstado) $ins['estado'] = $estados[$i];
            DB::table('alertas_ml')->insert($ins);
        }
        $this->command->info('ECOSISTEMA COMPLETO: 15 pacientes. ENUM tipo: '.implode(',',$enumVals).' | texto: '.$aText);
    }
}
