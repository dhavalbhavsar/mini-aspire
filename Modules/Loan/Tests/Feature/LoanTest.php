<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class LoanTest extends TestCase
{

    public function login($role = 'admin', $userId = null) {

        $user = empty($userId) ? User::factory()->create() : User::find($userId);
        $user->assignRole($role);

        Sanctum::actingAs($user);

        return $user;
    }

    /**
     * List loan by customer
     *
     * @return void
     */
    public function test_list_loan_by_customer()
    {

        $this->login('customer');

        $response = $this->get('/api/v1/loan/list');

        $response->assertStatus(200);
    }

    /**
     * List loan by admin
     *
     * @return void
     */
    public function test_list_loan_by_admin()
    {

        $this->login();

        $response = $this->get('/api/v1/loan/list');

        $response->assertStatus(200);
    }

    /**
     * Create loan by customer
     *
     * @return void
     */
    public function test_create_loan_by_customer()
    {
        $payload = [
            'amount' => 10,
            'term' => 3,
            'frequency' => 'weekly'
        ];

        $this->login('customer');

        $response = $this->post('/api/v1/loan/create', $payload);

        $response->assertStatus(200);
    }

    /**
     * Create loan by admin
     *
     * @return void
     */
    public function test_create_loan_by_admin()
    {
        $payload = [
            'amount' => 10,
            'term' => 3,
            'frequency' => 'weekly'
        ];

        $this->login();

        $response = $this->post('/api/v1/loan/create', $payload);

        $response->assertStatus(403);
    }

    /**
     * Approve the loan by admin
     *
     * @return void
     */
    public function test_create_approve_loan_by_admin()
    {
        $payload = [
            'amount' => 10,
            'term' => 3,
            'frequency' => 'weekly'
        ];

        $this->login('customer');

        $response = $this->post('/api/v1/loan/create', $payload);

        $loanCreate = json_decode($response->getContent());

        $response->assertStatus(200);

        // Login as admin
        $this->login();

        $response = $this->post('/api/v1/loan/approve/'.$loanCreate->data->loan->id);

        $response->assertStatus(200);
    }

    /**
     * Approve the loan by customer
     *
     * @return void
     */
    public function test_create_approve_loan_by_customer()
    {
        $payload = [
            'amount' => 10,
            'term' => 3,
            'frequency' => 'weekly'
        ];

        $this->login('customer');

        $response = $this->post('/api/v1/loan/create', $payload);

        $loanCreate = json_decode($response->getContent());

        $response->assertStatus(200);

        // Login as admin
        $this->login('customer');

        $response = $this->post('/api/v1/loan/approve/'.$loanCreate->data->loan->id);

        $response->assertStatus(403);
    }


    /**
     * Test loan payment
     *
     * @return void
     */
    public function test_loan_payment()
    {
        $payload = [
            'amount' => 10,
            'term' => 3,
            'frequency' => 'weekly'
        ];

        $customerUser = $this->login('customer');

        $response = $this->post('/api/v1/loan/create', $payload);

        $loanCreate = json_decode($response->getContent());

        $response->assertStatus(200);

        // Login as admin
        $this->login('admin');

        $response = $this->post('/api/v1/loan/approve/'.$loanCreate->data->loan->id);

        $response->assertStatus(200);

        // Login as customer
        $this->login('customer', $customerUser->id);

        //Do the payment of loan
        $scheduleRepayment = $loanCreate->data->scheduled_payments[0];

        $payloadPayment = [
            'amount' => $scheduleRepayment->amount
        ];  

        $response = $this->post('/api/v1/loan/payment/'.$scheduleRepayment->id, $payloadPayment);

        $response->assertStatus(200);
    }

    /**
     * Test loan payment to validate do multiple time
     *
     * @return void
     */
    public function test_multiple_payment_of_loan_schedule()
    {
        $payload = [
            'amount' => 10,
            'term' => 3,
            'frequency' => 'weekly'
        ];

        $customerUser = $this->login('customer');

        $response = $this->post('/api/v1/loan/create', $payload);

        $loanCreate = json_decode($response->getContent());

        $response->assertStatus(200);

        // Login as admin
        $this->login('admin');

        $response = $this->post('/api/v1/loan/approve/'.$loanCreate->data->loan->id);

        $response->assertStatus(200);

        // Login as customer
        $this->login('customer', $customerUser->id);

        //Do the payment of loan
        $scheduleRepayment = $loanCreate->data->scheduled_payments[0];

        $payloadPayment = [
            'amount' => $scheduleRepayment->amount
        ];  

        $response = $this->post('/api/v1/loan/payment/'.$scheduleRepayment->id, $payloadPayment);

        $response = $this->post('/api/v1/loan/payment/'.$scheduleRepayment->id, $payloadPayment);

        $response->assertStatus(400);
    }

    /**
     * Test loan payment with all payment
     *
     * @return void
     */
    public function test_paid_loan_payment()
    {
        $payload = [
            'amount' => 10,
            'term' => 3,
            'frequency' => 'weekly'
        ];

        $customerUser = $this->login('customer');

        $response = $this->post('/api/v1/loan/create', $payload);

        $loanCreate = json_decode($response->getContent());

        $response->assertStatus(200);

        // Login as admin
        $this->login('admin');

        $response = $this->post('/api/v1/loan/approve/'.$loanCreate->data->loan->id);

        // Login as customer
        $this->login('customer', $customerUser->id);


        //Do the all payment of loan
        $scheduleRepayments = $loanCreate->data->scheduled_payments;

        foreach ($scheduleRepayments as $scheduleRepayment) {

            $payloadPayment = [
                'amount' => $scheduleRepayment->amount
            ];  

            $response = $this->post('/api/v1/loan/payment/'.$scheduleRepayment->id, $payloadPayment);

            $response->assertStatus(200);
        }

        $response = $this->get('/api/v1/loan/'.$loanCreate->data->loan->id);

        $loanDetail = json_decode($response->getContent());

        $this->assertEquals( 'paid', $loanDetail->data->status);
        
    }

    /**
     * Create loan by customer and get
     * Validate with login user to get loan and admin user to get loan
     *
     * @return void
     */
    public function test_create_loan_and_get()
    {
        $payload = [
            'amount' => 10,
            'term' => 3,
            'frequency' => 'weekly'
        ];

        $customerUser = $this->login('customer');

        $response = $this->post('/api/v1/loan/create', $payload);

        $loanCreate = json_decode($response->getContent());

        $response->assertStatus(200);

        $response = $this->get('/api/v1/loan/'.$loanCreate->data->loan->id);

        $response->assertStatus(200);

        $this->login('customer');

        $response = $this->get('/api/v1/loan/'.$loanCreate->data->loan->id);

        $response->assertStatus(400);

        $this->login('admin');

        $response = $this->get('/api/v1/loan/'.$loanCreate->data->loan->id);

        $response->assertStatus(200);
    }
}
