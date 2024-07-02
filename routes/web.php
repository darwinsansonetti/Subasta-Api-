<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// $router->get('/', function () use ($router) {
//     return $router->app->version();
// });

//--------------------------------------Rutas sin Autenticacion------------------------------//

    //--------------------------------------Roles------------------------------//

    //Obtener todos los Roles -> http://localhost/apitusubusta/public/roles
    $router->get('/roles/show/{status}', 'RolController@index');

    //--------------------------------------Roles------------------------------//

    //--------------------------------------Usuario------------------------------//

    //Guardar un nuevo Usuario -> http://localhost/apitusubusta/public/user
    $router->post('/user', 'UserController@store');

    //Resetear el password de un Usuario -> http://localhost/apitusubusta/public/user?reset
    $router->post('/user/reset', 'UserController@reset');

    //Activar un Usuario -> http://localhost/apitusubusta/public/user/active/{id}
    $router->put('/user/active/{id}', 'UserController@active');

    //Login -> http://localhost/apitusubusta/public/login
    $router->post('/login', 'UserController@login');

    //--------------------------------------Usuario------------------------------//

//--------------------------------------Fin Rutas sin Autenticacion------------------------------//

//--------------------------------------Rutas que utilizan Token------------------------------//
$router->group(['middleware' => 'auth'], function () use ($router) {
    
//--------------------------------------Roles------------------------------//

    //Guardar Rol -> http://localhost/apitusubusta/public/roles
    $router->post('/roles', 'RolController@store');

    //Obtener un Rol -> http://localhost/apitusubusta/public/roles/{id}
    $router->get('/roles/{id}', 'RolController@show');

    //Borrar un Rol -> http://localhost/apitusubusta/public/roles/{id}
    $router->delete('/roles/{id}', 'RolController@delete');

    //Actualizar un Rol -> http://localhost/apitusubusta/public/roles/{id}
    $router->put('/roles/{id}', 'RolController@update');
//--------------------------------------Roles------------------------------//

//--------------------------------------Usuario------------------------------//

    //Obtener todos los Usuarios -> http://localhost/apitusubusta/public/user
    $router->get('/user/show/{status}', 'UserController@index');

    //Obtener un Usuario -> http://localhost/apitusubusta/public/user/{id}
    $router->get('/user/{id}', 'UserController@show');

    //Borrar o Inactivar un Usuario -> http://localhost/apitusubusta/public/user/{id}
    $router->delete('/user/{id}', 'UserController@delete');

    //Actualizar un Usuario -> http://localhost/apitusubusta/public/user/{id}
    $router->put('/user/{id}', 'UserController@update');

    //Actualizar el Saldo del Usuario -> http://localhost/apitusubusta/public/user/saldo/{id}
    $router->put('/user/saldo/{id}', 'UserController@saldo');

    //Cambiar Password -> http://localhost/apitusubusta/public/user/pass/{id}
    $router->put('/user/pass/{id}', 'UserController@updatePassword');

    //Para obtener el usuario que esta logueado
    $router->get('user', function () use ($router) {
        return auth()->user();
    });

    //Logout -> http://localhost/apitusubusta/public/logout
    $router->post('/logout', 'UserController@logout');

//--------------------------------------Usuario------------------------------//

//--------------------------------------Tipos de Transacciones------------------------------//

    //Obtener todos los Tipos de Transacciones -> http://localhost/apitusubusta/public/tipos_transacciones
    $router->get('/tipos_transacciones/show/{status}', 'TipoTransaccionController@index');

    //Guardar un nuevo Tipo de Transaccion -> http://localhost/apitusubusta/public/tipos_transacciones
    $router->post('/tipos_transacciones', 'TipoTransaccionController@store');

    //Obtener un Tipo de Transaccion -> http://localhost/apitusubusta/public/tipos_transacciones/{id}
    $router->get('/tipos_transacciones/{id}', 'TipoTransaccionController@show');

    //Borrar un Tipo de Transaccion -> http://localhost/apitusubusta/public/tipos_transacciones/{id}
    $router->delete('/tipos_transacciones/{id}', 'TipoTransaccionController@delete');

    //Actualizar un Tipo de Transaccion -> http://localhost/apitusubusta/public/tipos_transacciones/{id}
    $router->put('/tipos_transacciones/{id}', 'TipoTransaccionController@update');

//--------------------------------------Tipos de Transacciones------------------------------//

//--------------------------------------Tipos de Apuesta------------------------------//

    //Obtener todos los Tipos de Apuestas -> http://localhost/apitusubusta/public/tipos_apuestas
    $router->get('/tipos_apuestas/show/{status}', 'TipoApuestaController@index');

    //Guardar un nuevo Tipo de Apuestas -> http://localhost/apitusubusta/public/tipos_apuestas
    $router->post('/tipos_apuestas', 'TipoApuestaController@store');

    //Obtener un Tipo de Apuestas -> http://localhost/apitusubusta/public/tipos_apuestas/{id}
    $router->get('/tipos_apuestas/{id}', 'TipoApuestaController@show');

    //Borrar un Tipo de Apuestas -> http://localhost/apitusubusta/public/tipos_apuestas/{id}
    $router->delete('/tipos_apuestas/{id}', 'TipoApuestaController@delete');

    //Actualizar un Tipo de Apuestas -> http://localhost/apitusubusta/public/tipos_apuestas/{id}
    $router->put('/tipos_apuestas/{id}', 'TipoApuestaController@update');

//--------------------------------------Tipos de Apuesta------------------------------//

//--------------------------------------Hipodromo------------------------------//

    //Obtener todos los hipodromos -> http://localhost/apitusubusta/public/hipodromo/show/{status}
    $router->get('/hipodromo/show/{status}', 'HipodromoController@index');

    //Guardar un nuevo Hipodromo -> http://localhost/apitusubusta/public/hipodromo
    $router->post('/hipodromo', 'HipodromoController@store');

    //Obtener un Hipodromo -> http://localhost/apitusubusta/public/hipodromo/{id}
    $router->get('/hipodromo/{id}', 'HipodromoController@show');

    //Borrar un Hipodromo -> http://localhost/apitusubusta/public/hipodromo/{id}
    $router->delete('/hipodromo/{id}', 'HipodromoController@delete');

    //Actualizar un Hipodromo -> http://localhost/apitusubusta/public/hipodromo/{id}
    $router->put('/hipodromo/{id}', 'HipodromoController@update');

    //Activar un Hipodromo -> http://localhost/apitusubusta/public/hipodromo/active/{id}
    $router->put('/hipodromo/active/{id}', 'HipodromoController@active');

    //Caballos retirados por hipodromo -> http://localhost/apitusubusta/public/hipodromo/retirados/{id}
    $router->get('/hipodromo/retirados/{id}', 'HipodromoController@retirados');

    //Obtener todas las carreras de un hipodromo -> http://localhost/apitusubusta/public/hipodromo/carreras/show/{id}
    $router->get('/hipodromo/carreras/show/{id}', 'HipodromoController@show_carreras');

    //Obtener todas las Carreras de un hipodromo por fecha -> http://localhost/apitusubusta/public/hipodromo/carreras/data
    $router->post('/hipodromo/carreras/data/{id}', 'HipodromoController@show_data');

//--------------------------------------Hipodromo------------------------------//

//--------------------------------------Jugadas por Hipodromo------------------------------//

    //Obtener todos los tipos de jugadas de un hipodromo -> http://localhost/apitusubusta/public/jugadas/hipodromo/show/{id}
    $router->post('/jugada/hipodromo/show', 'JugadasController@index');

    //Guardar una nueva Jugada para un Hipodromo -> http://localhost/apitusubusta/public/jugadas
    $router->post('/jugada', 'JugadasController@store');

    //Borrar un Tipo de jugada para un hipodromo -> http://localhost/apitusubusta/public/jugadas/{id}
    $router->delete('/jugadas/{id}', 'JugadasController@delete');

    //Activar un tipo de jugada para un Hipodromo -> http://localhost/apitusubusta/public/jugadas/active/{id}
    $router->put('/jugadas/active/{id}', 'JugadasController@active');

//--------------------------------------Jugadas por Hipodromo------------------------------//

//--------------------------------------Carreras------------------------------//

    //Guardar una nueva Carrera -> http://localhost/apitusubusta/public/carreras
    $router->post('/carreras', 'CarreraController@store');

    //Obtener una carrera -> http://localhost/apitusubusta/public/carreras/{id}
    $router->get('/carreras/{id}', 'CarreraController@show');

    //Borrar una carrera -> http://localhost/apitusubusta/public/carreras/{id}
    $router->delete('/carreras/{id}', 'CarreraController@delete');

    //Actualizar una Carrera -> http://localhost/apitusubusta/public/carreras/{id}
    $router->put('/carreras/{id}', 'CarreraController@update');

    //Activar una Carrera -> http://localhost/apitusubusta/public/carreras/active/{id}
    $router->put('/carreras/active/{id}', 'CarreraController@active');

    //Obtener todos los caballos de una carrera segun la llegada -> http://localhost/apitusubusta/public/carreras/finish_result/{id}
    $router->get('/carreras/finish_result/{id}', 'CarreraController@finish_result');

    //Obtener todos los caballos de una carrera -> http://localhost/apitusubusta/public/carreras/caballo/{id}
    $router->get('/carreras/caballo/{id}', 'CarreraController@index_show');

    //Obtener todos los caballos de una carrera subastada -> http://localhost/apitusubusta/public/carreras/caballo/subasta/{id}
    $router->get('/carreras/caballo/subasta/{id}', 'CarreraController@show_subasta');

//--------------------------------------Carreras------------------------------//

//--------------------------------------Caballos------------------------------//

    //Guardar un caballo de una Carrera -> http://localhost/apitusubusta/public/caballo
    $router->post('/caballo', 'CaballoController@store');

    //Obtener un caballo especifico -> http://localhost/apitusubusta/public/caballo/{id}
    $router->get('/caballo/show/{id}', 'CaballoController@show');

    //Borrar un Caballo -> http://localhost/apitusubusta/public/caballo/{id}
    $router->delete('/caballo/{id}', 'CaballoController@delete');

    //Actualizar un Caballo -> http://localhost/apitusubusta/public/caballo/{id}
    $router->put('/caballo/{id}', 'CaballoController@update');

//--------------------------------------Caballos------------------------------//

//--------------------------------------Subasta------------------------------//

    //Guardar un caballo subastado -> http://localhost/apitusubusta/public/subasta
    $router->post('/subasta', 'SubastaController@store');

    // //Obtener un caballo especifico -> http://localhost/apitusubusta/public/caballo/{id}
    // $router->get('/caballo/show/{id}', 'CaballoController@show');

    // //Borrar un Caballo -> http://localhost/apitusubusta/public/caballo/{id}
    // $router->delete('/caballo/{id}', 'CaballoController@delete');

    // //Actualizar un Caballo -> http://localhost/apitusubusta/public/caballo/{id}
    // $router->put('/caballo/{id}', 'CaballoController@update');

//--------------------------------------Subasta------------------------------//

});
//--------------------------------------Fin Rutas que utilizan Token------------------------------//

