<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tipo_apuesta;
use App\Models\Tipo_transaccion;
use App\Models\Transaccion;

class TipoApuestaController extends Controller{

    //Obtiene todos los Tipo de Apuestas registradas
    public function index($status){

        $apuestas = new Tipo_apuesta();

        //Status = 2 para consultar todos los tipos de apuesta
        if($status == 2){
            $apuestas = Tipo_apuesta::all();
        }else{
            //Status = 1 para consultar los tipos de apuesta inactivos. Status = 0 para consultar los tipos de apuesta Activos. 
            $apuestas = Tipo_apuesta::Where('activo', '=', $status)->get();
        }

        return response()->json(
            [
                'Status_Code' => '200',
                'Success'     => 'True',
                'Response'    => ['Apuestas' => $apuestas],
                'Message'     => "Listado de Apuestas",
            ], 200
        ); 
    }

    //Guardar un Tipo de apuesta
    public function store(Request $request){

        if(auth()->user()->rol_id == 1){
            $new_apuesta = new Tipo_apuesta;

            if(!$request->input('name')){
                return response()->json(
                    [
                        'Status_Code' => '400',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Indique el nombre de la Apuesta",
                    ], 400
                );
            }else{
                $apuesta_consultado = Tipo_apuesta::where('name',$request->name)->where('activo', '=', 1)->get();

                if(count($apuesta_consultado) > 0){
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => [],
                            'Message'     => "Tipo de apuesta ya existente.",
                        ], 400
                    ); 
                }else{
                    $new_apuesta->name = $request->name;
                }
            }

            if($new_apuesta->save()){

                //Se crea una Transaccion para el usuario, Tipo Create
                $new_transaccion = new Transaccion();
                $new_transaccion->monto = 0;
                $tipo_transaccion = Tipo_transaccion::Where('activo', '=', 1)->Where('name', '=', "Create")->first();
                $new_transaccion->tipo_transaccion_id = $tipo_transaccion->id;
                $new_transaccion->observacion = "Creacion de un Tipo de apuesta ID " . Tipo_apuesta::latest('id')->first()->id . " - Admin ID " . auth()->user()->id;
                $new_transaccion->save();

                return response()->json(
                    [
                        'Status_Code' => '201',
                        'Success'     => 'True',
                        'Response'    => ['name' => $request->name],
                        'Message'     => "Tipo de Apuesta guardada exitosamente",
                    ], 201
                ); 
            }else{
                return response()->json(
                    [
                        'Status_Code' => '400',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Error al guardar el nuevo Tipo de Apuesta",
                    ], 400
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

    //Consultar un Tipo de Apuesta
    public function show($id){

        $Apuesta_consultado = Tipo_apuesta::where('id', $id)->get();

        if(count($Apuesta_consultado) > 0){
            return response()->json(
                [
                    'Status_Code' => '200',
                    'Success'     => 'True',
                    'Response'    => ['Apuesta' => $Apuesta_consultado],
                    'Message'     => "Tipo de apuesta encontrada",
                ], 200
            ); 
        }else{
            return response()->json(
                [
                    'Status_Code' => '404',
                    'Success'     => 'False',
                    'Response'    => [],
                    'Message'     => "Tipo de apuesta no encontrada",
                ], 404
            ); 
        }
    }

    //Borrar un Tipo de Apuesta
    public function delete($id){

        if(auth()->user()->rol_id == 1){
            $data = Tipo_apuesta::where('id',$id)->first();

            if($data != null){
                if($data->activo == 1){
                    $data->activo = 0;

                    if($data->save()){

                        //Se crea una Transaccion para el usuario, Tipo Delete
                        $new_transaccion = new Transaccion();
                        $new_transaccion->monto = 0;
                        $tipo_transaccion = Tipo_transaccion::Where('activo', '=', 1)->Where('name', '=', "Delete")->first();
                        $new_transaccion->tipo_transaccion_id = $tipo_transaccion->id;
                        $new_transaccion->observacion = "Eliminacion del tipo de apuesta ID " . $id . " - Admin ID " . auth()->user()->id;
                        $new_transaccion->save();

                        return response()->json(
                            [
                                'Status_Code' => '200',
                                'Success'     => 'True',
                                'Response'    => ['Tipo_Apuesta' => $data],
                                'Message'     => "Tipo de Apuesta borrada exitosamente",
                            ], 200
                        );  
                    }else{
                        return response()->json(
                            [
                                'Status_Code' => '400',
                                'Success'     => 'False',
                                'Response'    => [],
                                'Message'     => "Error al borrar el Tipo de Apuesta",
                            ], 400
                        ); 
                    }
                }else{
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => ['Tipo_Apuesta' => $data],
                            'Message'     => "El Tipo de Apuesta se encuentra inactivo",
                        ], 400
                    );
                }
            }else{
                return response()->json(
                    [
                        'Status_Code' => '404',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Tipo de Apuesta no encontrado",
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

    //Actualizar un Tipo de Apuesta
    public function update(Request $request, $id){

        if(auth()->user()->rol_id == 1){
            $data = Tipo_apuesta::where('id',$id)->first();

            if($data != null){
                if(!$request->input('name')){
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => [],
                            'Message'     => "Indique el nombre del Tipo de apuesta",
                        ], 400
                    );
                }else{
                    $Apuesta_consultado = Tipo_apuesta::where('name',$request->name)->first();
        
                    if($Apuesta_consultado != null && (int)$id <> (int)$Apuesta_consultado->id){
                        return response()->json(
                            [
                                'Status_Code' => '400',
                                'Success'     => 'False',
                                'Response'    => [$Apuesta_consultado->id],
                                'Message'     => "Tipo de apuesta ya existente.",
                            ], 400
                        ); 
                    }else{
                        // var_dump($data->name);

                        $data->name = $request->name;
                    }
                }

                if($data->save()){

                    //Se crea una Transaccion para el usuario, Tipo Update
                    $new_transaccion = new Transaccion();
                    $new_transaccion->monto = 0;
                    $tipo_transaccion = Tipo_transaccion::Where('activo', '=', 1)->Where('name', '=', "Update")->first();
                    $new_transaccion->tipo_transaccion_id = $tipo_transaccion->id;
                    $new_transaccion->observacion = "Actualziacion del tipo de apuesta ID " . $id . " - Admin ID " . auth()->user()->id;
                    $new_transaccion->save();

                    return response()->json(
                        [
                            'Status_Code' => '201',
                            'Success'     => 'True',
                            'Response'    => ['name' => $request->name],
                            'Message'     => "Tipo de Apuesta guardado exitosamente",
                        ], 201
                    ); 
                }else{
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => [],
                            'Message'     => "Error al guardar el nuevo Tipo de Apuesta",
                        ], 400
                    ); 
                }   
            }else{
                return response()->json(
                    [
                        'Status_Code' => '404',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Tipo de apuesta no encontrado",
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
}