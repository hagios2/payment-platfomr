<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserPaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PaymentMethodTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        User::factory()->create();
    }

    public function testGuestCannotAddPaymentMethod()
    {
        $attributes = [
            'payment_method' => 'card',
            'is_default' => true
        ];

        $this->post('payment-method', $attributes)
            ->assertStatus(302)
            ->assertRedirect('/login');
    }

    public function testValidationErrorsWillBeThrownIfPaymentMethodNotPassed()
    {
        $user = User::find(1);

        $this->actingAs($user);

        $attributes = [
            'is_default' => true
        ];

        $this->post('payment-method', $attributes)
            ->assertStatus(302)
            ->assertSessionHasErrors('payment_method', 'The payment method field is required');
    }

    public function testAUserCanAddPaymentMethod()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $attributes = [
            'payment_method' => 'card',
            'is_default' => true
        ];

        $this->post('payment-method', $attributes)
            ->assertStatus(302)
            ->assertRedirect('/payment-method')->dumpSession()
            ->assertSessionHas('success', 'Payment Method saved successfully');

        $attributes['user_id'] = $user->id;
        $this->assertDatabaseHas((new UserPaymentMethod())->getTable(), $attributes);

//        mock()->shouldReceive('createCustomer')
//            ->andReturn([]);
//
//        $response = $this->post('add/card-details', [
//            'number' => '4242424242424242',
//            'exp_month' => 11,
//            'exp_year' => 2023,
//            'cvc' => '314'
//        ])
//        ->assertStatus(200);
//
//        $this->assertNotNull($user->fresh()->customer_id);
//        $this->assertNotNull($user->fresh()->source);
    }

    public function testAUserOverrideTheDefaultPaymentMethod()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $attributes = [
            'payment_method' => 'card',
            'is_default' => true
        ];

        $this->post('payment-method', $attributes)
            ->assertStatus(302)
            ->assertRedirect('/payment-method')
            ->assertSessionHas('success', 'Payment Method saved successfully');

        $attributes['user_id'] = $user->id;
        $this->assertDatabaseHas((new UserPaymentMethod())->getTable(), $attributes);

        $newAttributes = [
            'payment_method' => 'momo',
            'is_default' => true
        ];

        $this->post('payment-method', $newAttributes)
            ->assertStatus(302)
            ->assertRedirect('/payment-method')->dumpSession()
            ->assertSessionHas('success', 'Payment Method saved successfully');

        $newAttributes['user_id'] = $user->id;
        $this->assertDatabaseHas((new UserPaymentMethod())->getTable(), $newAttributes);

        $attributes['is_default'] = false;
        $this->assertDatabaseHas((new UserPaymentMethod())->getTable(), $attributes);
    }

    public function testAUserCannotAddExistingPaymentMethod()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $attributes = [
            'payment_method' => 'card',
            'is_default' => true
        ];

        //hit the endpoint to add a new payment method
        $this->post('payment-method', $attributes)
            ->assertStatus(302)
            ->assertRedirect('/payment-method')
            ->assertSessionHas('success', 'Payment Method saved successfully');

        $attributes['user_id'] = $user->id;
        $this->assertDatabaseHas((new UserPaymentMethod())->getTable(), $attributes);


        //add the same payment method again
        $this->post('payment-method', $attributes)
            ->assertStatus(302)->dumpSession()
            ->assertSessionHas('error', 'The selected payment method has already been added');
    }

    public function testUserCanViewAllPaymentMethods()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $attributes = [
            'payment_method' => 'card',
            'is_default' => true
        ];

        //hit the endpoint to add a some payment method data
        $this->post('payment-method', $attributes)
            ->assertStatus(302)
            ->assertRedirect('/payment-method')
            ->assertSessionHas('success', 'Payment Method saved successfully');

        $attributes['user_id'] = $user->id;
        $this->assertDatabaseHas((new UserPaymentMethod())->getTable(), $attributes);

        //hit the payment method index page
        $response = $this->get('/payment-method')
            ->assertOk()
            ->assertViewIs('payment_method.index');

        $paymentMethod = UserPaymentMethod::all();

        // assert that the payment method data is the same as the one from db
        $response->assertViewHas('paymentMethods', $paymentMethod);
    }

    public function testAUserCanUpdatePaymentMethod()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $attributes = [
            'payment_method' => 'card',
            'is_default' => true
        ];

        // hit the endpoint to add a new payment method
        $this->post('payment-method', $attributes)
            ->assertStatus(302)
            ->assertRedirect('/payment-method')
            ->assertSessionHas('success', 'Payment Method saved successfully');

        $attributes['user_id'] = $user->id;
        $this->assertDatabaseHas((new UserPaymentMethod())->getTable(), $attributes);

        $newAttributes = [
            'payment_method' => 'momo',
            'is_default' => true
        ];

        // hit the endpoint to update payment method
        $paymentMethod = UserPaymentMethod::find(1);
        $this->put("payment-method/{$paymentMethod->id}", $newAttributes)
            ->assertStatus(302)
            ->assertRedirect('/payment-method')->dumpSession()
            ->assertSessionHas('success', 'Payment method updated successfully');

        $newAttributes['user_id'] = $user->id;
        $newAttributes['id'] = $paymentMethod->id;

        //assert updated fields were stored in the db
        $this->assertDatabaseHas((new UserPaymentMethod())->getTable(), $newAttributes);

        //assert previous fields are no longer in the db
        $this->assertDatabaseMissing((new UserPaymentMethod())->getTable(), $attributes);
    }

    public function testAUserCanDeleteAPaymentMethod()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $attributes = [
            'payment_method' => 'card',
            'is_default' => true
        ];

        // hit the endpoint to add a new payment method
        $this->post('payment-method', $attributes)
            ->assertStatus(302)
            ->assertRedirect('/payment-method')
            ->assertSessionHas('success', 'Payment Method saved successfully');

        $attributes['user_id'] = $user->id;
        $this->assertDatabaseHas((new UserPaymentMethod())->getTable(), $attributes);

        // hit the endpoint to add a new payment method
        $paymentMethod = UserPaymentMethod::find(1);
        $this->delete("payment-method/{$paymentMethod->id}")
            ->assertStatus(302)
            ->assertRedirect('/payment-method')
            ->assertSessionHas('success', 'Payment Method Deleted Successfully');

        $this->assertDatabaseMissing((new UserPaymentMethod())->getTable(), $attributes);
    }

    public function testAUserCanFetchAPaymentMethod()
    {
        $user = User::find(1);
        $this->actingAs($user);

        $attributes = [
            'payment_method' => 'card',
            'is_default' => true
        ];

        // hit the endpoint to add a new payment method
        $this->post('payment-method', $attributes)
            ->assertStatus(302)
            ->assertRedirect('/payment-method')
            ->assertSessionHas('success', 'Payment Method saved successfully');

        $attributes['user_id'] = $user->id;
        $this->assertDatabaseHas((new UserPaymentMethod())->getTable(), $attributes);

        $paymentMethod = UserPaymentMethod::find(1);
        $this->get("payment-method/{$paymentMethod->id}")
            ->assertOk()
            ->assertJson([
                'payment_method' => $attributes
            ]);

    }
}
