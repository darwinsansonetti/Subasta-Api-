<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hipodromo;
use App\Models\Carrera;
use App\Models\Caballo;
use App\Models\Subasta;
use App\Models\Caballo_subastado;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Support\Facades\DB;

class CaballoController extends Controller{

    //Guardar un Caballo de una carrera
    public function store(Request $request){

        $carrera_consultado = new Carrera();

        if(auth()->user()->rol_id == 1){
            $new_caballo = new Caballo;
            $validator = "";

            if(!$request->input('name')){
                $validator = "Nombre del caballo requerido";
            }

            if(!$request->input('nro_caballo')){
                $validator = ($validator == "") ? $validator . "Nro del caballo requerido" : $validator . " - Nro del caballo requerido";
            }

            if(!$request->input('jinete')){
                $validator = ($validator == "") ? $validator . "Nombre del jinete requerido" : $validator . " - Nombre del jinete requerido";
            }

            if(!$request->input('carrera_id')){
                $validator = ($validator == "") ? $validator . "ID de la carrera requerido" : $validator . " - ID de la carrera requerido";
            }else{
                $carrera_consultado = Carrera::where('id',$request->carrera_id)->get();

                if(count($carrera_consultado) < 1){
                    $validator = ($validator == "") ? $validator . "No existe la carrera" : $validator . " - No existe la carrera";
                }
            }

            if($validator != ""){
                return response()->json(
                    [
                        'Status_Code' => '400',
                        'Success'     => 'False',
                        'Response'    => ['Errores' => $validator],
                        'Message'     => "Existen errores",
                    ], 400
                ); 
            }else{
                $new_caballo->name = $request->name;
                $new_caballo->nro_caballo = $request->nro_caballo;
                $new_caballo->jinete = $request->jinete;
                $new_caballo->carrera_id = $request->carrera_id;
    
                if($new_caballo->save()){

                    //Si la Carrera tiene Subasta, se incrementa el total
                    $subasta = Subasta::where('carrera_id', '=', $request->carrera_id)
                                    ->where('activa', '=', 1)
                                    ->first();

                    //Si la carrera tiene subasta, sumamos 5 bs al total
                    if($subasta != null){
                        
                        $subasta->total += 5;
                        $porcentaje_resta = round((($subasta->total * $subasta->porcentaje) / 100), 2);
                        $subasta->premio = round(($subasta->total - $porcentaje_resta), 2);
                        $subasta->save();

                        $new_caballo_subastado = new Caballo_subastado;
                        $new_caballo_subastado->monto_subastado = 5;
                        $new_caballo_subastado->subasta_id = Subasta::latest('id')->first()->id;
                        $new_caballo_subastado->caballo_id = Caballo::latest('id')->first()->id;

                        //Obtener el ID del Rol Admin para asignarselo al caballo subastado
                        $rol_admin = Rol::where('name',"Admin")->where('activo', '=', 1)->first();
                        if($rol_admin != null){
                            $user_admin = User::Where('activo', '=', 1)->Where('rol_id', '=', $rol_admin->id)->first();
                            
                            $new_caballo_subastado->user_id = $user_admin->id;
                        }

                        $new_caballo_subastado->save();
                    }

                    return response()->json(
                        [
                            'Status_Code' => '201',
                            'Success'     => 'True',
                            'Response'    => ['Caballo' => $new_caballo],
                            'Message'     => "Caballo creado exitosamente",
                        ], 201
                    ); 
                }else{
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => [],
                            'Message'     => "Error al guardar la informacion del caballo",
                        ], 400
                    ); 
                }
            }
        }else{
            return response()->json(
                [
                    'Status_Code' => '403',
                    'Success'     => 'False',
                    'Response'    => [],
                    'Message'     => "No posee los permisos necasarios para acceder.",
                ], 403
            );
        }        
    }

    //Consultar un Caballo especifico
    public function show($id){

        $caballo_consultada = Caballo::where('id', $id)->get();

        if(count($caballo_consultada) > 0){
            return response()->json(
                [
                    'Status_Code' => '200',
                    'Success'     => 'True',
                    'Response'    => ['Caballo' => $caballo_consultada],
                    'Message'     => "Caballo encontrada",
                ], 200
            ); 
        }else{
            return response()->json(
                [
                    'Status_Code' => '404',
                    'Success'     => 'False',
                    'Response'    => [],
                    'Message'     => "Caballo no encontrada",
                ], 404
            ); 
        }
    }

    //Borrar un caballo
    public function delete($id){

        if(auth()->user()->rol_id == 1){
            $data = Caballo::where('id',$id)->first();

            if($data != null){
                if($data->borrada == 0){
                    $data->borrada = 1;

                    if($data->save()){

                        //Se verifica si el caballo tiene Subasta
                        $caballo_subastado = Caballo_subastado::where('caballo_id', '=', $id)
                        ->where('borrado', '=', 0)
                        ->first();

                        //Si el caballo tiene subasta, se elimina la subasta y se devuelve el dinero
                        if($caballo_subastado != null){

                            //Restamos el valor subastado del caballo al total de la subasta
                            $subasta = Subasta::where('id', '=', $caballo_subastado->subasta_id)
                            ->where('activa', '=', 1)
                            ->first();

                            $subasta->total = $subasta->total - $caballo_subastado->monto_subastado;
                            $porcentaje_resta = round((($subasta->total * $subasta->porcentaje) / 100), 2);
                            $subasta->premio = round(($subasta->total - $porcentaje_resta), 2);
                            $subasta->save();

                            //Se busca el Usuario para devolver el dinero
                            $user_consultado = User::where('id', $caballo_subastado->user_id)->first();

                            if($user_consultado != null){

                                $user_consultado->saldo += $caballo_subastado->$monto_subastado;
                                $user_consultado->save();
                            }

                            $caballo_subastado->monto_subastado = 0;
                            $caballo_subastado->save();
                        }

                        return response()->json(
                            [
                                'Status_Code' => '200',
                                'Success'     => 'True',
                                'Response'    => ['Caballo' => $data],
                                'Message'     => "Caballo borrado exitosamente",
                            ], 200
                        );  
                    }else{
                        return response()->json(
                            [
                                'Status_Code' => '400',
                                'Success'     => 'False',
                                'Response'    => [],
                                'Message'     => "Error al borrar la carrera",
                            ], 400
                        ); 
                    }
                }else{
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => ['Caballo' => $data],
                            'Message'     => "El Caballo se encuentra en estatus = BORRADO",
                        ], 400
                    );
                }
            }else{
                return response()->json(
                    [
                        'Status_Code' => '404',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Caballo no encontrado",
                    ], 404
                ); 
            } 
        }else{
            return response()->json(
                [
                    'Status_Code' => '403',
                    'Success'     => 'False',
                    'Response'    => [],
                    'Message'     => "No posee los permisos necasarios para acceder.",
                ], 403
            );
        }   
    }

    //Actualizar la informacion de un caballo
    public function update(Request $request, $id){

        if(auth()->user()->rol_id == 1){
            $data = Caballo::where('id',$id)->first();

            if($data != null){

                $carrera_consultado = Carrera::where('id',$data->carrera_id)->get();

                if($request->input('name')){
                    $data->name = $request->input('name');
                }

                if($request->input('jinete')){
                    $data->jinete = $request->input('jinete');
                }

                if($request->input('retirado')){

                    if($request->input('retirado') == 1 && $data->retirado == 0){
                        $data->retirado = $request->input('retirado');

                        //Se verifica si el caballo tiene Subasta
                        $caballo_subastado = Caballo_subastado::where('caballo_id', '=', $id)
                        ->where('borrado', '=', 0)
                        ->first();

                        //Si el caballo tiene subasta, se elimina la subasta y se devuelve el dinero
                        if($caballo_subastado != null){

                            //Restamos el valor subastado del caballo al total de la subasta
                            $subasta = Subasta::where('id', '=', $caballo_subastado->subasta_id)
                            ->where('activa', '=', 1)
                            ->first();

                            $subasta->total = $subasta->total - $caballo_subastado->$monto_subastado;
                            $porcentaje_resta = round((($subasta->total * $subasta->porcentaje) / 100), 2);
                            $subasta->premio = round(($subasta->total - $porcentaje_resta), 2);
                            $subasta->save();

                            //Se busca el Usuario para devolver el dinero
                            $user_consultado = User::where('id', $caballo_subastado->user_id)->first();

                            if($user_consultado != null){

                                $user_consultado->saldo += $caballo_subastado->$monto_subastado;
                                $user_consultado->save();
                            }

                            $caballo_subastado->monto_subastado = 0;                            
                            $caballo_subastado->borrado = 1;
                            $caballo_subastado->save();
                        }
                    }
                }

                if($request->input('puesto_llegada')){
                    $data->puesto_llegada = $request->input('puesto_llegada');
                }

                if($request->input('dividendo')){
                    $data->dividendo = $request->input('dividendo');
                }
    
                //Activo = 1, Inactivo = 0
                if($request->input('activo')){

                    if($carrera_consultado->activa == 1){
                        $data->activo = $request->input('activo');
                    }else{
                        return response()->json(
                            [
                                'Status_Code' => '400',
                                'Success'     => 'False',
                                'Response'    => [],
                                'Message'     => "La carrera a la que pertenece este caballo se encuentra CERRADA",
                            ], 400
                        ); 
                    }
                }

                if($data->save()){
                    return response()->json(
                        [
                            'Status_Code' => '201',
                            'Success'     => 'True',
                            'Response'    => ['Caballo' => $data],
                            'Message'     => "Caballo actualizado exitosamente",
                        ], 201
                    ); 
                }else{
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => [],
                            'Message'     => "Error al actualizar la informacion del Caballo",
                        ], 400
                    ); 
                } 
            }else{
                return response()->json(
                    [
                        'Status_Code' => '404',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Caballo no encontrado",
                    ], 404
                ); 
            } 
        }else{
            return response()->json(
                [
                    'Status_Code' => '403',
                    'Success'     => 'False',
                    'Response'    => [],
                    'Message'     => "No posee los permisos necasarios para acceder.",
                ], 403
            );
        }
    }

    // //Borrar un caballo. Operacion = 1, viene del Borrar un caballo. Operacion = 2, viene de un Retiro.
    // public function devolucion(Caballo_subastado $caballo_subastado, $operacion){

    //     //Restamos el valor subastado del caballo al total de la subasta
    //     $subasta = Subasta::where('id', '=', $caballo_subastado->subasta_id)
    //             ->where('activa', '=', 1)
    //             ->first();

    //     $subasta->total = $subasta->total - $caballo_subastado->$monto_subastado;
    //     $porcentaje_resta = round((($subasta->total * $subasta->porcentaje) / 100), 2);
    //     $subasta->premio = round(($subasta->total - $porcentaje_resta), 2);

    //     $subasta->save();


    //     //Se busca el Usuario para devolver el dinero
    //     $user_consultado = User::where('id', $caballo_subastado->user_id)->first();

    //     if($user_consultado != null){

    //         $user_consultado->saldo += $caballo_subastado->$monto_subastado;
    //         $user_consultado->save();
    //     }

    //     $caballo_subastado->monto_subastado = 0;

    //     //Caballo Retirado
    //     if($operacion == 2){
    //         $caballo_subastado->borrado = 1;
    //     }

    //     $caballo_subastado->save();
    // }
}