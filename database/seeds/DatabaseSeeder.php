<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(\CoinsSeeder::class);
        $this->call(\UserSeeder::class);
        //$this->call(WalletsSeeder::class);
        //$this->call(TransactionsSeeder::class);
    }
}
