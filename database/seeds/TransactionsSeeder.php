<?php

use Illuminate\Database\Seeder;

class TransactionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\DB::table('coins')->insert([
            [
                'name' => 'BTC',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'USDT',
                'created_at' => date('Y-m-d H:i:s'),
            ]
        ]);
    }
}
