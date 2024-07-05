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
use App\Models\Tipo_transaccion;
use App\Models\Transaccion;
use Illuminate\Support\Facades\DB;

class CaballosubastadoController extends Controller{

    //Guardar el puesto de llegada de un caballo subastado. Nota: Es como pase la raya
    public function store_subasta(Request $request, $id){

        if(auth()->user()->rol_id == 1){
            $data = Caballo_subastado::where('caballo_id',$id)
                                        ->where('borrado', '=', 0)
                                        ->first();

            if($data != null){

                if($request->input('puesto_llegada')){
                    $data->puesto_llegada = $request->input('puesto_llegada');
                }

                if($data->save()){

                    return response()->json(
                        [
                            'Status_Code' => '201',
                            'Success'     => 'True',
                            'Response'    => ['Caballo' => $data],
                            'Message'     => "Puesto de llegada del Caballo Subastado actualizado exitosamente",
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

    
}