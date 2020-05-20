<?php

namespace Tests\Feature;

use App\Uuid;
use App\Wallet;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Tests\CreatesApplication;
use Tests\TestCase;

class TransactionsTest extends TestCase
{
    use CreatesApplication, DatabaseMigrations;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = factory(\App\User::class)->create();
    }

    public function testGet()
    {
        $transactionsCount = 3;

        $this->seed();

        $wallets = factory(\App\Wallet::class, 2)->create([
            'user_id' => $this->user->id,
        ]);

        $transactions = factory(\App\Transaction::class, $transactionsCount)->create([
            'from_wallet_id' => $wallets[0]->id,
            'to_wallet_id' => $wallets[1]->id,
            'amount' => 0.01,
        ]);

        $response = $this->actingAs($this->user)->getJson('/transactions?token=' . $this->user->token);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonCount($transactionsCount, 'in')
            ->assertJsonCount($transactionsCount, 'out');
    }

    public function testMakeWalletIsWrong()
    {
        $this->seed();

        $wallets = factory(\App\Wallet::class, 2)->create([
            'user_id' => $this->user->id,
        ]);

        # Case 1
        $response = $this->actingAs($this->user)->postJson('/transactions', [
            'token' => $this->user->token,
            'amount' => 0.1,
            'from' => (string)Str::uuid(),
            'to' => $wallets[0]->uuid,
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);

        # Case 2
        $response = $this->actingAs($this->user)->postJson('/transactions', [
            'token' => $this->user->token,
            'amount' => 0.1,
            'from' => $wallets[0]->uuid,
            'to' => $wallets[0]->uuid,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonPath('errors.to.0', 'The to and from must be different.');

        # Case 3
        $response = $this->actingAs($this->user)->postJson('/transactions', [
            'token' => $this->user->token,
            'amount' => 0.1,
            'from' => $wallets[0]->uuid,
            'to' => (string)Str::uuid(),
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    public function testMakeTooBigAmount()
    {
        $this->seed();

        $wallets = factory(\App\Wallet::class, 2)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->postJson('/transactions', [
            'token' => $this->user->token,
            'amount' => 11,
            'from' => $wallets[0]->uuid,
            'to' => $wallets[1]->uuid,
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJsonPath('errors.from', 'Not enough coins');
    }

    public function testMake()
    {
        $this->seed();

        $uuid = (string)Str::uuid();

        $this->partialMock(Uuid::class, function ($mock) use ($uuid) {
            $mock->shouldReceive('generate')->once()->andReturn($uuid);
        });

        $wallets = factory(\App\Wallet::class, 2)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->postJson('/transactions', [
            'token' => $this->user->token,
            'amount' => 0.01,
            'from' => $wallets[0]->uuid,
            'to' => $wallets[1]->uuid,
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'tax' => 0,
                'transaction' => $uuid,
            ]);
    }

    public function testMakeToAnotherUser()
    {
        $this->seed();

        $uuid = (string)Str::uuid();

        $this->partialMock(Uuid::class, function ($mock) use ($uuid) {
            $mock->shouldReceive('generate')->once()->andReturn($uuid);
        });

        $walletFirst = factory(\App\Wallet::class)->create([
            'user_id' => $this->user->id,
        ]);

        $secondUser = factory(\App\User::class)->create();
        $walletSecond = factory(\App\Wallet::class)->create([
            'user_id' => $secondUser->id,
        ]);

        $response = $this->actingAs($this->user)->postJson('/transactions', [
            'token' => $this->user->token,
            'amount' => 0.02,
            'from' => $walletFirst->uuid,
            'to' => $walletSecond->uuid,
        ]);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'tax' => bcmul(0.02, Wallet::TAX_RATE, 8),
                'transaction' => $uuid,
            ]);
    }
}
