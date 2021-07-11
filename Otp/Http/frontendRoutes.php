<?php

use Illuminate\Routing\Router;

/** @var Router $router */
$router->group(['prefix' => '/otp', 'middleware' => ['web', 'auth']], function (Router $router) {

    $router->get('verify', [
        'as' => 'otp.view',
        'uses' => 'Client\OtpController@view',
    ]);
    $router->post('check', [
        'as' => 'otp.verify',
        'uses' => 'Client\OtpController@check',
    ]);
});
