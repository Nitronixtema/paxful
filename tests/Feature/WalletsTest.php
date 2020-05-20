<?php

namespace Tests\Feature;

use App\Exchanges\Base as Exchange;
use App\Uuid;
use App\Wallet;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

class WalletsTest extends TestCase
{
    use CreatesApplication, DatabaseMigrations;

    /**
     *
     * @dataProvider walletsProvider
     *
     * @param string $uuid
     */
    public function testCreate(string $uuid, bool $expect)
    {
        $this->seed(\CoinsSeeder::class);

        $converter = new Converter();

        if ($expect) {
            $user = factory(\App\User::class)->create();

            $this->partialMock(Uuid::class, function ($mock) use ($uuid) {
                $mock->shouldReceive('generate')->once()->andReturn($uuid);
            });

            $this->partialMock(Exchange::class, function ($mock) use ($converter) {
                $mock->shouldReceive('getConverter')->once()->andReturn($converter);
            });


            $response = $this->actingAs($user)->postJson('/wallets/', ['token' => $user->token]);

            $response->assertStatus(200)
                ->assertJson([
                    'btc' => Wallet::GIFT_BTC_AMOUNT,
                    'token' => $uuid,
                    'usd' => $converter->btcToUsdt(Wallet::GIFT_BTC_AMOUNT),
                ]);
        } else {
            $response = $this->postJson('/wallets/');

            $response->assertStatus(403)
                ->assertJsonMissing([
                    'btc' => Wallet::GIFT_BTC_AMOUNT,
                    'token' => $uuid,
                    'usd' => $converter->btcToUsdt(Wallet::GIFT_BTC_AMOUNT),
                ])
                ->assertJsonPath('errors.auth', 'Unauthenticated');
        }
    }

    /**
     * @dataProvider walletsProvider
     */
    public function testGet(string $uuid, bool $expect)
    {
        $this->seed(\CoinsSeeder::class);

        $user = factory(\App\User::class)->create();

        $this->be($user);

        $converter = new Converter();

        $this->partialMock(Uuid::class, function ($mock) use ($uuid) {
            $mock->shouldReceive('generate')->andReturn($uuid);
        });

        $this->partialMock(Exchange::class, function ($mock) use ($converter) {
            $mock->shouldReceive('getConverter')->andReturn($converter);
        });

        if ($expect) {
            $this->postJson('/wallets/', ['token' => $user->token]);
        }

        $response = $this->getJson("/wallets/$uuid?token=$user->token", ['token' => $user->token]);

        if ($expect) {
            $response->assertStatus(200)
                ->assertJson([
                    'btc' => Wallet::GIFT_BTC_AMOUNT,
                    'token' => $uuid,
                    'usd' => $converter->btcToUsdt(Wallet::GIFT_BTC_AMOUNT),
                ]);
        } else {
            $response->assertStatus(404)
                ->assertJsonMissing([
                    'btc' => Wallet::GIFT_BTC_AMOUNT,
                    'token' => $uuid,
                    'usd' => $converter->btcToUsdt(Wallet::GIFT_BTC_AMOUNT),
                ]);
        }
    }

    public function testGetTransactionsWrongUuid()
    {
        $this->seed();

        $user = factory(\App\User::class)->create();

        $response = $this->actingAs($user)->getJson("/wallets/wrong-uuid/transactions?token=$user->token", ['token' => $user->token]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonPath('errors.address', 'The wallet not found');
    }

    public function testGetTransactionsCorrectUuid()
    {
        $transactionsCount = 3;

        $this->seed();

        $user = factory(\App\User::class)->create();

        $wallets = factory(\App\Wallet::class, 2)->create([
            'user_id' => $user->id,
        ]);

        $transactions = factory(\App\Transaction::class, $transactionsCount)->create([
            'from_wallet_id' => $wallets[0]->id,
            'to_wallet_id' => $wallets[1]->id,
            'amount' => 0.01,
        ]);

        $response = $this->actingAs($user)->getJson(
            "/wallets/"
            . $wallets[0]->uuid
            . "/transactions?token=$user->token"
        );

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount($transactionsCount, 'out');
    }

    public function walletsProvider()
    {
        return [
            ['uuid-1', true],
            [(string)Str::uuid(), true],
            ['uuid-3', false],
        ];
    }
}
