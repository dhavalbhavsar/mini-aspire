<?php

namespace Modules\Loan\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

use Modules\Loan\Models\Loan;
use Modules\Loan\Http\Requests\CreateRequest;
use Modules\Loan\Http\Requests\PaymentRequest;
use App\Http\Controllers\Controller as BaseController;
use Modules\Loan\Repositories\LoanRepository;

class LoanController extends BaseController
{
    /**
     * List of loan.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $loans = Auth::user()->hasRole('admin') ? Loan::orderBy('id','DESC')->paginate(10) : Auth::user()->loans()->orderBy('id','DESC')->paginate(10);

        return $this->respond('Loan listed successfully', $loans, Response::HTTP_OK);
    }

    /**
     * Create loan.
     * @param Request $request
     * @param Loan $loan
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateRequest $request, LoanRepository $loanRepository)
    {
        $payload = $request->validated();

        $data = $loanRepository->create($payload, Auth::user());

        if(!$data)
            return $this->respond('Loan fail to create', [], Response::HTTP_BAD_REQUEST);

        return $this->respond('Loan created successfully', $data, Response::HTTP_OK);
    }

    /**
     * Detail of loan.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail($id)
    {
        $loan = Auth::user()->hasRole('admin') ? Loan::with('scheduledPayments')->find($id) : Loan::with('scheduledPayments')->whereId($id)->whereUserId(Auth::user()->id)->first();

        if(!$loan)
            return $this->respond('Fail to get loan detail', [], Response::HTTP_BAD_REQUEST);

        return $this->respond('Loan detail successfully', $loan, Response::HTTP_OK);
    }

    /**
     * Do the payment.
     * 
     * @param int $id (ScheduleRepayment Id)
     * @return \Illuminate\Http\JsonResponse
     */
    public function payment($id, PaymentRequest $request, LoanRepository $loanRepository)
    {
        $payload = $request->validated();

        $data = $loanRepository->loanPayment($id, $payload);

        return $this->respond($data['message'], $data['data'], $data['status'] === true ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST);
    }

    /**
     * Approve the loan.
     * 
     * @param Loan $loan
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve(Loan $loan)
    {
        if($loan->status === 'approved')
            return $this->respond('This loan already approved', [], Response::HTTP_BAD_REQUEST);

        $loan->status = 'approved';
        $loan->save();

        return $this->respond('Loan approved successfully', $loan->toArray() , Response::HTTP_OK);
    }
}
