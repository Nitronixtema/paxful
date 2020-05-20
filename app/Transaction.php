<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class Transaction
 * @property string amount
 * @property int coin_id
 * @property int from_wallet_id
 * @property int id
 * @property string to_wallet_id
 * @property string tax
 * @property string uuid
 * @package App
 */
class Transaction extends Model
{
    public $timestamps = true;

    public function setUpdatedAt($value) {}

    public static function getOutByUser(int $userId, ?int $walletId = null): Collection
    {
        return DB::table('transactions')
            ->select([
                'transactions.amount',
                'transactions.uuid as tuuid',
                'transactions.created_at',
                'wallets.uuid as wuuid',
            ])
            ->join('wallets', function ($join) use ($walletId) {
                $x = $join->on('transactions.from_wallet_id', '=', 'wallets.id');

                if ($walletId) {
                    $x->where('wallets.id', $walletId);
                }
            })
            ->join('users', function ($join) use ($userId) {
                $join->on('wallets.user_id', '=', 'users.id')
                    ->where('users.id', $userId);
            })
            ->get();
    }

    public static function getInByUser(int $userId, ?int $walletId = null): Collection
    {
        return DB::table('transactions')
            ->select([
                'transactions.amount',
                'transactions.uuid as tuuid',
                'transactions.created_at',
                'wallets.uuid as wuuid',
            ])
            ->join('wallets', function ($join) use ($walletId) {
                $x = $join->on('transactions.to_wallet_id', '=', 'wallets.id');

                if ($walletId) {
                    $x->where('wallets.id', $walletId);
                }
            })
            ->join('users', function ($join) use ($userId) {
                $join->on('wallets.user_id', '=', 'users.id')
                    ->where('users.id', $userId);
            })
            ->get();
    }
}
