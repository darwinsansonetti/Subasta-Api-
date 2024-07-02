<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hipodromo;
use App\Models\Caballo;
use App\Models\Carrera;
use App\Models\Subasta;
use App\Models\Caballo_subastado;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SubastaController extends Controller{

    //Guardar una Subasta
    public function store(Request $request){

        $validator = "";
        $indice = 0;
        $id_subasta = 0;

        foreach($request->input('subasta') as $valor_subasta)
        {
            $indice++;
            $id_subasta = $valor_subasta['subasta_id'];
            $new_subasta = new Subasta;
            
            if(is_null($valor_subasta['subasta_id'])){ 
                $validator = "ID de la Subasta requerido. Registro $indice";
            }else{
                $subasta = Subasta::Where('id', '=', $valor_subasta['subasta_id'])
                                    ->where('activa', '=', 1)                            
                                    ->first();

                if($subasta == null){
                    $validator = "No existe el ID de la Subasta. Registro $indice.";
                }
            }

            if(is_null($valor_subasta['caballo_subastado_id'])){ 
                $validator = ($validator == "") ? $validator . "ID del Caballo Subastado requerido. Registro $indice." : $validator . " - ID del Caballo Subastado requerido. Registro $indice.";
            }else{
                $caballo_subastado = Caballo_subastado::Where('id', '=', $valor_subasta['caballo_subastado_id'])
                                    ->where('borrado', '=', 0)                            
                                    ->first();

                if($caballo_subastado == null){
                    $validator = ($validator == "") ? $validator . "No existe el Caballo para la subasta. Registro $indice." : $validator . " - No existe el Caballo para la subasta. Registro $indice.";
                }
            }

            if(is_null($valor_subasta['user_id'])){ 
                $validator = ($validator == "") ? $validator . "ID del Usuario requerido. Registro $indice." : $validator . " - ID del Usuario requerido. Registro $indice.";
            }else{
                $user_consultado = User::where('id', $valor_subasta['user_id'])->first();

                if($user_consultado == null){
                    $validator = ($validator == "") ? $validator . "No existe el Usuario. Registro $indice." : $validator . " - No existe el Usuario. Registro $indice.";
                }
            }

            if(is_null($valor_subasta['monto'])){ 
                $validator = "Monto requerido. Registro $indice.";
            }else{
                if($valor_subasta['monto'] < 1 || ($caballo_subastado != null && $valor_subasta['monto'] <= $caballo_subastado->monto_subastado)){ 
                    $validator = ($validator == "") ? $validator . "Monto invalido. Registro $indice." : $validator . " - Monto invalido. Registro $indice.";
                }else{
                    if($user_consultado->saldo < $valor_subasta['monto']){
                        $validator = ($validator == "") ? $validator . "Saldo insuficiente. Registro $indice." : $validator . " - Saldo insuficiente. Registro $indice.";
                    }
                }
            }

            if($validator == ""){
                //Se ubica el usuario y el monto anterior
                $user_old = $caballo_subastado->user_id;
                $monto_old = $caballo_subastado->monto_subastado;

                //Restamos el valor subastado del caballo al total de la subasta y se le suma el nuevo monto
                $subasta->total = $subasta->total - $caballo_subastado->monto_subastado;
                $subasta->total += $valor_subasta['monto'];
                $porcentaje_resta = round((($subasta->total * $subasta->porcentaje) / 100), 2);
                $subasta->premio = round(($subasta->total - $porcentaje_resta), 2);
                $subasta->save();

                //Se busca el Usuario para devolver el dinero
                $user_consultado_old = User::where('id', $user_old)->first();

                if($user_consultado_old != null){
                    $user_consultado_old->saldo += $monto_old;
                    $user_consultado_old->save();
                }

                //Se resta el saldo al nuevo usuario q subasto
                $user_consultado->saldo -= $valor_subasta['monto'];
                $user_consultado->save();

                //Se asigna el monto y el usuario al caballo subastado
                $caballo_subastado->monto_subastado = $valor_subasta['monto'];
                $caballo_subastado->user_id = $valor_subasta['user_id'];
                $caballo_subastado->save();
            }
        }

        if($validator != ""){

            if($indice > 0){
                $subasta = Subasta::Where('subasta.id', '=', $id_subasta)
                            ->Join('carrera', 'subasta.carrera_id', '=', 'carrera.id')
                            ->select('subasta.*', 'carrera.nro_carrera', 'carrera.fecha', 'carrera.distancia', 'carrera.hora')                            
                            ->first();

                if($subasta != null){
                    $caballos_subastados = Caballo_subastado::Where('subasta_id', '=', $subasta->id)
                                ->where('caballo_subastado.borrado', '=', 0)
                                ->Join('caballo', 'caballo.id', '=', 'caballo_subastado.caballo_id')
                                ->Join('user', 'user.id', '=', 'caballo_subastado.user_id')
                                ->select('caballo_subastado.*', 'caballo.nro_caballo', 'caballo.name', 'caballo.retirado', 'user.name as usuario')
                                ->orderBy('id', 'asc')
                                ->get();
    
                    return response()->json(
                        [
                            'Status_Code' => '409',
                            'Success'     => 'False',
                            'Response'    => ['Errores' => $validator, 'Subasta' => $subasta, 'Caballos' => $caballos_subastados],
                            'Message'     => "Existen errores, pero se pudieron haber creado subastas.",
                        ], 200
                    );
                }else{
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => ['Errores' => $validator],
                            'Message'     => "Existen errores",
                        ], 400
                    );
                }
            }else{
                return response()->json(
                    [
                        'Status_Code' => '400',
                        'Success'     => 'False',
                        'Response'    => ['Errores' => $validator],
                        'Message'     => "Existen errores",
                    ], 400
                ); 
            }
        }else{

            $subasta = Subasta::Where('subasta.id', '=', $id_subasta)
                            ->Join('carrera', 'subasta.carrera_id', '=', 'carrera.id')
                            ->select('subasta.*', 'carrera.nro_carrera', 'carrera.fecha', 'carrera.distancia', 'carrera.hora')                            
                            ->first();

            if($subasta != null){
                $caballos_subastados = Caballo_subastado::Where('subasta_id', '=', $subasta->id)
                            ->where('caballo_subastado.borrado', '=', 0)
                            ->Join('caballo', 'caballo.id', '=', 'caballo_subastado.caballo_id')
                            ->Join('user', 'user.id', '=', 'caballo_subastado.user_id')
                            ->select('caballo_subastado.*', 'caballo.nro_caballo', 'caballo.name', 'caballo.retirado', 'user.name as usuario')
                            ->orderBy('id', 'asc')
                            ->get();

                return response()->json(
                    [
                        'Status_Code' => '200',
                        'Success'     => 'True',
                        'Response'    => ['Subasta' => $subasta, 'Caballos' => $caballos_subastados],
                        'Message'     => "Subastas asignadas exitosamente",
                    ], 200
                );
            }
        }
    }

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