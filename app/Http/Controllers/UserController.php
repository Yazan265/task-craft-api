<?php


namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

 
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = $this->userService->registerUser($validatedData);

        return response()->json([
            'message' => 'User registered successfully!',
            'data' => $user
        ], 201);
    }

public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|string|email',
        'password' => 'required|string',
    ]);

    $token = $this->userService->loginUser($credentials);

    if (!$token) {
        return response()->json([
            'message' => 'Unauthorized! Invalid email or password.'
        ], 401);
    }

    return response()->json([
        'message' => 'Login successful!',
        'access_token' => $token,
        'token_type' => 'bearer',
        'expires_in' => auth()->guard('api')->factory()->getTTL() * 60 
    ], 200);
}
}