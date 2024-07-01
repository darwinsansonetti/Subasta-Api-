<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jugadas;
use App\Models\Hipodromo;
use App\Models\Tipo_apuesta;
use App\Models\Carrera;
use App\Models\Caballo;
use App\Models\Caballo_subastado;
use App\Models\Rol;
use App\Models\User;
use App\Models\Subasta;
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

                        //Si la Jugada es Subasta, se busca si hay carreras activas y se crea la Subasta y los Caballos Subastados
                        $tipo_jugada = Tipo_apuesta::Where('activo', '=', 1)->Where('name', '=', "Subasta")->first();

                        if($tipo_jugada != null && $tipo_jugada->id == $request->tipo_apuesta_id){

                            $carreras_x_subasta = Carrera::Where('hipodromo_id', $request->hipodromo_id)
                                                ->where('borrada', '=', 0)
                                                ->where('activa', '=', 1)
                                                ->select('carrera.id')
                                                ->pluck('id');
                                           
                            //Se buscan los ID de las Carreras que pertenecen a ese Hipodromo
                            if(count($carreras_x_subasta) > 0){ 
                                foreach ($carreras_x_subasta as $id_Carrera) {
                                    $subasta = Subasta::Where('carrera_id', '=', $id_Carrera)
                                                        ->where('activa', '=', 1)                            
                                                        ->first();

                                    //Si no existe el registro para la subasta. se crea
                                    if(is_null($subasta)){

                                        $new_subasta = new Subasta;
                                        $new_subasta->carrera_id = $id_Carrera;
                                        $new_subasta->save();

                                        $subasta = Subasta::Where('id', '=', $new_subasta->id)
                                        ->where('activa', '=', 1)                            
                                        ->first();
                                        
                                        //Se buscan los caballos de esa carrera
                                        $array_caballos = Caballo::Where('carrera_id', $id_Carrera)
                                        ->where('borrado', '=', 0)
                                        ->where('activo', '=', 1)
                                        ->select('caballo.id')
                                        ->pluck('id');

                                        //Si la carrera tiene caballos registrados, sumamos 5 bs al total
                                        if(count($array_caballos) > 0){

                                            foreach ($array_caballos as $id_Caballo) {

                                                $subasta->total += 5;
                                                $porcentaje_resta = round((($subasta->total * $subasta->porcentaje) / 100), 2);
                                                $subasta->premio = round(($subasta->total - $porcentaje_resta), 2);
                                                $subasta->save();

                                                $new_caballo_subastado = new Caballo_subastado;
                                                $new_caballo_subastado->monto_subastado = 5;
                                                $new_caballo_subastado->subasta_id = $subasta->id;
                                                $new_caballo_subastado->caballo_id = $id_Caballo;

                                                //Obtener el ID del Rol Admin para asignarselo al caballo subastado
                                                $rol_admin = Rol::where('name',"Admin")->where('activo', '=', 1)->first();
                                                if($rol_admin != null){
                                                    $user_admin = User::Where('activo', '=', 1)->Where('rol_id', '=', $rol_admin->id)->first();

                                                    $new_caballo_subastado->user_id = $user_admin->id;
                                                }

                                                $new_caballo_subastado->save();
                                            }
                                        }
                                    }else{                                        

                                        //Si existe el registro de la Subasta, se eliminan los registros de la tabla Caballo_subastado y Subasta
                                        DB::table('caballo_subastado')->where('subasta_id',$subasta->id)->update(['borrado'=>1]);
                                        DB::table('subasta')->where('id',$subasta->id)->update(['total'=>0, 'premio'=>0]);

                                        $subasta = Subasta::Where('carrera_id', '=', $id_Carrera)
                                        ->where('activa', '=', 1)                            
                                        ->first();

                                        //Se buscan los caballos de esa carrera
                                        $array_caballos = Caballo::Where('carrera_id', $id_Carrera)
                                        ->where('borrado', '=', 0)
                                        ->where('activo', '=', 1)
                                        ->select('caballo.id')
                                        ->pluck('id');

                                        //Si la carrera tiene caballos registrados, sumamos 5 bs al total
                                        if(count($array_caballos) > 0){

                                            foreach ($array_caballos as $id_Caballo) {

                                                $subasta->total += 5;
                                                $porcentaje_resta = round((($subasta->total * $subasta->porcentaje) / 100), 2);
                                                $subasta->premio = round(($subasta->total - $porcentaje_resta), 2);
                                                $subasta->save();

                                                $new_caballo_subastado = new Caballo_subastado;
                                                $new_caballo_subastado->monto_subastado = 5;
                                                $new_caballo_subastado->subasta_id = $subasta->id;
                                                $new_caballo_subastado->caballo_id = $id_Caballo;

                                                //Obtener el ID del Rol Admin para asignarselo al caballo subastado
                                                $rol_admin = Rol::where('name',"Admin")->where('activo', '=', 1)->first();
                                                if($rol_admin != null){
                                                    $user_admin = User::Where('activo', '=', 1)->Where('rol_id', '=', $rol_admin->id)->first();

                                                    $new_caballo_subastado->user_id = $user_admin->id;
                                                }

                                                $new_caballo_subastado->save();
                                            }
                                        }
                                    }
                                }
                            }
                        }

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