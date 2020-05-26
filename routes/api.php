<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});


$api = app('Dingo\Api\Routing\Router');
$api->version('v1', ['namespace' => 'App\Http\Controllers\V1'], function ($api) {
    $api->post('register', 'AuthController@register');
    $api->post('login', 'AuthController@login');
    $api->post('logout', 'AuthController@logout');
    $api->post('refresh', 'AuthController@refresh');
    $api->post('me', 'AuthController@me');
    $api->get('test', 'AuthController@test');

    $api->post('index', 'IndexConteoller@index');
    $api->post('search', 'IndexConteoller@search');

    $api->post('category', 'ProductsController@category');
    $api->post('detail', 'ProductsController@detail');
    $api->post('myfavor', 'ProductsController@myfavor');
    $api->post('favor', 'ProductsController@favor');
    $api->post('disfavor', 'ProductsController@disfavor');

    $api->post('addresses', 'UserAddressesController@addresses');
    $api->post('upresses', 'UserAddressesController@upresses');
    $api->post('addressdet', 'UserAddressesController@addressdet');
    $api->post('getaddress', 'UserAddressesController@getaddress');
    $api->post('updefault', 'UserAddressesController@updefault');
    $api->post('deladdress', 'UserAddressesController@deladdress');
});