<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jugadas;
use App\Models\Hipodromo;
use App\Models\Tipo_apuesta;
use Illuminate\Support\Facades\DB;

class JugadasController extends Controller{

    //Obtiene todos los Tipo de Jugadas de un Hipodromo
    public function index(Request $request){

        $jugadas = new Jugadas();
        $validator = "";
        
        if(!$request->input('hipodromo_id')){
            $validator = "ID del hipodromo es requerido";
        }else{
            $hipodromo_consultado = Hipodromo::where('id',$request->hipodromo_id)->get();

            if(count($hipodromo_consultado) < 1){
                $validator = ($validator == "") ? $validator . "No existe el hipodromo" : $validator . " - No existe el hipodromo";
            }
        }

        if(!$request->input('status')){
            $validator = ($validator == "") ? $validator . "Filtro status requerido" : $validator . " - Filtro status requerido";
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
            //status = 2 se consultan todas las jugadas de un Hipodromo
            if($request->input('status') == 2){
                $jugadas = DB::table('jugadas')
                ->where('hipodromo_id', '=', $request->input('hipodromo_id'))
                ->Join('hipodromo', 'jugadas.hipodromo_id', '=', 'hipodromo.id')
                ->Join('tipo_apuesta', 'jugadas.tipo_apuesta_id', '=', 'tipo_apuesta.id')            
                ->select('jugadas.id', 'jugadas.hipodromo_id', 'hipodromo.name AS hipodromo', 'jugadas.tipo_apuesta_id', 'tipo_apuesta.name AS jugada', 'jugadas.activa')
                ->get();
            }else{
                $jugadas = DB::table('jugadas')
                ->where('hipodromo_id', '=', $request->input('hipodromo_id'))
                ->where('activa', '=', $request->input('status'))
                ->Join('hipodromo', 'jugadas.hipodromo_id', '=', 'hipodromo.id')
                ->Join('tipo_apuesta', 'jugadas.tipo_apuesta_id', '=', 'tipo_apuesta.id')            
                ->select('jugadas.id', 'jugadas.hipodromo_id', 'hipodromo.name AS hipodromo', 'jugadas.tipo_apuesta_id', 'tipo_apuesta.name AS jugada', 'jugadas.activa')
                ->get();
            }

            return response()->json(
                [
                    'Status_Code' => '200',
                    'Success'     => 'True',
                    'Response'    => ['Jugada_por_Hipodromo' => $jugadas],
                    'Message'     => "Listado de Jugadas por Hipodromo",
                ], 200
            ); 
        }
    }

    //Guardar un Tipo de jugada para un Hipodromo
    public function store(Request $request){

        if(auth()->user()->rol_id == 1){
            $new_jugada = new Jugadas;
            $validator = "";

            if(!$request->input('hipodromo_id')){
                $validator = "ID del hipodromo requerido";
            }else{
                if(is_null(Hipodromo::where('id', $request->hipodromo_id)->first())){
                    $validator = ($validator == "") ? $validator . "Hipodromo no existente." : $validator . " - Hipodromo no existente.";
                }
            }            

            if(!$request->input('tipo_apuesta_id')){
                $validator = ($validator == "") ? $validator . "ID del tipo de apuesta requerido" : $validator . " - ID del tipo de apuesta requerido";
            }else{
                if(is_null(Tipo_apuesta::where('id', $request->tipo_apuesta_id)->first())){
                    $validator = ($validator == "") ? $validator . "Tipo de jugada no existente." : $validator . " - Tipo de jugada no existente.";
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
                $jugada = Jugadas::Where('hipodromo_id', $request->hipodromo_id)->where('tipo_apuesta_id', '=', $request->tipo_apuesta_id)->first();         

                if(!is_null($jugada))
                {
                    if($jugada->activa == 1){
                        return response()->json(
                            [
                                'Status_Code' => '200',
                                'Success'     => 'True',
                                'Response'    => ['Jugada' => $jugada],
                                'Message'     => "La jugada existe y se encuentra Activa.",
                            ], 200
                        );
                    }else{
                        return response()->json(
                            [
                                'Status_Code' => '200',
                                'Success'     => 'True',
                                'Response'    => ['Jugada' => $jugada],
                                'Message'     => "La jugada existe y se encuentra Inactiva.",
                            ], 200
                        );
                    }
                }
                else{
                    $new_jugada->hipodromo_id = $request->hipodromo_id;
                    $new_jugada->tipo_apuesta_id = $request->tipo_apuesta_id;

                    if($new_jugada->save()){
                        return response()->json(
                            [
                                'Status_Code' => '201',
                                'Success'     => 'True',
                                'Response'    => ['Jugada' => $new_jugada],
                                'Message'     => "Jugada creada exitosamente para ese Hipodromo.",
                            ], 201
                        ); 
                    }else{
                        return response()->json(
                            [
                                'Status_Code' => '400',
                                'Success'     => 'False',
                                'Response'    => [],
                                'Message'     => "Error al guardar la nuevo Jugada para ese hipodromo.",
                            ], 400
                        ); 
                    }
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

    //Borrar un Tipo de Jugada de un Hipodromo
    public function delete($id){

        if(auth()->user()->rol_id == 1){
            $data = Jugadas::where('id',$id)->first();

            if($data != null){
                if($data->activa == 1){
                    $data->activa = 0;

                    if($data->save()){
                        return response()->json(
                            [
                                'Status_Code' => '200',
                                'Success'     => 'True',
                                'Response'    => ['Jugada_hipodromo' => $data],
                                'Message'     => "Tipo de jugada borrada exitosamente para ese hipodromo.",
                            ], 200
                        );  
                    }else{
                        return response()->json(
                            [
                                'Status_Code' => '400',
                                'Success'     => 'False',
                                'Response'    => [],
                                'Message'     => "Error al borrar el Tipo de jugada de ese hipodromo.",
                            ], 400
                        ); 
                    }
                }else{
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => ['Tipo_Apuesta' => $data],
                            'Message'     => "El Tipo de jugada se encuentra inactivo para ese hipodromo.",
                        ], 400
                    );
                }
            }else{
                return response()->json(
                    [
                        'Status_Code' => '404',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Tipo de jugada no encontrada para ese hipodromo.",
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

    //Activar un Tipo de jugada para un Hipodromo especifico. Se cambio el estado a ACTIVO
    public function active($id){

        $data = Jugadas::where('id',$id)->first();

        if($data != null){
            if($data->activa == 1){
                return response()->json(
                    [
                        'Status_Code' => '200',
                        'Success'     => 'True',
                        'Response'    => ['Tipo_jugada' => $data],
                        'Message'     => "El tipo de jugada del Hipodromo se encuentra ACTIVO",
                    ], 400
                );
            }else{
                $data->activa = 1;

                if($data->save()){
                    return response()->json(
                        [
                            'Status_Code' => '201',
                            'Success'     => 'True',
                            'Response'    => ['Tipo_jugada' => $data],
                            'Message'     => "Tipo de jugada para el Hipodromo ACTIVADA exitosamente",
                        ], 201
                    ); 
                }else{
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => [],
                            'Message'     => "Error al activar el tipo de jugada para el Hipodromo",
                        ], 400
                    ); 
                }
            }             
        }else{
            return response()->json(
                [
                    'Status_Code' => '404',
                    'Success'     => 'False',
                    'Response'    => [],
                    'Message'     => "Tipo de jugada del Hipodromo no encontrado",
                ], 404
            ); 
        } 
    }
}