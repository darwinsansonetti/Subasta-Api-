<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Rol;
use App\Models\Tipo_transaccion;
use App\Models\Transaccion;

class RolController extends Controller{

    //Obtiene todos los Roles registrados
    public function index($status){

        $roles = new Rol();

        //Status = 2 para consultar todos los roles
        if($status == 2){
            $roles = Rol::all();
        }else{
            //Status = 1 para consultar los roles inactivos. Status = 0 para consultar los roles Activos. 
            $roles = Rol::Where('activo', '=', $status)->get();
        }

        return response()->json(
            [
                'Status_Code' => '200',
                'Success'     => 'True',
                'Response'    => ['Roles' => $roles],
                'Message'     => "Listado de Roles",
            ], 200
        ); 
    }

    //Guardar Roles
    public function store(Request $request){

        if(auth()->user()->rol_id == 1){
            $new_rol = new Rol;

            if(!$request->input('name')){
                return response()->json(
                    [
                        'Status_Code' => '400',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Indique el nombre del Rol",
                    ], 400
                );
            }else{
                $rol_consultado = Rol::where('name',$request->name)->where('activo', '=', 1)->get();
    
                if(count($rol_consultado) > 0){
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => [],
                            'Message'     => "Rol ya existente.",
                        ], 400
                    ); 
                }else{
                    $new_rol->name = $request->name;
                }
            }
    
            if($new_rol->save()){

                //Se crea una Transaccion para el usuario, Tipo Create
                $new_transaccion = new Transaccion();
                $new_transaccion->monto = 0;
                $tipo_transaccion = Tipo_transaccion::Where('activo', '=', 1)->Where('name', '=', "Create")->first();
                $new_transaccion->tipo_transaccion_id = $tipo_transaccion->id;
                $new_transaccion->observacion = "Creacion del Rol ID " . Rol::latest('id')->first()->id . " - Admin ID " . auth()->user()->id;
                $new_transaccion->save();

                return response()->json(
                    [
                        'Status_Code' => '201',
                        'Success'     => 'True',
                        'Response'    => ['name' => $request->name],
                        'Message'     => "Rol guardado exitosamente",
                    ], 201
                ); 
            }else{
                return response()->json(
                    [
                        'Status_Code' => '400',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Error al guardar el nuevo Rol",
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

    //Consultar un Rol
    public function show($id){

        $rol_consultado = Rol::where('id', $id)->get();

        if(count($rol_consultado) > 0){
            return response()->json(
                [
                    'Status_Code' => '200',
                    'Success'     => 'True',
                    'Response'    => ['Rol' => $rol_consultado],
                    'Message'     => "Rol encontrado",
                ], 200
            ); 
        }else{
            return response()->json(
                [
                    'Status_Code' => '404',
                    'Success'     => 'False',
                    'Response'    => [],
                    'Message'     => "Rol no encontrado",
                ], 404
            ); 
        }
    }

    //Borrar un Rol
    public function delete($id){

        if(auth()->user()->rol_id == 1){
            $data = Rol::where('id',$id)->first();

            if($data != null){
                if($data->activo == 1){
                    $data->activo = 0;
    
                    if($data->save()){

                        //Se crea una Transaccion para el usuario, Tipo Delete
                        $new_transaccion = new Transaccion();
                        $new_transaccion->monto = 0;
                        $tipo_transaccion = Tipo_transaccion::Where('activo', '=', 1)->Where('name', '=', "Delete")->first();
                        $new_transaccion->tipo_transaccion_id = $tipo_transaccion->id;
                        $new_transaccion->observacion = "Eliminacion del Rol ID " . $id . " - Admin ID " . auth()->user()->id;
                        $new_transaccion->save();

                        return response()->json(
                            [
                                'Status_Code' => '201',
                                'Success'     => 'True',
                                'Response'    => ['Rol' => $data],
                                'Message'     => "Rol borrado exitosamente",
                            ], 201
                        ); 
                    }else{
                        return response()->json(
                            [
                                'Status_Code' => '400',
                                'Success'     => 'False',
                                'Response'    => [],
                                'Message'     => "Error al borrar el Rol",
                            ], 400
                        ); 
                    }
                }else{
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => ['Rol' => $data],
                            'Message'     => "El Rol se encuentra inactivo",
                        ], 400
                    );
                }
            }else{
                return response()->json(
                    [
                        'Status_Code' => '404',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Rol no encontrado",
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

    //Actualizar un Rol
    public function update(Request $request, $id){

        if(auth()->user()->rol_id == 1){
            $data = Rol::where('id',$id)->first();

            if($data != null){
                if(!$request->input('name')){
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => [],
                            'Message'     => "Indique el nombre del Rol",
                        ], 400
                    );
                }else{
                    $rol_consultado = Rol::where('name',$request->name)->first();
        
                    if($rol_consultado != null && (int)$id <> (int)$rol_consultado->id){
                        return response()->json(
                            [
                                'Status_Code' => '400',
                                'Success'     => 'False',
                                'Response'    => [$rol_consultado->id],
                                'Message'     => "Rol ya existente.",
                            ], 400
                        ); 
                    }else{
                        $data->name = $request->name;
                    }
                }
    
                if($data->save()){

                    //Se crea una Transaccion para el usuario, Tipo Update
                    $new_transaccion = new Transaccion();
                    $new_transaccion->monto = 0;
                    $tipo_transaccion = Tipo_transaccion::Where('activo', '=', 1)->Where('name', '=', "Update")->first();
                    $new_transaccion->tipo_transaccion_id = $tipo_transaccion->id;
                    $new_transaccion->observacion = "Actualizacion del Rol ID " . $id . " - Admin ID " . auth()->user()->id;
                    $new_transaccion->save();

                    return response()->json(
                        [
                            'Status_Code' => '201',
                            'Success'     => 'True',
                            'Response'    => ['name' => $request->name],
                            'Message'     => "Rol actualizado exitosamente",
                        ], 201
                    ); 
                }else{
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => [],
                            'Message'     => "Error al guardar el nuevo Rol",
                        ], 400
                    ); 
                }   
            }else{
                return response()->json(
                    [
                        'Status_Code' => '404',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Rol no encontrado",
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