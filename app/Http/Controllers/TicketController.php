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

class TicketController extends Controller{

    //Mostrar los Ticket por Fecha
    public function show(Request $request){

        $validator = "";
        $fecha_creacion = date("Y-m-d"); // 2001-03-10

        if($request->input('fecha_creacion')){
            $fecha_creacion = $request->input('fecha_creacion');
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
            //Se buscan los Ticket por Usuario y fecha
            $tickets = Ticket::Where('ticket.user_id', '=', $request->input('user_id'))
                                ->where('ticket.fecha_creacion', '=', $fecha_creacion)
                                ->where('ticket.activo', '=', 1)
                                ->Join('tipo_apuesta', 'tipo_apuesta.id', '=', 'ticket.tipo_apuesta_id')
                                ->Join('caballo', 'caballo.id', '=', 'ticket.caballo_id')
                                ->Join('carrera', 'carrera.id', '=', 'caballo.carrera_id')
                                ->select('ticket.*', 'tipo_apuesta.name as jugada', 'caballo.nro_caballo', 'caballo.name as name_caballo', 'carrera.nro_carrera')
                                ->orderBy('id', 'desc')
                                ->get();

            if($tickets != null){
                return response()->json(
                    [
                        'Status_Code' => '200',
                        'Success'     => 'True',
                        'Response'    => ['Tickets' => $tickets],
                        'Message'     => "Tickets",
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