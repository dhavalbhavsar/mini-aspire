<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

use Modules\Loan\Repositories\LoanRepository;
use App\Models\User;
use Modules\Loan\Models\Loan;

class LoanTest extends TestCase
{
    /**
     * Test the loan 
     *
     * @return void
     */
    public function test_loan_create()
    {
        $faker = \Faker\Factory::create();

        $user = User::create([
            'name' => $faker->name(),
            'email' => $faker->email(),
            'password' => $faker->password()
        ]);

        $user->assignRole('customer');

        $loanRepository = new LoanRepository();

        $create = $loanRepository->create([
            'amount' => 10,
            'term' => 3,
            'frequency' => 'weekly'
        ], $user);

        $this->assertEquals(is_array($create), true);
    }

    /**
     * Test the loan 
     *
     * @return void
     */
    public function test_fail_loan_create()
    {
        $faker = \Faker\Factory::create();

        $user = User::create([
            'name' => $faker->name(),
            'email' => $faker->email(),
            'password' => $faker->password()
        ]);

        $user->assignRole('customer');

        $loanRepository = new LoanRepository();

        $create = $loanRepository->create([
            'amount' => 'asdssd',
            'term' => 3,
            'frequency' => 'weekly'
        ], $user);

        $this->assertEquals($create, false);
    }

    /**
     * Test the loan with payment
     *
     * @return void
     */
    public function test_loan_with_payment()
    {
        $faker = \Faker\Factory::create();

        $user = User::create([
            'name' => $faker->name(),
            'email' => $faker->email(),
            'password' => Hash::make($faker->password())
        ]);

        $user->assignRole('customer');

        $loanRepository = new LoanRepository();

        $loan = $loanRepository->create([
            'amount' => 10,
            'term' => 3,
            'frequency' => 'weekly'
        ], $user);

        $this->assertEquals(is_array($loan), true);

        $loanObject = Loan::find($loan['loan']->id);

        // Mark loan approved
        $loanObject->status = 'approved';
        $loanObject->save();

        foreach($loan['scheduled_payments'] as $schedulePayment) {

            $loanPayment = $loanRepository->loanPayment($schedulePayment->id, ['amount' => $schedulePayment->amount], true);

            $this->assertEquals($loanPayment['status'], true);

        }

        $loanObject->refresh();

        $this->assertEquals($loanObject->status == 'paid', true);
    }

    /**
     * Test the loan with fail payment
     *
     * @return void
     */
    public function test_fail_with_loan_payment()
    {
        $faker = \Faker\Factory::create();

        $user = User::create([
            'name' => $faker->name(),
            'email' => $faker->email(),
            'password' => Hash::make($faker->password())
        ]);

        $user->assignRole('customer');

        $loanRepository = new LoanRepository();

        $loan = $loanRepository->create([
            'amount' => 10,
            'term' => 3,
            'frequency' => 'weekly'
        ], $user);

        $this->assertEquals(is_array($loan), true);

        $loanObject = Loan::find($loan['loan']->id);

        foreach($loan['scheduled_payments'] as $schedulePayment) {

            $loanPayment = $loanRepository->loanPayment($schedulePayment->id, ['amount' => $schedulePayment->amount]);

            $this->assertEquals($loanPayment['status'], false);

        }

        $loanObject->refresh();

        $this->assertEquals($loanObject->status == 'paid', false);
    }

}
