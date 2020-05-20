<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Wallet
 * @property string amount
 * @property int coin_id
 * @property int id
 * @property int user_id
 * @property string uuid
 * @package App
 */
class Wallet extends Model
{
    const MAXIMUM_WALLETS_COUNT = 10;
    const GIFT_BTC_AMOUNT = '1.00000000';
    const TAX_RATE = 0.015;
    const TAX_SELF_WALLETS = false;

    public function transactions()
    {
        return $this->hasMany('App\Transaction');
    }
}
