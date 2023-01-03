<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Returns an Ok response
     *
     * @return JsonResponse
     */
    public function respondApiOk()
    {
        return $this->respond('ok');
    }

    /**
     * Http response
     *
     * @param     $data
     * @param int $status
     *
     * @return JsonResponse
     */
    public function respond($message, $data = [], $status = 200)
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}
