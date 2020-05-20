<?php

use Illuminate\Support\Facades\Route;

Route::post('users', 'Users@create');

Route::middleware(['auth:api'])->group(function () {
    Route::prefix('wallets')->group(function() {
        Route::get('{address}', 'Wallets@get');
        Route::get('{address}/transactions', 'Wallets@getTransactions');
        Route::post('/', 'Wallets@create');
    });

    Route::prefix('transactions')->group(function() {
        Route::post('/', 'Transactions@make');
        Route::get('/', 'Transactions@get');
    });
});
