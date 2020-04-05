<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Http\Services\UtilityService;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserLoginRequest;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Illuminate\Support\Facades\Auth;
class UserController extends Controller
{
    protected $user;
    protected $utilityService;

   public function __construct()
   {
       $this->middleware("auth:user",['except'=>['login','register']]);
       $this->user = new User;
       $this->utilityService = new UtilityService;
   }

    public function register(UserRegisterRequest $request)
    {
        $password_hash = $this->utilityService->hash_password($request->password);
        $this->user->createUser($request,$password_hash);
        $success_message = "registration completed successfully";
     return  $this->utilityService->is200Response($success_message);
    }

    public function login(UserLoginRequest $request)
    {
        $credentials = $request->only(['email', 'password']);

        if (! $token = Auth::guard('user')->attempt($credentials)) {
            $responseMessage = "invalid username or password";
            return $this->utilityService->is422Response($responseMessage);
         }
         

        return $this->respondWithToken($token);
    }
    public function viewProfile()
    {
        $responseMessage="user profile";
        $data = Auth::guard("user")->user();
        return $this->utilityService->is200ResponseWithData($responseMessage, $data);
    }
    public function logUserOutAction()
    {
        Auth::guard("user")->logout();
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
        return Auth::guard("user")->refresh();
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

