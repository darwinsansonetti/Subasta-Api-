<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tipo_transaccion;

class TipoTransaccionController extends Controller{

    //Obtiene todos los Tipo de Transacciones registrados
    public function index($status){

        $transacciones = new Tipo_transaccion();

        //Status = 2 para consultar todos los tipos de transaccion
        if($status == 2){
            $transacciones = Tipo_transaccion::all();
        }else{
            //Status = 1 para consultar los tipos de transaccion inactivos. Status = 0 para consultar los tipos de transaccion Activos. 
            $transacciones = Tipo_transaccion::Where('activo', '=', $status)->get();
        }

        return response()->json(
            [
                'Status_Code' => '200',
                'Success'     => 'True',
                'Response'    => ['Transacciones' => $transacciones],
                'Message'     => "Listado de Transacciones",
            ], 200
        ); 
    }

    //Guardar un Tipo de transaccion
    public function store(Request $request){

        if(auth()->user()->rol_id == 1){
            $new_transaccion = new Tipo_transaccion;

            if(!$request->input('name')){
                return response()->json(
                    [
                        'Status_Code' => '400',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Indique el nombre de la Transaccion",
                    ], 400
                );
            }else{
                $transaccion_consultado = Tipo_transaccion::where('name',$request->name)->where('activo', '=', 1)->get();

                if(count($transaccion_consultado) > 0){
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => [],
                            'Message'     => "Tipo de Transaccion ya existente.",
                        ], 400
                    ); 
                }else{
                    $new_transaccion->name = $request->name;
                }
            }

            if($new_transaccion->save()){
                return response()->json(
                    [
                        'Status_Code' => '201',
                        'Success'     => 'True',
                        'Response'    => ['name' => $request->name],
                        'Message'     => "Tipo de Transaccion guardada exitosamente",
                    ], 201
                ); 
            }else{
                return response()->json(
                    [
                        'Status_Code' => '400',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Error al guardar el nuevo Tipo de Transaccion",
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

    //Consultar un Tipo de Transaccion
    public function show($id){

        $Transaccion_consultado = Tipo_transaccion::where('id', $id)->get();

        if(count($Transaccion_consultado) > 0){
            return response()->json(
                [
                    'Status_Code' => '200',
                    'Success'     => 'True',
                    'Response'    => ['Transaccion' => $Transaccion_consultado],
                    'Message'     => "Tipo de transaccion encontrada",
                ], 200
            ); 
        }else{
            return response()->json(
                [
                    'Status_Code' => '404',
                    'Success'     => 'False',
                    'Response'    => [],
                    'Message'     => "Tipo de transaccion no encontrada",
                ], 404
            ); 
        }
    }

    //Borrar un Tipo de Transaccion
    public function delete($id){

        if(auth()->user()->rol_id == 1){
            $data = Tipo_transaccion::where('id',$id)->first();

            if($data != null){
                if($data->activo == 1){
                    $data->activo = 0;

                    if($data->save()){
                        return response()->json(
                            [
                                'Status_Code' => '200',
                                'Success'     => 'True',
                                'Response'    => ['Tipo_transaccion' => $data],
                                'Message'     => "Tipo de Transaccion borrada exitosamente",
                            ], 200
                        );  
                    }else{
                        return response()->json(
                            [
                                'Status_Code' => '400',
                                'Success'     => 'False',
                                'Response'    => [],
                                'Message'     => "Error al borrar el Tipo de Transaccion",
                            ], 400
                        ); 
                    }
                }else{
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => ['Tipo_transaccion' => $data],
                            'Message'     => "El Tipo de Transaccion se encuentra inactivo",
                        ], 400
                    );
                }



                $data->delete();

                return response()->json(
                    [
                        'Status_Code' => '200',
                        'Success'     => 'True',
                        'Response'    => ['Tipo_transaccion' => $data],
                        'Message'     => "Tipo de transaccion borrada exitosamente",
                    ], 200
                ); 
            }else{
                return response()->json(
                    [
                        'Status_Code' => '404',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Tipo de transaccion no encontrado",
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

    //Actualizar un Tipo de Transaccion
    public function update(Request $request, $id){

        if(auth()->user()->rol_id == 1){
            $data = Tipo_transaccion::where('id',$id)->first();

            if($data != null){
                if(!$request->input('name')){
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => [],
                            'Message'     => "Indique el nombre del Tipo de transaccion",
                        ], 400
                    );
                }else{
                    $Transaccion_consultado = Tipo_transaccion::where('name',$request->name)->first();
        
                    if($Transaccion_consultado != null && (int)$id <> (int)$Transaccion_consultado->id){
                        return response()->json(
                            [
                                'Status_Code' => '400',
                                'Success'     => 'False',
                                'Response'    => [$Transaccion_consultado->id],
                                'Message'     => "Tipo de transaccion ya existente.",
                            ], 400
                        ); 
                    }else{
                        // var_dump($data->name);

                        $data->name = $request->name;
                    }
                }

                if($data->save()){
                    return response()->json(
                        [
                            'Status_Code' => '201',
                            'Success'     => 'True',
                            'Response'    => ['name' => $request->name],
                            'Message'     => "Tipo de transaccion guardado exitosamente",
                        ], 201
                    ); 
                }else{
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => [],
                            'Message'     => "Error al guardar el nuevo Tipo de transaccion",
                        ], 400
                    ); 
                }   
            }else{
                return response()->json(
                    [
                        'Status_Code' => '404',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Tipo de transaccion no encontrado",
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