<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hipodromo;
use App\Models\Caballo;
use App\Models\Carrera;
use App\Models\Subasta;
use Illuminate\Support\Facades\DB;

class SubastaController extends Controller{

// //Guardar una Subasta
// public function store(Request $request){

//     $new_subasta = new Subasta();

//     if(auth()->user()->rol_id == 1){

//         $validator = "";

//         if(!$request->input('carrera_id')){
//             $validator = "ID de la carrera requerido";
//         }else{

//             //Se obtiene el ID de la Jugada Subasta
//             $tipo_jugada = Tipo_apuesta::Where('activo', '=', 1)->Where('name', '=', "Subasta")->first();

//             if($tipo_jugada != null){
//                 //Consultar si el Hipodromo al cual pertenece la carrera, tiene la jugada de subastas Activa
//                 $jugadas = DB::table('jugadas')
//                 ->where('hipodromo_id', '=', $request->input('hipodromo_id'))
//                 ->where('tipo_apuesta_id', '=', $tipo_jugada->id)
//                 ->where('activa', '=', 1)
//                 ->first();

//                 //El hipodromo tiene las Subastas activas. Se crea el registro de subasta
//                 if($jugadas != null){
//                     $new_subasta = new Subasta;

//                     $new_subasta->carrera_id = $new_carrera->id;

//                     $new_subasta->save();
//                 }
//             }







//             $subasta_consultado = Subasta::where('carrera_id',$request->carrera_id)->get();

//             if(count($subasta_consultado) > 0){
//                 $validator = ($validator == "") ? $validator . "Ya existe una Subasta para esta Carrera" : $validator . " - Ya existe una Subasta para esta Carrera";
//             }
//         }

//         if(!$request->input('fecha')){
//             $validator = ($validator == "") ? $validator . "Fecha de la carrera requerida" : $validator . " - Fecha de la carrera requerida";
//         }

//         if(!$request->input('distancia')){
//             $validator = ($validator == "") ? $validator . "Distancia de la carrera requerida" : $validator . " - Distancia de la carrera requerida";
//         }

//         if(!$request->input('hora')){
//             $validator = ($validator == "") ? $validator . "Hora de la carrera requerida" : $validator . " - Hora de la carrera requerida";
//         }

//         if(!$request->input('hipodromo_id')){
//             $validator = ($validator == "") ? $validator . "ID del hipodromo requerido" : $validator . " - ID del hipodromo requerido";
//         }else{
//             $hipodromo_consultado = Hipodromo::where('id',$request->hipodromo_id)->get();

//             if(count($hipodromo_consultado) < 1){
//                 $validator = ($validator == "") ? $validator . "No existe el hipodromo" : $validator . " - No existe el hipodromo";
//             }
//         }

//         if($validator != ""){
//             return response()->json(
//                 [
//                     'Status_Code' => '400',
//                     'Success'     => 'False',
//                     'Response'    => ['Errores' => $validator],
//                     'Message'     => "Existen errores",
//                 ], 400
//             ); 
//         }else{
//             $new_carrera->nro_carrera = $request->nro_carrera;
//             $new_carrera->fecha = $request->fecha;
//             $new_carrera->distancia = $request->distancia;
//             $new_carrera->hora = $request->hora;
//             $new_carrera->hipodromo_id = $request->hipodromo_id;

//             if($new_carrera->save()){

//                 //Se obtiene el ID de la Jugada Subasta
//                 $tipo_jugada = Tipo_apuesta::Where('activo', '=', 1)->Where('name', '=', "Subasta")->first();

//                 if($tipo_jugada != null){
//                     //Consultar si el Hipodromo al cual pertenece la carrera, tiene la jugada de subastas Activa
//                     $jugadas = DB::table('jugadas')
//                     ->where('hipodromo_id', '=', $request->input('hipodromo_id'))
//                     ->where('tipo_apuesta_id', '=', $tipo_jugada->id)
//                     ->where('activa', '=', 1)
//                     ->first();

//                     //El hipodromo tiene las Subastas activas. Se crea el registro de subasta
//                     if($jugadas != null){
//                         $new_subasta = new Subasta;

//                         $new_subasta->carrera_id = $new_carrera->id;

//                         $new_subasta->save();
//                     }
//                 }

//                 return response()->json(
//                     [
//                         'Status_Code' => '201',
//                         'Success'     => 'True',
//                         'Response'    => ['Carrera' => $new_carrera],
//                         'Message'     => "Carrera creada exitosamente",
//                     ], 201
//                 ); 
//             }else{
//                 return response()->json(
//                     [
//                         'Status_Code' => '400',
//                         'Success'     => 'False',
//                         'Response'    => [],
//                         'Message'     => "Error al crear la nueva Carrera",
//                     ], 400
//                 ); 
//             }
//         }
//     }else{
//         return response()->json(
//             [
//                 'Status_Code' => '403',
//                 'Success'     => 'False',
//                 'Response'    => [],
//                 'Message'     => "No posee los permisos necasarios para acceder.",
//             ], 403
//         );
//     }        
// }

    // //Obtiene todos los Hipodromos registrados
    // public function index($status){

    //     $hipodromo = new Hipodromo();

    //     //Status = 2 para consultar todos los hipodromos
    //     if($status == 2){
    //         $hipodromo = Hipodromo::all();
    //     }else{
    //         //Status = 1 para consultar los hipodromos inactivos. Status = 0 para consultar los hipodromos Activos. 
    //         $hipodromo = Hipodromo::Where('activo', '=', $status)->get();
    //     }

    //     return response()->json(
    //         [
    //             'Status_Code' => '200',
    //             'Success'     => 'True',
    //             'Response'    => ['Hipodromos' => $hipodromo],
    //             'Message'     => "Listado de Hipodromos",
    //         ], 200
    //     ); 
    // }    

    // //Consultar un Hipodromo
    // public function show($id){

    //     $hipodromo_consultado = Hipodromo::where('id', $id)->get();

    //     if(count($hipodromo_consultado) > 0){
    //         return response()->json(
    //             [
    //                 'Status_Code' => '200',
    //                 'Success'     => 'True',
    //                 'Response'    => ['Hipodromo' => $hipodromo_consultado],
    //                 'Message'     => "Hipodromo encontrado",
    //             ], 200
    //         ); 
    //     }else{
    //         return response()->json(
    //             [
    //                 'Status_Code' => '404',
    //                 'Success'     => 'False',
    //                 'Response'    => [],
    //                 'Message'     => "Hipodromo no encontrada",
    //             ], 404
    //         ); 
    //     }
    // }

    // //Borrar un Hipodromo
    // public function delete($id){

    //     if(auth()->user()->rol_id == 1){
    //         $data = Hipodromo::where('id',$id)->first();

    //         if($data != null){
    //             if($data->activo == 1){
    //                 $data->activo = 0;

    //                 if($data->save()){
    //                     return response()->json(
    //                         [
    //                             'Status_Code' => '200',
    //                             'Success'     => 'True',
    //                             'Response'    => ['Hipodormo' => $data],
    //                             'Message'     => "Hipodromo borrada exitosamente",
    //                         ], 200
    //                     );  
    //                 }else{
    //                     return response()->json(
    //                         [
    //                             'Status_Code' => '400',
    //                             'Success'     => 'False',
    //                             'Response'    => [],
    //                             'Message'     => "Error al borrar el Hipodromo",
    //                         ], 400
    //                     ); 
    //                 }
    //             }else{
    //                 return response()->json(
    //                     [
    //                         'Status_Code' => '400',
    //                         'Success'     => 'False',
    //                         'Response'    => ['Tipo_Apuesta' => $data],
    //                         'Message'     => "El Hipodromo se encuentra inactivo",
    //                     ], 400
    //                 );
    //             }
    //         }else{
    //             return response()->json(
    //                 [
    //                     'Status_Code' => '404',
    //                     'Success'     => 'False',
    //                     'Response'    => [],
    //                     'Message'     => "Hipodromo no encontrado",
    //                 ], 404
    //             ); 
    //         } 
    //     }else{
    //         return response()->json(
    //             [
    //                 'Status_Code' => '403',
    //                 'Success'     => 'False',
    //                 'Response'    => [],
    //                 'Message'     => "No posee los permisos necasarios para acceder.",
    //             ], 403
    //         );
    //     }   
    // }

    // //Actualizar un Hipodromo
    // public function update(Request $request, $id){

    //     if(auth()->user()->rol_id == 1){
    //         $data = Hipodromo::where('id',$id)->first();

    //         if($data != null){
    //             if(!$request->input('name')){
    //                 return response()->json(
    //                     [
    //                         'Status_Code' => '400',
    //                         'Success'     => 'False',
    //                         'Response'    => [],
    //                         'Message'     => "Indique el nombre del Hipodromo",
    //                     ], 400
    //                 );
    //             }else{
    //                 $hipodromo_consultado = Hipodromo::where('name',$request->name)->first();
        
    //                 if($hipodromo_consultado != null && (int)$id <> (int)$hipodromo_consultado->id){
    //                     return response()->json(
    //                         [
    //                             'Status_Code' => '400',
    //                             'Success'     => 'False',
    //                             'Response'    => [$hipodromo_consultado->id],
    //                             'Message'     => "Hipodromo ya existente.",
    //                         ], 400
    //                     ); 
    //                 }else{
    //                     $data->name = $request->name;
    //                 }
    //             }

    //             if($data->save()){
    //                 return response()->json(
    //                     [
    //                         'Status_Code' => '201',
    //                         'Success'     => 'True',
    //                         'Response'    => ['name' => $request->name],
    //                         'Message'     => "Hipodromo actualizado exitosamente",
    //                     ], 201
    //                 ); 
    //             }else{
    //                 return response()->json(
    //                     [
    //                         'Status_Code' => '400',
    //                         'Success'     => 'False',
    //                         'Response'    => [],
    //                         'Message'     => "Error al guardar el nuevo Tipo de Apuesta",
    //                     ], 400
    //                 ); 
    //             }   
    //         }else{
    //             return response()->json(
    //                 [
    //                     'Status_Code' => '404',
    //                     'Success'     => 'False',
    //                     'Response'    => [],
    //                     'Message'     => "Hipodromo no encontrado",
    //                 ], 404
    //             ); 
    //         } 
    //     }else{
    //         return response()->json(
    //             [
    //                 'Status_Code' => '403',
    //                 'Success'     => 'False',
    //                 'Response'    => [],
    //                 'Message'     => "No posee los permisos necasarios para acceder.",
    //             ], 403
    //         );
    //     }
    // }

    // //Activar un Hipodromo especifico. Se cambio el estado a ACTIVO
    // public function active($id){

    //     if(auth()->user()->rol_id == 1){
    //         $data = Hipodromo::where('id',$id)->first();

    //         if($data != null){
    //             if($data->activo == 1){
    //                 return response()->json(
    //                     [
    //                         'Status_Code' => '200',
    //                         'Success'     => 'True',
    //                         'Response'    => ['Hipodromo' => $data],
    //                         'Message'     => "El Hipodromo se encuentra ACTIVO",
    //                     ], 400
    //                 );
    //             }else{
    //                 $data->activo = 1;

    //                 if($data->save()){
    //                     return response()->json(
    //                         [
    //                             'Status_Code' => '201',
    //                             'Success'     => 'True',
    //                             'Response'    => ['Hipodromo' => $data],
    //                             'Message'     => "Hipodromo ACTIVADO exitosamente",
    //                         ], 201
    //                     ); 
    //                 }else{
    //                     return response()->json(
    //                         [
    //                             'Status_Code' => '400',
    //                             'Success'     => 'False',
    //                             'Response'    => [],
    //                             'Message'     => "Error al activar el Hipodromo",
    //                         ], 400
    //                     ); 
    //                 }
    //             }             
    //         }else{
    //             return response()->json(
    //                 [
    //                     'Status_Code' => '404',
    //                     'Success'     => 'False',
    //                     'Response'    => [],
    //                     'Message'     => "Hipodromo no encontrado",
    //                 ], 404
    //             ); 
    //         } 
    //     }else{
    //         return response()->json(
    //             [
    //                 'Status_Code' => '403',
    //                 'Success'     => 'False',
    //                 'Response'    => [],
    //                 'Message'     => "No posee los permisos necasarios para acceder.",
    //             ], 403
    //         );
    //     }
    // }

    // //Obtiene todos los caballos Retirados de una carrera en un Hipodromo
    // public function retirados(Request $request, $id){

    //     if(auth()->user()->rol_id == 1){
            
    //         $validator = "";

    //         $hipodromo_consultado = Hipodromo::where('id',$id)->get();

    //         if(count($hipodromo_consultado) < 1){
    //             $validator = "No existe el hipodromo";
    //         }

    //         if(!$request->input('fecha')){
    //             $validator = ($validator == "") ? $validator . "Campo fecha es Requerido." : $validator . " - Campo fecha es Requerido.";
    //         }

    //         if($validator != ""){
    //             return response()->json(
    //                 [
    //                     'Status_Code' => '400',
    //                     'Success'     => 'False',
    //                     'Response'    => ['Errores' => $validator],
    //                     'Message'     => "Existen errores",
    //                 ], 400
    //             ); 
    //         }else{
    //             $carrera_consultada = DB::table('carrera')
    //                                          ->where('hipodromo_id',$id)
    //                                          ->where('fecha', $request->fecha)
    //                                          ->where('borrada', '=', 0)
    //                                          ->select('carrera.id')
    //                                          ->pluck('id');
                
    //             if(count($carrera_consultada) > 0){

    //                 $retirados = Caballo::whereIn('carrera_id', $carrera_consultada)
    //                                         ->where('retirado',1)
    //                                         ->where('borrado', '=', 0)
    //                                         ->Join('carrera', 'caballo.carrera_id', '=', 'carrera.id')
    //                                         ->select('carrera.nro_carrera', 'caballo.nro_caballo', 'caballo.name')
    //                                         ->get();


    //                 return response()->json(
    //                     [
    //                         'Status_Code' => '200',
    //                         'Success'     => 'True',
    //                         'Response'    => ['Retirados' => $retirados],
    //                         'Message'     => "Caballos retirados",
    //                     ], 200
    //                 ); 
    //             }else{
    //                 return response()->json(
    //                     [
    //                         'Status_Code' => '404',
    //                         'Success'     => 'False',
    //                         'Response'    => [],
    //                         'Message'     => "El hipodromo no posee carreras en la fecha consultada.",
    //                     ], 404
    //                 ); 
    //             }
    //         }

    //     }else{
    //         return response()->json(
    //             [
    //                 'Status_Code' => '403',
    //                 'Success'     => 'False',
    //                 'Response'    => [],
    //                 'Message'     => "No posee los permisos necasarios para acceder.",
    //             ], 403
    //         );
    //     }
    // }

    // //Obtiene todas las Carreras de un Hipodromo
    // public function show_carreras(Request $request, $id){

    //     var_dump($id);
    //     if(auth()->user()->rol_id == 1){

    //         $validator = "";
        
    //         $hipodromo_consultado = Hipodromo::where('id',$id)->get();
    
    //         if(count($hipodromo_consultado) < 1){
    //             $validator = "No existe el hipodromo";
    //         }
    
    //         if(is_null($request->input('activa'))){
    //             $validator = ($validator == "") ? $validator . "Filtro status requerido" : $validator . " - Filtro status requerido";
    //         }
    
    //         if($validator != ""){
    //             return response()->json(
    //                 [
    //                     'Status_Code' => '400',
    //                     'Success'     => 'False',
    //                     'Response'    => ['Errores' => $validator],
    //                     'Message'     => "Existen errores",
    //                 ], 400
    //             ); 
    //         }else{
    //              //status = 2 se consultan todas las Carreras de un Hipodromo
    //             if($request->input('status') == 2){
    //                 $carreras = DB::table('carrera')
    //                 ->where('hipodromo_id', '=', $id)
    //                 ->where('borrada', '=', 0)
    //                 ->orderBy('id', 'asc')
    //                 ->get();
    //             }else{
    //                 $carreras = DB::table('carrera')
    //                 ->where('hipodromo_id', '=', $id)
    //                 ->where('activa', '=', $request->input('activa'))
    //                 ->where('borrada', '=', 0)
    //                 ->orderBy('id', 'asc')
    //                 ->get();
    //             }
        
    //             return response()->json(
    //                 [
    //                     'Status_Code' => '200',
    //                     'Success'     => 'True',
    //                     'Response'    => ['Carreras' => $carreras],
    //                     'Message'     => "Listado de carreras por Hipodromo",
    //                 ], 200
    //             );
    //         } 
    //     }else{
    //         return response()->json(
    //             [
    //                 'Status_Code' => '403',
    //                 'Success'     => 'False',
    //                 'Response'    => [],
    //                 'Message'     => "No posee los permisos necasarios para acceder.",
    //             ], 403
    //         );
    //     }
    // }

    // //Consultar todas las Carreras de un hipodromo por fecha
    // public function show_data(Request $request, $id){

    //     $validator = "";

    //     if(auth()->user()->rol_id == 1){

    //         $hipodromo_consultado = Hipodromo::where('id',$id)->get();
    
    //         if(count($hipodromo_consultado) < 1){
    //             $validator = "No existe el hipodromo";
    //         }
    
    //         if(!$request->input('fecha')){
    //             $validator = ($validator == "") ? $validator . "Fecha requerida" : $validator . " - Fecha requerida";
    //         }
    
    //         if($validator != ""){
    //             return response()->json(
    //                 [
    //                     'Status_Code' => '400',
    //                     'Success'     => 'False',
    //                     'Response'    => ['Errores' => $validator],
    //                     'Message'     => "Existen errores",
    //                 ], 400
    //             ); 
    //         }else{

    //             $carrera_consultada = Carrera::where('hipodromo_id',$id)
    //                                          ->where('fecha', $request->fecha)
    //                                          ->where('borrada', '=', 0)
    //                                          ->get();

    //             if(count($carrera_consultada) > 0){
    //                 return response()->json(
    //                     [
    //                         'Status_Code' => '200',
    //                         'Success'     => 'True',
    //                         'Response'    => ['Carrera' => $carrera_consultada],
    //                         'Message'     => "Carrera encontrada",
    //                     ], 200
    //                 ); 
    //             }else{
    //                 return response()->json(
    //                     [
    //                         'Status_Code' => '404',
    //                         'Success'     => 'False',
    //                         'Response'    => [],
    //                         'Message'     => "Carrera no encontrada",
    //                     ], 404
    //                 ); 
    //             }
    //         }
    //     }else{
    //         return response()->json(
    //             [
    //                 'Status_Code' => '403',
    //                 'Success'     => 'False',
    //                 'Response'    => [],
    //                 'Message'     => "No posee los permisos necasarios para acceder.",
    //             ], 403
    //         );
    //     }
    // }
}