<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hipodromo;
use App\Models\Carrera;
use App\Models\Caballo;
use App\Models\Tipo_apuesta;
use App\Models\Jugadas;
use App\Models\Subasta;
use App\Models\Caballo_subastado;
use Illuminate\Support\Facades\DB;

class CarreraController extends Controller{

    //Guardar una Carrera
    public function store(Request $request){

        $hipodromo_consultado = new Hipodromo();

        if(auth()->user()->rol_id == 1){
            $new_carrera = new Carrera;
            $validator = "";

            if(!$request->input('nro_carrera')){
                $validator = "Nro de carrera requerido";
            }

            if(!$request->input('fecha')){
                $validator = ($validator == "") ? $validator . "Fecha de la carrera requerida" : $validator . " - Fecha de la carrera requerida";
            }

            if(!$request->input('distancia')){
                $validator = ($validator == "") ? $validator . "Distancia de la carrera requerida" : $validator . " - Distancia de la carrera requerida";
            }

            if(!$request->input('hora')){
                $validator = ($validator == "") ? $validator . "Hora de la carrera requerida" : $validator . " - Hora de la carrera requerida";
            }

            if(!$request->input('hipodromo_id')){
                $validator = ($validator == "") ? $validator . "ID del hipodromo requerido" : $validator . " - ID del hipodromo requerido";
            }else{
                $hipodromo_consultado = Hipodromo::where('id',$request->hipodromo_id)->get();

                if(count($hipodromo_consultado) < 1){
                    $validator = ($validator == "") ? $validator . "No existe el hipodromo" : $validator . " - No existe el hipodromo";
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
                $new_carrera->nro_carrera = $request->nro_carrera;
                $new_carrera->fecha = $request->fecha;
                $new_carrera->distancia = $request->distancia;
                $new_carrera->hora = $request->hora;
                $new_carrera->hipodromo_id = $request->hipodromo_id;
    
                if($new_carrera->save()){

                    //Se obtiene el ID de la Jugada Subasta
                    $tipo_jugada = Tipo_apuesta::Where('activo', '=', 1)->Where('name', '=', "Subasta")->first();

                    if($tipo_jugada != null){
                        //Consultar si el Hipodromo al cual pertenece la carrera, tiene la jugada de subastas Activa
                        $jugadas = DB::table('jugadas')
                        ->where('hipodromo_id', '=', $request->input('hipodromo_id'))
                        ->where('tipo_apuesta_id', '=', $tipo_jugada->id)
                        ->where('activa', '=', 1)
                        ->first();

                        //El hipodromo tiene las Subastas activas. Se crea el registro de subasta
                        if($jugadas != null){
                            $new_subasta = new Subasta;

                            $new_subasta->carrera_id = $new_carrera->id;

                            $new_subasta->save();
                        }
                    }

                    return response()->json(
                        [
                            'Status_Code' => '201',
                            'Success'     => 'True',
                            'Response'    => ['Carrera' => $new_carrera],
                            'Message'     => "Carrera creada exitosamente",
                        ], 201
                    ); 
                }else{
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => [],
                            'Message'     => "Error al crear la nueva Carrera",
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

    //Consultar una Carrera especifica
    public function show($id){

        $carrera_consultada = Carrera::where('id', $id)->get();

        if(count($carrera_consultada) > 0){
            return response()->json(
                [
                    'Status_Code' => '200',
                    'Success'     => 'True',
                    'Response'    => ['Carrera' => $carrera_consultada],
                    'Message'     => "Carrera encontrada",
                ], 200
            ); 
        }else{
            return response()->json(
                [
                    'Status_Code' => '404',
                    'Success'     => 'False',
                    'Response'    => [],
                    'Message'     => "Carrera no encontrada",
                ], 404
            ); 
        }
    }

    //Borrar una carrera
    public function delete($id){

        if(auth()->user()->rol_id == 1){
            $data = Carrera::where('id',$id)->first();

            if($data != null){
                if($data->borrada == 0){
                    $data->activa = 0;
                    $data->borrada = 1;

                    if($data->save()){
                        return response()->json(
                            [
                                'Status_Code' => '200',
                                'Success'     => 'True',
                                'Response'    => ['Carrera' => $data],
                                'Message'     => "Carrera borrada exitosamente",
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
                            'Response'    => ['Carrera' => $data],
                            'Message'     => "La Carrera en estatus = BORRADO",
                        ], 400
                    );
                }
            }else{
                return response()->json(
                    [
                        'Status_Code' => '404',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Carrera no encontrada",
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

    //Actualizar una carrea
    public function update(Request $request, $id){

        if(auth()->user()->rol_id == 1){
            $data = Carrera::where('id',$id)->first();

            if($data != null){
                
                $validator = "";

                if(!$request->input('nro_carrera')){
                    $validator = "Nro de carrera requerido";
                }
    
                if(!$request->input('fecha')){
                    $validator = ($validator == "") ? $validator . "Fecha de la carrera requerida" : $validator . " - Fecha de la carrera requerida";
                }
    
                if(!$request->input('distancia')){
                    $validator = ($validator == "") ? $validator . "Distancia de la carrera requerida" : $validator . " - Distancia de la carrera requerida";
                }
    
                if(!$request->input('hora')){
                    $validator = ($validator == "") ? $validator . "Hora de la carrera requerida" : $validator . " - Hora de la carrera requerida";
                }
    
                if(!$request->input('hipodromo_id')){
                    $validator = ($validator == "") ? $validator . "ID del hipodromo requerido" : $validator . " - ID del hipodromo requerido";
                }else{
                    $hipodromo_consultado = Hipodromo::where('id',$request->hipodromo_id)->get();
    
                    if(count($hipodromo_consultado) < 1){
                        $validator = ($validator == "") ? $validator . "No existe el hipodromo" : $validator . " - No existe el hipodromo";
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
                    $data->nro_carrera = $request->nro_carrera;
                    $data->fecha = $request->fecha;
                    $data->distancia = $request->distancia;
                    $data->hora = $request->hora;
                    $data->hipodromo_id = $request->hipodromo_id;
        
                    if($data->save()){
                        return response()->json(
                            [
                                'Status_Code' => '201',
                                'Success'     => 'True',
                                'Response'    => ['Carrera' => $new_carrera],
                                'Message'     => "Carrera actualizada exitosamente",
                            ], 201
                        ); 
                    }else{
                        return response()->json(
                            [
                                'Status_Code' => '400',
                                'Success'     => 'False',
                                'Response'    => [],
                                'Message'     => "Error al actualizar la Carrera",
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
                        'Message'     => "Carrera no encontrado",
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

    //Desactivar una Carrera especifico.
    public function active($id){

        $data = Carrera::where('id',$id)->first();

        if($data != null){
            if($data->activa == 1){
                $data->activa = 0;

                if($data->save()){
                    //Tambienb se desactivan todos los caballos de esa Carrera
                    $caballos = Caballo::Where('carrera_id', '=', $id)
                                    ->where('borrado', '=', 0)
                                    ->update([
                                        'activo' => 0
                                    ]);

                    return response()->json(
                        [
                            'Status_Code' => '201',
                            'Success'     => 'True',
                            'Response'    => ['Carrera' => $data],
                            'Message'     => "Carrera DESACTIVADO exitosamente",
                        ], 201
                    ); 
                }else{
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => [],
                            'Message'     => "Error al desactivar la Carrera",
                        ], 400
                    ); 
                }                
            }else{
                return response()->json(
                    [
                        'Status_Code' => '200',
                        'Success'     => 'True',
                        'Response'    => ['Carrera' => $data],
                        'Message'     => "La Carrera se encuentra DESACTIVADA",
                    ], 400
                );
            }             
        }else{
            return response()->json(
                [
                    'Status_Code' => '404',
                    'Success'     => 'False',
                    'Response'    => [],
                    'Message'     => "Carrera no encontrada",
                ], 404
            ); 
        } 
    }

    //Obtiene todos los Caballos de una Carrera segun la llegada final
    public function finish_result($id){

        if(auth()->user()->rol_id == 1){

            $carrera_consultado = Carrera::where('id',$id)->first();

            if($carrera_consultado == null){
                return response()->json(
                    [
                        'Status_Code' => '400',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Carrera no encontrada.",
                    ], 403
                );
            }else{
                // La carrera aun no esta confirmada
                if($carrera_consultado->confirmado == 0){
                    return response()->json(
                        [
                            'Status_Code' => '200',
                            'Success'     => 'True',
                            'Response'    => [],
                            'Message'     => "Carrera sin confirmar",
                        ], 200
                    );
                }else{
                    $caballos = Caballo::Where('carrera_id', '=', $carrera_id)
                    ->where('borrado', '=', 0)
                    ->orderBy('puesto_llegada', 'asc')
                    ->get();

                    return response()->json(
                        [
                            'Status_Code' => '200',
                            'Success'     => 'True',
                            'Response'    => ['Caballos' => $caballos],
                            'Message'     => "Caballos segun el orden de llegada",
                        ], 200
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

    //Obtiene todos los Caballos de una Carrera
    public function index_show($id){

        if(auth()->user()->rol_id == 1){

            $carrera_consultado = Carrera::where('id',$id)->get();

            if(count($carrera_consultado) < 1){
                return response()->json(
                    [
                        'Status_Code' => '400',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Carrera no encontrada.",
                    ], 403
                );
            }else{
                $caballos = Caballo::Where('carrera_id', '=', $id)
                ->where('borrado', '=', 0)
                ->orderBy('id', 'asc')
                ->get();

                return response()->json(
                    [
                        'Status_Code' => '200',
                        'Success'     => 'True',
                        'Response'    => ['Caballos' => $caballos],
                        'Message'     => "Listado de Caballos por carrera",
                    ], 200
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

    //Consultar los caballos subastados de una Carrera
    public function show_subasta($id){

        if(auth()->user()->rol_id == 1){

            $carrera_consultado = Carrera::where('id',$id)->first();

            if($carrera_consultado != null){

                $subasta = Subasta::Where('carrera_id', '=', $id)
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
                            'Message'     => "Informacion de la subasta",
                        ], 200
                    );
                }else{
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => [],
                            'Message'     => "La carrera no posee Subasta.",
                        ], 403
                    );
                }
            }else{               

                return response()->json(
                    [
                        'Status_Code' => '400',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Carrera no encontrada.",
                    ], 403
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