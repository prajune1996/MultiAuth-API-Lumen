<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Admin;
use App\Http\Services\UtilityService;
use App\Http\Requests\AdminRegisterRequest;
use App\Http\Requests\AdminLoginRequest;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Illuminate\Support\Facades\Auth;
class AdminController extends Controller
{
    protected $admin;
    protected $utilityService;
    public function __construct()
    {
        $this->middleware("auth:admin",['except'=>['login','register']]);
        $this->admin = new Admin;
        $this->utilityService = new UtilityService;
    }

    public function register(AdminRegisterRequest $request)
    {
        $password_hash = $this->utilityService->hash_password($request->password);
        $this->admin->createUser($request,$password_hash);
        $success_message = "registration completed successfully";
        return  $this->utilityService->is200Response($success_message);
    }

    public function login(AdminLoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (! $token = Auth::guard('admin')->attempt($credentials)) {
            $responseMessage = "invalid username or password";
            return $this->utilityService->is422Response($responseMessage);
         }
         

        return $this->respondWithToken($token);
    }
    public function viewProfile()
    {
        $responseMessage="admin profile";
        $data = Auth::guard("admin")->user();
        return $this->utilityService->is200ResponseWithData($responseMessage, $data);
    }
    public function logUserOutAction()
    {
        Auth::guard("admin")->logout();
        $responseMessage = "Successfully logged out";
        return $this->utilityService->is200Response($responseMessage);
    }

    public function logout()
    {
        try
        {
            $this->logUserOutAction();
        }catch(TokenExpiredException $e)
        {
            $responseMessage =" Token already Expired";
            $this->tokenExpiredException($responseMessage);
        }
    }
    public function tokenExpiredException()
    {
        return $this->utilityService->is422Response($responseMessage);
    }
    public function refreshTokenAction()
    {
        return Auth::guard("admin")->refresh();
    }

    public function refreshToken()
    {
        try
        {
            return $this->respondWithToken($this->refreshTokenAction());
        }catch(tokenExpiredException $ex)
        {
            $responseMessage = "Token refresh failed";
            return $this->tokenExpiredException($responseMessage);
        }
    }
}
