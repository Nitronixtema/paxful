<?php

namespace App\Console\Commands;

use App\Exchanges\Binance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use WSSC\WebSocketClient;
use WSSC\Components\ClientConfig;

class DownloadBinanceData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download:binance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'DownloadCurrent prices from Binance exchange';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        set_time_limit(0);

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $exchange = new Binance;

        $client = new WebSocketClient($exchange->getBtcUsdtStreamTicker(), new ClientConfig());

        $exchange->setSocketClient($client);

        while ($msg = $client->receive()) {
            $rawData = json_decode($msg);
            if (is_object($rawData)) {
                if ($ticker = $exchange->processTicker($rawData)) {
                    Cache::put('ticker', serialize($ticker));
                }
            }
        }
    }
}
