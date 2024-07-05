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
use App\Models\Tipo_apuesta;
use App\Models\Transaccion;
use Illuminate\Support\Facades\DB;

class SubastaController extends Controller{

    //Guardar una Subasta
    public function store(Request $request){

        $validator = "";
        $indice = 0;
        $id_subasta = 0;

        foreach($request->input('subasta') as $valor_subasta)
        {
            $indice++;
            $id_subasta = $valor_subasta['subasta_id'];
            $new_subasta = new Subasta;
            
            if(is_null($valor_subasta['subasta_id'])){ 
                $validator = "ID de la Subasta requerido. Registro $indice";
            }else{
                $subasta = Subasta::Where('id', '=', $valor_subasta['subasta_id'])
                                    ->where('activa', '=', 1)                            
                                    ->first();

                if($subasta == null){
                    $validator = "No existe el ID de la Subasta. Registro $indice.";
                }
            }

            if(is_null($valor_subasta['caballo_subastado_id'])){ 
                $validator = ($validator == "") ? $validator . "ID del Caballo Subastado requerido. Registro $indice." : $validator . " - ID del Caballo Subastado requerido. Registro $indice.";
            }else{
                $caballo_subastado = Caballo_subastado::Where('id', '=', $valor_subasta['caballo_subastado_id'])
                                    ->where('borrado', '=', 0)                            
                                    ->first();

                if($caballo_subastado == null){
                    $validator = ($validator == "") ? $validator . "No existe el Caballo para la subasta. Registro $indice." : $validator . " - No existe el Caballo para la subasta. Registro $indice.";
                }
            }

            if(is_null($valor_subasta['user_id'])){ 
                $validator = ($validator == "") ? $validator . "ID del Usuario requerido. Registro $indice." : $validator . " - ID del Usuario requerido. Registro $indice.";
            }else{
                $user_consultado = User::where('id', $valor_subasta['user_id'])->first();

                if($user_consultado == null){
                    $validator = ($validator == "") ? $validator . "No existe el Usuario. Registro $indice." : $validator . " - No existe el Usuario. Registro $indice.";
                }
            }

            if(is_null($valor_subasta['monto'])){ 
                $validator = "Monto requerido. Registro $indice.";
            }else{
                if($valor_subasta['monto'] < 1 || ($caballo_subastado != null && $valor_subasta['monto'] <= $caballo_subastado->monto_subastado)){ 
                    $validator = ($validator == "") ? $validator . "Monto invalido. Registro $indice." : $validator . " - Monto invalido. Registro $indice.";
                }else{
                    if($user_consultado->saldo < $valor_subasta['monto']){
                        $validator = ($validator == "") ? $validator . "Saldo insuficiente. Registro $indice." : $validator . " - Saldo insuficiente. Registro $indice.";
                    }
                }
            }

            if($validator == ""){   

                //Se ubica el usuario y el monto anterior
                $user_old = $caballo_subastado->user_id;
                $monto_old = $caballo_subastado->monto_subastado;             

                //Se busca el ID de la Jugada Subasta
                $tipo_jugada = Tipo_apuesta::Where('activo', '=', 1)->Where('name', '=', "Subasta")->first();

                //Se desactiva el Ticket para el Usuario anterior
                $ticket_old = Ticket::Where('tipo_apuesta_id', '=', $tipo_jugada->id)
                ->where('caballo_id', '=', $caballo_subastado->caballo_id)
                ->where('user_id', '=', $user_old)
                ->update([
                    'activo' => 0,
                    'observacion' => "Jugada de Subasta Reembolsada"
                ]);

                //Restamos el valor subastado del caballo al total de la subasta y se le suma el nuevo monto
                $subasta->total = $subasta->total - $caballo_subastado->monto_subastado;
                $subasta->total += $valor_subasta['monto'];
                $porcentaje_resta = round((($subasta->total * $subasta->porcentaje) / 100), 2);
                $subasta->premio = round(($subasta->total - $porcentaje_resta), 2);
                $subasta->save();

                //Se busca el Usuario para devolver el dinero
                $user_consultado_old = User::where('id', $user_old)->first();

                if($user_consultado_old != null){
                    $user_consultado_old->saldo += $monto_old;
                    $user_consultado_old->save();
                }

                //Se resta el saldo al nuevo usuario q subasto
                $user_consultado->saldo -= $valor_subasta['monto'];
                $user_consultado->save();

                //Se asigna el monto y el usuario al caballo subastado
                $caballo_subastado->monto_subastado = $valor_subasta['monto'];
                $caballo_subastado->user_id = $valor_subasta['user_id'];
                $caballo_subastado->save();

                //Se crea un Ticket por la Jugada de Subasta
                $new_ticket = new Ticket;
                $new_ticket->fecha_creacion = date("Y-m-d"); // 2001-03-10
                $new_ticket->monto = $valor_subasta['monto'];
                $new_ticket->tipo_apuesta_id = $tipo_jugada->id;
                $new_ticket->caballo_id = $caballo_subastado->caballo_id;
                $new_ticket->user_id = $user_consultado->id;
                $new_ticket->save();

                //Se crea una Transaccion para el usuario
                $new_transaccion = new Transaccion;
                $new_transaccion->monto = $valor_subasta['monto'];
                $tipo_transaccion = Tipo_transaccion::Where('activo', '=', 1)->Where('name', '=', "Jugada Subasta")->first();
                $new_transaccion->tipo_transaccion_id = $tipo_transaccion->id;
                $new_transaccion->pivot_id_jugada = Ticket::latest('id')->first()->id;
                $new_transaccion->observacion = "Jugada de Subasta";
                $new_transaccion->fecha_creacion = date("Y-m-d"); // 2001-03-10
                $new_transaccion->user_id = $user_consultado->id;
                $new_transaccion->save();
            }
        }

        if($validator != ""){

            if($indice > 0){
                $subasta = Subasta::Where('subasta.id', '=', $id_subasta)
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
                            'Status_Code' => '409',
                            'Success'     => 'False',
                            'Response'    => ['Errores' => $validator, 'Subasta' => $subasta, 'Caballos' => $caballos_subastados],
                            'Message'     => "Existen errores, pero se pudieron haber creado subastas.",
                        ], 200
                    );
                }else{
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => ['Errores' => $validator],
                            'Message'     => "Existen errores",
                        ], 400
                    );
                }
            }else{
                return response()->json(
                    [
                        'Status_Code' => '400',
                        'Success'     => 'False',
                        'Response'    => ['Errores' => $validator],
                        'Message'     => "Existen errores",
                    ], 400
                ); 
            }
        }else{

            $subasta = Subasta::Where('subasta.id', '=', $id_subasta)
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
                        'Message'     => "Subastas asignadas exitosamente",
                    ], 200
                );
            }
        }
    }
}