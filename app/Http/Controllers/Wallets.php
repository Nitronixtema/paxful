<?php

namespace App\Http\Controllers;

use App\Coin;
use App\Exchanges\Base as Exchange;
use App\Transaction;
use App\Uuid;
use App\Wallet;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Wallets extends Controller
{
    public function create(Exchange $exchange, Request $request, Uuid $uuid)
    {
        $user = $request->user();

        if ($user->wallets->count() > Wallet::MAXIMUM_WALLETS_COUNT) {
            return response()->json([
                'errors' => ['Too many wallets'],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $wallet = new Wallet();
        $wallet->amount = Wallet::GIFT_BTC_AMOUNT;
        $wallet->coin_id = Coin::where('name', 'BTC')->first()->id;
        $wallet->user_id = $user->id;
        $wallet->uuid = $uuid->generate();
        $wallet->save();

        return response()->json([
            'token' => $wallet->uuid,
            'btc' => $wallet->amount,
            'usd' => $exchange->getConverter()->btcToUsdt($wallet->amount),
        ]);
    }

    public function get(string $address, Exchange $exchange, Request $request)
    {
        $user = $request->user();

        if (!$wallet = $user->wallets()->where('uuid', $address)->first()) {
            return response()->json([
                'errors' => ['address' => 'Wrong address'],
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'token' => $wallet->uuid,
            'btc' => $wallet->amount,
            'usd' => $exchange->getConverter()->btcToUsdt($wallet->amount),
        ]);
    }

    public function getTransactions(string $address, Request $request)
    {
        $user = $request->user();

        $wallet = $user->wallets()->where('uuid', $address)->first();

        if (!$wallet) {
            return response()->json([
                'errors' => ['address' => 'The wallet not found'],
            ], Response::HTTP_BAD_REQUEST);
        }

        $result = [];

        foreach (Transaction::getInByUser($user->id, $wallet->id) as $transaction) {
            $result['in'][] = $this->retrieveFromTransaction($transaction);
        }

        foreach (Transaction::getOutByUser($user->id, $wallet->id) as $transaction) {
            $result['out'][] = $this->retrieveFromTransaction($transaction);
        }

        return $result;
    }

    private function retrieveFromTransaction(object $transaction): array
    {
        return [
            'amount' => $transaction->amount,
            'transaction' => $transaction->tuuid,
            'wallet' => $transaction->wuuid,
            'date' => $transaction->created_at,
        ];
    }
}
