<?php

// Client View Groups
Route::group(['middleware' => ['web'], 'namespace' => '\Acelle\Paytr\Controllers'], function () {
    Route::get('plugins/acelle/paytr', 'DashboardController@index');

    // 
    Route::match(['get', 'post'], 'plugins/acelle/paytr/{invoice_uid}/checkout', 'PaytrController@checkout');
    Route::match(['get', 'post'], 'plugins/acelle/paytr/settings', 'PaytrController@settings');

    Route::get('plugins/acelle/paytr/pay/{invoice_uid}/success', 'PaytrController@success');
    Route::post('plugins/acelle/paytr/pay/{invoice_uid}/failed', 'PaytrController@failed');

    Route::match(['post', 'get'], 'paytr/notification', 'PaytrController@notification');
});
