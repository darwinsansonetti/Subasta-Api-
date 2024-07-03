<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Crypt;
use App\Models\User;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Hashing\HashManager;
// use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Str;
use App\Models\Tipo_transaccion;
use App\Models\Transaccion;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller{

    //Obtiene todos los Usuarios registrados
    public function index($status){

        if(auth()->user()->rol_id == 1){
            $usuarios = new User();

            //Status = 2 para consultar todos los usuarios
            if($status == 2){
                $usuarios = User::all();
            }else{
                //Status = 1 para consultar los usuarios inactivos. Status = 0 para consultar los usuarios Activos. 
                $usuarios = User::Where('activo', '=', $status)->get();
            }
    
            return response()->json(
                [
                    'Status_Code' => '200',
                    'Success'     => 'True',
                    'Response'    => ['Usuarios' => $usuarios],
                    'Message'     => "Listado de Usuarios",
                ], 200
            ); 
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

    //Guardar un nuevo Usuario
    public function store(Request $request)
    {
        $new_user = new User;
        $validator = "";

        if(!$request->input('name')){
            $validator = "Nombre requerido";
        }

        if(!$request->input('cedula')){
            $validator = ($validator == "") ? $validator . "Cedula identidad requerida" : $validator . " - Cedula identidad requerida";
        }else{
            $cedula_consultada = User::where('cedula',$request->cedula)->first();
    
            if($cedula_consultada != null){
                $validator = ($validator == "") ? $validator . "Cedula identidad existente" : $validator . " - Cedula identidad existente";
            }
        }

        if(!$request->input('email')){
            $validator = ($validator == "") ? $validator . "Email requerido" : $validator . " - Email requerido";
        }else{
            $email_consultado = User::where('email',$request->email)->first();
    
            if($email_consultado != null){
                $validator = ($validator == "") ? $validator . "Email existente" : $validator . " - Email existente";
            }
        }

        if(!$request->input('username')){
            $validator = ($validator == "") ? $validator . "Nombre de usuario requerido" : $validator . " - Nombre de usuario requerido";
        }else{
            $username_consultado = User::where('username',$request->username)->first();
    
            if($username_consultado != null){
                $validator = ($validator == "") ? $validator . "Nombre de usuario existente" : $validator . " - Nombre de usuario existente";
            }
        }

        if(!$request->input('password')){
            $validator = ($validator == "") ? $validator . "Contraseña requerida" : $validator . " - Contraseña requerida";
        }

        if(!$request->input('rol_id')){
            $validator = ($validator == "") ? $validator . "Id del Rol requerido" : $validator . " - Id del Rol requerido";
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
            $new_user->name = $request->name;
            $new_user->cedula = $request->cedula;
            $new_user->email = $request->email;
            $new_user->username = $request->username;
            $new_user->password = Hash::make($request->password);
            $new_user->rol_id = $request->rol_id;

            if($new_user->save()){
                return response()->json(
                    [
                        'Status_Code' => '201',
                        'Success'     => 'True',
                        'Response'    => ['Usuario' => $new_user],
                        'Message'     => "Usuario creado exitosamente",
                    ], 201
                ); 
            }else{
                return response()->json(
                    [
                        'Status_Code' => '400',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Error al crear el nuevo Usuario",
                    ], 400
                ); 
            }
        }
    }

    //Consultar un Usuario especifico
    public function show($id){

        $user_consultado = User::where('id', $id)->get();

        if(count($user_consultado) > 0){
            return response()->json(
                [
                    'Status_Code' => '200',
                    'Success'     => 'True',
                    'Response'    => ['User' => $user_consultado],
                    'Message'     => "Usuario encontrado",
                ], 200
            ); 
        }else{
            return response()->json(
                [
                    'Status_Code' => '404',
                    'Success'     => 'False',
                    'Response'    => [],
                    'Message'     => "Usuario no encontrado",
                ], 404
            );
        }

    }

    //Borrar un Usuario especifico. Este borrado es un cambio de estado a INACTIVO
    public function delete($id){

        if(auth()->user()->rol_id == 1){
            $data = User::where('id',$id)->first();

            if($data != null){
                if($data->activo == 1){
                    $data->activo = 0;

                    if($data->save()){
                        return response()->json(
                            [
                                'Status_Code' => '201',
                                'Success'     => 'True',
                                'Response'    => ['Usuario' => $data],
                                'Message'     => "Usuario borrado exitosamente",
                            ], 201
                        ); 
                    }else{
                        return response()->json(
                            [
                                'Status_Code' => '400',
                                'Success'     => 'False',
                                'Response'    => [],
                                'Message'     => "Error al borrar el Usuario",
                            ], 400
                        ); 
                    }
                }else{
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => ['Usuario' => $data],
                            'Message'     => "El usuario se encuentra inactivo",
                        ], 400
                    );
                }             
            }else{
                return response()->json(
                    [
                        'Status_Code' => '404',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Usuario no encontrado",
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

    //Actualizar la informacion de un Usuario
    public function update(Request $request, $id){

        $data = User::where('id',$id)->first();
        $validator = "";

        if($data != null){
            if($data->activo == 1){
                if(!$request->input('direccion')){
                    $validator = "Direccion requerida";
                }
    
                if(!$request->input('fecha_nacimiento')){
                    $validator = ($validator == "") ? $validator . "Fecha ded nacimiento requerida" : $validator . " - Fecha ded nacimiento requerida";
                }
    
                if(!$request->input('phone')){
                    $validator = ($validator == "") ? $validator . "Nro de telefono requerido" : $validator . " - Nro de telefono requerido";
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
                    $data->direccion = $request->direccion;
                    $data->fecha_nacimiento = $request->fecha_nacimiento;
                    $data->phone = $request->phone;
        
                    if($data->save()){
                        return response()->json(
                            [
                                'Status_Code' => '201',
                                'Success'     => 'True',
                                'Response'    => ['Usuario' => $data],
                                'Message'     => "Usuario actualizado exitosamente",
                            ], 201
                        ); 
                    }else{
                        return response()->json(
                            [
                                'Status_Code' => '400',
                                'Success'     => 'False',
                                'Response'    => [],
                                'Message'     => "Error al actualizar el usuario",
                            ], 400
                        ); 
                    } 
                }
            }else{
                return response()->json(
                    [
                        'Status_Code' => '400',
                        'Success'     => 'False',
                        'Response'    => ['Usuario' => $data],
                        'Message'     => "El usuario se encuentra inactivo",
                    ], 400
                );
            }              
        }else{
            return response()->json(
                [
                    'Status_Code' => '404',
                    'Success'     => 'False',
                    'Response'    => [],
                    'Message'     => "Usuario no encontrado",
                ], 404
            ); 
        } 
    }

    //Actualizar el Saldo de un Usuario (DEPOSITO / RETIRO)
    public function saldo(Request $request, $id){

        if(auth()->user()->rol_id == 1){
            $new_transaccion = new Transaccion;

            $data = User::where('id',$id)->first();
            $validator = "";

            if($data != null){
                if($data->activo == 1){

                    if(!$request->input('saldo')){
                        return response()->json(
                            [
                                'Status_Code' => '400',
                                'Success'     => 'False',
                                'Response'    => [],
                                'Message'     => "Saldo requerido",
                            ], 400
                        ); 
                    }
                    
                    if($request->input('saldo') < 50){
                        return response()->json(
                            [
                                'Status_Code' => '400',
                                'Success'     => 'False',
                                'Response'    => [],
                                'Message'     => "El monto debe ser mayor a 50 Bs",
                            ], 400
                        ); 
                    }

                    if(!$request->input('tipo_operacion')){
                        return response()->json(
                            [
                                'Status_Code' => '400',
                                'Success'     => 'False',
                                'Response'    => [],
                                'Message'     => "Tipo de Operacion requerido",
                            ], 400
                        ); 
                    }

                    //Tipo Operacion = 1 DEPOSITO
                    if($request->input('tipo_operacion') == 1){
                        $data->saldo += $request->input('saldo');

                        //Se crea una Transaccion para el usuario, Tipo Deposito
                        $new_transaccion->monto = $request->input('saldo');
                        $tipo_transaccion = Tipo_transaccion::Where('activo', '=', 1)->Where('name', '=', "Deposito")->first();
                        $new_transaccion->tipo_transaccion_id = $tipo_transaccion->id;
                        $new_transaccion->observacion = "Deposito de Saldo";
                    }else{
                        //Tipo Operacion = 2 RETIRO
                        if($request->input('tipo_operacion') == 2){
                            if($data->saldo < $request->input('saldo')){
                                return response()->json(
                                    [
                                        'Status_Code' => '400',
                                        'Success'     => 'False',
                                        'Response'    => [],
                                        'Message'     => "Saldo Insuficiente",
                                    ], 400
                                );
                            }else{
                                $data->saldo -= $request->input('saldo');

                                //Se crea una Transaccion para el usuario, Tipo Retiro
                                $new_transaccion->monto = $request->input('saldo');
                                $tipo_transaccion = Tipo_transaccion::Where('activo', '=', 1)->Where('name', '=', "Retiro")->first();
                                $new_transaccion->tipo_transaccion_id = $tipo_transaccion->id;
                                $new_transaccion->observacion = "Retiro de Saldo";
                            }
                        }else{
                            return response()->json(
                                [
                                    'Status_Code' => '400',
                                    'Success'     => 'False',
                                    'Response'    => [],
                                    'Message'     => "Tipo de Operacion Invalida",
                                ], 400
                            );
                        }
                    }
        
                    if($data->save()){

                        $new_transaccion->save();

                        return response()->json(
                            [
                                'Status_Code' => '201',
                                'Success'     => 'True',
                                'Response'    => ['Usuario' => $data],
                                'Message'     => "Operacion realizada exitosamente",
                            ], 201
                        ); 
                    }else{
                        return response()->json(
                            [
                                'Status_Code' => '400',
                                'Success'     => 'False',
                                'Response'    => [],
                                'Message'     => "Error al actualizar el usuario",
                            ], 400
                        ); 
                    }
                }else{
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => ['Usuario' => $data],
                            'Message'     => "El usuario se encuentra inactivo",
                        ], 400
                    );
                }              
            }else{
                return response()->json(
                    [
                        'Status_Code' => '404',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Usuario no encontrado",
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

    //Activar un Usuario especifico. Se cambio el estado a ACTIVO
    public function active($id){

        $data = User::where('id',$id)->first();

        if($data != null){
            if($data->activo == 1){
                return response()->json(
                    [
                        'Status_Code' => '200',
                        'Success'     => 'True',
                        'Response'    => ['Usuario' => $data],
                        'Message'     => "El usuario se encuentra ACTIVO",
                    ], 400
                );
            }else{
                $data->activo = 1;

                if($data->save()){
                    return response()->json(
                        [
                            'Status_Code' => '201',
                            'Success'     => 'True',
                            'Response'    => ['Usuario' => $data],
                            'Message'     => "Usuario ACTIVADO exitosamente",
                        ], 201
                    ); 
                }else{
                    return response()->json(
                        [
                            'Status_Code' => '400',
                            'Success'     => 'False',
                            'Response'    => [],
                            'Message'     => "Error al activar el Usuario",
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
                    'Message'     => "Usuario no encontrado",
                ], 404
            ); 
        } 
    }

    //Login authenticate
    public function login(Request $request){

        $validator = "";

        if(!$request->input('email')){
            $validator = "Email requerido";
        }

        if(!$request->input('password')){
            $validator = ($validator == "") ? $validator . "Password requerido" : $validator . " - Password requerido";
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
            $user = User::Where('email', $request->email)->where('activo', '=', 1)->first();         

            if(!is_null($user) && Hash::check($request->password, $user->password))
            {
                $user->api_token = Str::random(150);
                $user->save();

                return response()->json(
                    [
                        'Status_Code' => '200',
                        'Success'     => 'True',
                        'Response'    => ['User' => $user],
                        'Message'     => "Bienvenido al sistema",
                    ], 200
                );
            }
            else{
                return response()->json(
                    [
                        'Status_Code' => '400',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Credenciales no encontradas",
                    ], 400
                );
            } 
        }
    }

    //Logout Sesion
    public function logout()
    {
        $user = auth()->user(); 
        
        $user->api_token = null;
        $user->save();

        return response()->json(
            [
                'Status_Code' => '200',
                'Success'     => 'True',
                'Response'    => ['user' => $user],
                'Message'     => "Sesion cerrada",
            ], 200
        );
    }

    //Resetear password
    public function reset(Request $request)
    {
        $validator = "";

        if(!$request->input('email')){
            $validator = "Email requerido";
        }

        if(!$request->input('cedula')){
            $validator = ($validator == "") ? $validator . "Cedula Identidad requerida" : $validator . " - Cedula Identidad requerida";
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
            $user = User::Where('email', $request->email)->where('cedula', '=', $request->cedula)->where('activo', '=', 1)->first();         

            if(!is_null($user))
            {
                $user->password = Hash::make("12345678");
                $user->save();

                return response()->json(
                    [
                        'Status_Code' => '200',
                        'Success'     => 'True',
                        'Response'    => ['Password' => "12345678"],
                        'Message'     => "Su nuevo password es 12345678. Por su seguridad, ingrese y realice el cambio del password.",
                    ], 200
                );
            }
            else{
                return response()->json(
                    [
                        'Status_Code' => '400',
                        'Success'     => 'False',
                        'Response'    => [],
                        'Message'     => "Credenciales no encontradas",
                    ], 400
                );
            } 
        }
    }

    //Update password
    public function updatePassword(Request $request, $id)
    {
        $data = User::where('id',$id)->first();
        $validator = "";

        if($data != null){
            if($data->activo == 1){
                if(!$request->input('password')){
                    $validator = "Password actual requerido";
                }
    
                if(!$request->input('new_password')){
                    $validator = ($validator == "") ? $validator . "Nuevo password requerido" : $validator . " - Nuevo password requerido";
                }

                if(Hash::check($request->password, $data->password))
                {
                    $validator = ($validator == "") ? $validator . "No coincide el password actual" : $validator . " - No coincide el password actual";
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
                    $data->password = Hash::make($request->new_password);
        
                    if($data->save()){
                        return response()->json(
                            [
                                'Status_Code' => '201',
                                'Success'     => 'True',
                                'Response'    => ['Usuario' => $data],
                                'Message'     => "Password actualizado exitosamente",
                            ], 201
                        ); 
                    }else{
                        return response()->json(
                            [
                                'Status_Code' => '400',
                                'Success'     => 'False',
                                'Response'    => [],
                                'Message'     => "Error al actualizar el usuario",
                            ], 400
                        ); 
                    } 
                }
            }else{
                return response()->json(
                    [
                        'Status_Code' => '400',
                        'Success'     => 'False',
                        'Response'    => ['Usuario' => $data],
                        'Message'     => "El usuario se encuentra inactivo",
                    ], 400
                );
            }              
        }else{
            return response()->json(
                [
                    'Status_Code' => '404',
                    'Success'     => 'False',
                    'Response'    => [],
                    'Message'     => "Usuario no encontrado",
                ], 404
            ); 
        } 
    }
}