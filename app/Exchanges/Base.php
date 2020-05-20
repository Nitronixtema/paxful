<?php

namespace App\Exchanges;

use App\Exchanges\Responses\Ticker;
use WSSC\WebSocketClient;

abstract class Base
{
    protected $webSocketClient;

    abstract public function getBtcUsdtStreamTicker(): string;

    public function getConverter(): Converter
    {
        return new Converter();
    }

    public function processTicker(object $rawTicker): Ticker
    {
        return new Ticker(
            $rawTicker->b, $rawTicker->a
        );
    }

    public function setSocketClient(WebSocketClient $webSocketClient): Base
    {
        $this->webSocketClient = $webSocketClient;

        return $this;
    }
}
