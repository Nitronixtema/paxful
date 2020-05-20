<?php

namespace App\Http\Controllers;

use App\Coin;
use App\Http\Requests\CreateTransaction as RequestCreateTransaction;
use App\Transaction;
use App\Uuid;
use App\Wallet;
use InvalidArgumentException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class Transactions extends Controller
{
    public function get(Request $request)
    {
        $user = $request->user();

        $result = [];

        foreach (Transaction::getInByUser($user->id) as $transaction) {
            $result['in'][] = $this->retrieveFromTransaction($transaction);
        }

        foreach (Transaction::getOutByUser($user->id) as $transaction) {
            $result['out'][] = $this->retrieveFromTransaction($transaction);
        }

        return response()->json($result);
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

    public function make(RequestCreateTransaction $request, Uuid $uuid)
    {
        $validated = $request->validated();
        $user = $request->user();

        try {
            $transaction = new Transaction();

            DB::transaction(function () use ($transaction, $user, $uuid, $validated) {
                if (!$fromWallet = $user->wallets()->where('uuid', $validated['from'])->lockForUpdate()->first()) {
                    throw new InvalidArgumentException('form|The wallet not found');
                }

                if (bccomp($fromWallet->amount, $validated['amount'], 8) === -1) {
                    throw new InvalidArgumentException('from|Not enough coins');
                }

                if (!$toWallet = Wallet::where('uuid', $validated['to'])->first()) {
                    throw new InvalidArgumentException('to|The wallet not found');
                }

                $tax = 0;
                if ($fromWallet->user_id !== $toWallet->user_id
                    || Wallet::TAX_SELF_WALLETS
                ) {
                    $tax = bcmul($validated['amount'], Wallet::TAX_RATE, 8);
                }

                $fromWallet->amount = bcsub($fromWallet->amount, $validated['amount'], 8);
                $fromWallet->save();

                $afterTaxedAmount = bcsub($validated['amount'], $tax, 8);

                DB::table('wallets')
                    ->where('id', $toWallet->id)
                    ->increment('amount', $afterTaxedAmount);

                $transaction->coin_id = Coin::where('name', 'BTC')->first()->id;
                $transaction->uuid = $uuid->generate();
                $transaction->amount = $afterTaxedAmount;
                $transaction->tax = $tax;
                $transaction->from_wallet_id = $fromWallet->id;
                $transaction->to_wallet_id = $toWallet->id;
                $transaction->save();
            });

            return response()->json([
                'transaction' => $transaction->uuid,
                'tax' => $transaction->tax
            ]);
        } catch (\InvalidArgumentException $e) {
            $msg = explode('|', $e->getMessage());
            return response()->json([
                'errors' => [$msg[0] => $msg[1]],
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return response()->json(
                ['errors' => ['Impossible to proceed. ' . $e->getMessage()]],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
