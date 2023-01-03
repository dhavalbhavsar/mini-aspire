<?php

namespace Modules\Auth\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Http\Controllers\Controller as BaseController;

use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;

class AuthController extends BaseController
{

    private $token;

    /**
     * Constructor
     * @return void
     */
    public function __construct()
    {
        $this->token = config('auth.sanctum_token');
    }

    /**
     * Registration
     *
     * @param  RegisterRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        try {

            $payload = $request->validated();

            $role = $request->input('role') === 'admin' ? 'admin' : 'customer';

            $user = User::create([
                'name' => $payload['name'],
                'email' => $payload['email'],
                'password' => bcrypt($payload['password']),
            ]);

            $user->assignRole($role);

            $data = [
                'user' => $user,
                'token' => $user->createToken($this->token)->plainTextToken
            ];

            return $this->respond('User created successfully', $data , Response::HTTP_CREATED);

        } catch (\Exception $e) {
            
            return $this->respond('User fail to create',  [] , Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * Login
     *
     * @param  LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        try {

            $payload = $request->validated();

            if (!Auth::attempt($request->only(['email', 'password'])))
                return $this->errorResponse('Email OR Password does not match with our record.', Response::HTTP_UNAUTHORIZED);

            $user = User::whereEmail($payload['email'])->first();

            $data = [
                'user' => $user,
                'token' => $user->createToken($this->token)->plainTextToken
            ];

            return $this->respond('User logged in successfully', $data , Response::HTTP_OK);

        } catch (\Exception $e) {

            return $this->respond('User fail to login',  [] , Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Logout
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        if (auth()->user()->tokens()->delete())
            return $this->respond('User logged out successfully', [] , Response::HTTP_OK);   
    }
    
}
