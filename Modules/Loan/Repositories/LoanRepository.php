<?php

namespace Modules\Loan\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Modules\Loan\Models\Loan;
use Modules\Loan\Models\ScheduleRepayment;
use App\Models\User;

class LoanRepository
{
    CONST DEFAULT_LOAN_FREQUENCY = 'weekly';

    use AuthorizesRequests;

    /**
     * Create Loan & Schedule Payment
     *
     * @param  Array $payload
     * @param  Loan $loan
     * @return array
     */
    public function create($payload, User $user)
    {
        $amount = $payload['amount'];
        $term   = $payload['term'];
        $frequency = $payload['frequency'] ?? self::DEFAULT_LOAN_FREQUENCY;

        try {

            $loan = $user->loans()->create([
                'amount'    => $amount,
                'term'      => $term,
                'frequency' => $frequency
            ]);

            $scheduleAmount = ($amount / $term);

            $schedulePayments = [];

            $schedulePaymentRaw = [
                'amount'    => round($scheduleAmount,2)
            ];

            $rawAmount = 0;

            for ($i = 0; $i < $term; $i++) {

                if($frequency === 'weekly')
                    $schedulePaymentRaw['schedule_date'] = Carbon::now()->addWeeks(($i + 1))->format('Y-m-d h:i:s');
                else if($frequency === 'monthly')
                    $schedulePaymentRaw['schedule_date'] = Carbon::now()->addMonths(($i + 1))->format('Y-m-d h:i:s');
                else
                    $schedulePaymentRaw['schedule_date'] = Carbon::now()->addYears(($i + 1))->format('Y-m-d h:i:s');

                if($term == ($i + 1))
                    $schedulePaymentRaw['amount'] = round(($amount - $rawAmount),2);

                $rawAmount += $schedulePaymentRaw['amount'];

                $schedulePayments[] = $schedulePaymentRaw;
            }

            $createdSchedulePayments = $loan->scheduledPayments()->createMany($schedulePayments);

        } catch (\Exception $e) {

            return false;
        }

        return [
            'loan' => $loan,
            'scheduled_payments' => $createdSchedulePayments
        ];
    }

    /**
     * Loan Payment
     *
     * @param  array $payload
     *
     * @return array $responseData
     */
    public function loanPayment($scheduleRepaymentId, $payload, $skipLoginUser = false)
    {
        $amount = $payload['amount'];

        try {

            $scheduleRepayment = ScheduleRepayment::find($scheduleRepaymentId);

            if (empty($scheduleRepayment)) {
                return [
                    'data' => [],
                    'message' => 'Schedule Repayment Not Found',
                    'status' => false
                ];
            }

            if(!$skipLoginUser) {

                if($scheduleRepayment->loan->user_id != Auth::user()->id) {
                    return [
                        'data' => [],
                        'message' => 'Loan not belongs to login user',
                        'status' => false
                    ];
                }

            }

            // Validate loan is approved
            if($scheduleRepayment->loan->status === 'approved') {

                if($scheduleRepayment->status === 'pending') {

                    if($amount >= $scheduleRepayment->amount) {

                        $scheduleRepayment->status = 'paid';
                        $scheduleRepayment->amount_paid = $amount;
                        $scheduleRepayment->save();

                        // Check all loan payment then close the loan
                        $this->closeLoan($scheduleRepayment);

                        return [
                            'data' => $scheduleRepayment->toArray(),
                            'message' => 'Payment successful',
                            'status' => true
                        ];

                    } else {
                        return [
                            'data' => [],
                            'message' => 'Loan amount should not be partial',
                            'status' => false
                        ];
                    }

                } else {
                    return [
                        'data' => [],
                        'message' => 'Loan amount already paid',
                        'status' => false
                    ];
                }
                
            } else {
                return [
                    'data' => [],
                    'message' => 'Loan is not approved',
                    'status' => false
                ];
            }

        } catch (\Exception $e) {
            return [
                'data' => [],
                'message' => 'Loan payment failed',
                'status' => false
            ];
        }
    }

    /**
     * Close Loan
     * 
     * @param  mixed $scheduledPaymentRecord
     * @return void
     */
    public function closeLoan(ScheduleRepayment $scheduleRepayment)
    {
        $scheduleRepayments = $scheduleRepayment->loan->scheduledPayments;

        if ($scheduleRepayments->where('status','paid')->count() == $scheduleRepayments->count()) {
            $scheduleRepayment->loan->update(['status' => 'paid']);
        }
    }
}