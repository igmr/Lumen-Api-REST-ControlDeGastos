<?php

use Illuminate\Http\Response;

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
function data()
{
	return [
		'name'      => 'Api REST Control de gastos',
		'framework' => 'Lumen/Laravel',
		'version'   => '0.0.1',
	];
}

$router->get('/'    , function () {return Response()->json(data());});
$router->get('/api' , function () {return Response()->json(data());});


$router->get('/api/classification'         , 'ClassificationController@index');
$router->get('/api/classification/{id}'    , 'ClassificationController@show');
$router->post('/api/classification'        , 'ClassificationController@store');
$router->put('/api/classification/{id}'    , 'ClassificationController@update');
$router->delete('/api/classification/{id}' , 'ClassificationController@destroy');

$router->get('/api/subclassification'         , 'SubclassificationController@index');
$router->get('/api/subclassification/{id}'    , 'SubclassificationController@show');
$router->post('/api/subclassification'        , 'SubclassificationController@store');
$router->put('/api/subclassification/{id}'    , 'SubclassificationController@update');
$router->delete('/api/subclassification/{id}' , 'SubclassificationController@destroy');

$router->get('/api/operation'          , 'OperationController@index');
$router->get('/api/operation/{id}'     , 'OperationController@show');
$router->post('/api/operation/income'  , 'OperationController@storeIncome');
$router->post('/api/operation/outcome' , 'OperationController@storeOutcome');
$router->put('/api/operation/{id}'     , 'OperationController@update');
$router->delete('/api/operation'       , 'OperationController@destroy');
