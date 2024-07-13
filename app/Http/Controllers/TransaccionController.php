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
use App\Models\Ticket;
use App\Models\Tipo_transaccion;
use App\Models\Transaccion;
use App\Models\Tipo_apuesta;
use Illuminate\Support\Facades\DB;

class TransaccionController extends Controller{

    //Mostrar las Transacciones de un usuario por Fecha
    public function show(Request $request){

        $validator = "";
        $fecha_creacion = date("Y-m-d"); // 2001-03-10

        if($request->input('fecha_creacion')){
            $fecha_creacion =  $request->input('fecha_creacion');
        }

        if(!$request->input('user_id')){
            $validator = "ID del Usuario requerido.";
        }else{
            //Se busca el Usuario
            $user_consultado = User::where('id', $request->input('user_id'))->first();

            if($user_consultado == null){
                $validator = "No existe el Usuario.";
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
            //Se buscan las Transacciones por Usuario y fecha
            $transacciones = Transaccion::Where('transaccion.user_id', '=', $request->input('user_id'))
                                ->where('transaccion.fecha_creacion', '=', $fecha_creacion)
                                ->select('transaccion.id','transaccion.monto', 'transaccion.tipo_transaccion_id', 'transaccion.pivot_id_jugada as id_ticket', 'transaccion.observacion', 'transaccion.fecha_creacion')
                                ->orderBy('id', 'desc')
                                ->get();

            if($transacciones != null){
                return response()->json(
                    [
                        'Status_Code' => '200',
                        'Success'     => 'True',
                        'Response'    => ['Transacciones' => $transacciones],
                        'Message'     => "Transacciones",
                    ], 200
                );
            }else{
                return response()->json(
                    [
                        'Status_Code' => '200',
                        'Success'     => 'True',
                        'Response'    => [],
                        'Message'     => "El usuario no posee Ticket en la fecha consultada.",
                    ], 200
                );
            }            
        }
    }
}