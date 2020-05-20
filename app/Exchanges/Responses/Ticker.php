<?php


namespace App\Exchanges\Responses;


class Ticker
{
    protected $buyPrice;
    protected $sellPrice;
    protected $time;

    public function __construct(string $buyPrice, string $sellPrice)
    {
        $this->buyPrice = $buyPrice;
        $this->sellPrice = $sellPrice;
        $this->time = time();
    }

    public function getBuyPrice(): string
    {
        return $this->buyPrice;
    }

    public function getSellPrice(): string
    {
        return $this->sellPrice;
    }

    public function isOld(int $seconds = 30): bool
    {
        return time() < $this->time + $seconds;
    }
}
