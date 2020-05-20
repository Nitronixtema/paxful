<?php

namespace Tests\Feature;

class Converter extends \App\Exchanges\Converter
{
    private $rate;

    public function __construct()
    {
        $this->rate = mt_rand(5000, 12000);
    }

    public function btcToUsdt(string $amount): string
    {
        return bcmul($amount, $this->rate, 8);
    }
}
