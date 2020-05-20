<?php

use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //factory(App\User::class, 3)->create();

        factory(\App\User::class, 3)
            ->create()
            ->each(function ($user) {
                $factory = factory(\App\Wallet::class, 4)->make(['user_id' => $user->id]);
                $factory->each(function ($wallet) {
                    $wallet->save();
                });
            });
    }
}
