<?php

namespace App\Exchanges;

use App\Exchanges\Responses\Ticker;
use Illuminate\Support\Facades\Cache;

class Converter
{
    public function btcToUsdt(string $amount): string
    {
        /**
         * @var $ticker Ticker
         */
        $ticker = unserialize(Cache::get('ticker'));

        return bcmul($amount, $ticker->getBuyPrice(), 8);
    }
}
