<?php

namespace App\Exchanges;

use App\Exchanges\Responses\Ticker;

class Binance extends Base
{
    public function getBtcUsdtStreamTicker(): string
    {
        return 'wss://stream.binance.com:9443/ws/btcusdt@ticker';
    }

    public function getConverter(): Converter
    {
        return new \App\Exchanges\Binance\Converter();
    }
}
