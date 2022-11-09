<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Services\StripeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;

use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        User::factory()->create();
    }

    public function testGuestCannotAddPaymentMethod()
    {
        $this->post('make-payment')
            ->assertStatus(302)
            ->assertRedirect('/login');
    }

    public function testAUserCanCreateStripeCustomer()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $this->addCustomer($user);

        $this->assertNotNull($user->fresh()->customer_id);
        $this->assertNotNull($user->fresh()->source);
    }

    public function testAUserCannotMakeAChargeWithoutAddingCard()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $attributes = [
            'amount' => 5000.00,
            'description' => $this->faker->paragraph,
            'currency' => 'usd'
        ];

        $this->post('make-payment', $attributes)
            ->assertStatus(302)
            ->assertSessionHas('error', 'Card details required! Please a add card first');
    }

    public function testAUserCanMakeACharge()
    {
        $user = User::find(1);
        $this->actingAs($user);

        //add a customer
        $this->addCustomer($user);

        //add a charge
        $this->addCharge($user);
    }

    public function testAUserCanRefundACharge()
    {
        $user = User::find(1);
        $this->actingAs($user);

        //setup stripe customer data
        $this->addCustomer($user);

        // add a charge
       $this->addCharge($user);

        $transaction = Transaction::find(1);

        //mock refund
        $this->mock(StripeService::class, function (Mockery\MockInterface $mock) use ($transaction) {
            $mock->shouldReceive('refund')
                ->with($transaction->charge_id)
                ->andReturn('succeeded');
        });

        //refund the charge
        $this->post("refund/{$transaction->id}/charge")
            ->assertStatus(302)
            ->assertRedirect('transactions')
            ->assertSessionHas('success', 'Refund was successful');

        $attributes['user_id'] = $user->id;
        $attributes['type'] = 'charge';
        $attributes['status'] = 'refunded';
        //assert previous charge has been refunded
        $this->assertDatabaseHas((new Transaction())->getTable(), $attributes);

        //assert a transaction was made for the refund
        $newTransaction = $transaction->only(['charge_id', 'amount', 'user_id', 'currency']);
        $newTransaction['type'] = 'refund';
        $newTransaction['status'] = 'success';
        $this->assertDatabaseHas((new Transaction())->getTable(), $newTransaction);
        $this->assertDatabaseCount((new Transaction())->getTable(), 2);
    }

    public function addCharge(User $user)
    {
        $attributes = [
            'amount' => 5000.00,
            'description' => $this->faker->paragraph,
            'currency' => 'usd'
        ];

        $this->mock(StripeService::class, function (Mockery\MockInterface $mock) use ($attributes) {
            $mock->shouldReceive('charge')
                ->with($attributes)
                ->andReturn($this->faker->isbn10());
        });

        $this->post('make-payment', $attributes)
            ->assertStatus(302)
            ->assertRedirect('transactions')
            ->assertSessionHas('success', 'Charge was successful');

        $attributes['user_id'] = $user->id;
        $attributes['type'] = 'charge';
        $attributes['status'] = 'success';
        $this->assertDatabaseHas((new Transaction())->getTable(), $attributes);
    }

    public function addCustomer(User $user)
    {
        $attribute = [
            'number' => '4242424242424242',
            'exp_month' => 11,
            'exp_year' => 2023,
            'cvc' => '314'
        ];

        $cardDetails = ['card' => $attribute];

        $this->mock(StripeService::class, function (Mockery\MockInterface $mock) use ($user, $cardDetails) {
            $mock->shouldReceive('createCustomer')
                ->with($user, $cardDetails)
                ->andReturn([
                    'customer_id' => $this->faker->isbn10(),
                    'source' => $this->faker->isbn10()
                ]);
        });

        $this->post('add/card-details', $attribute)
            ->assertStatus(302)
            ->assertRedirect('transactions')
            ->assertSessionHas('success', 'card details added successfully');
    }
}
