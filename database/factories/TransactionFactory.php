<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(\App\Transaction::class, function (Faker $faker) {
    return [
        'coin_id' => \App\Coin::where('name', 'BTC')->first()->id,
        'uuid' => (string)Str::uuid(),
        'amount' => mt_rand(100000, 1000000) / 100000,
        'tax' => 0,
    ];
});
