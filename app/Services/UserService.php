<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
   
  public function registerUser(array $data): array
{
    $data['password'] = Hash::make($data['password']);
    $user = User::create($data);

    // توليد التوكن فوراً للمستخدم الجديد
    $token = auth()->guard('api')->login($user);

    return [
        'user' => $user,
        'token' => $token,
    ];
}
   
public function loginUser(array $credentials): ?string
{
  
    $token = auth()->guard('api')->attempt($credentials);

    if (!$token) {
        return null; 
    }

    return $token; 
}
public function refreshToken(): string
{
    // تقوم بتجديد التوكن الحالي الصادر للمستخدم وإرجاع توكن جديد
    return auth('api')->refresh();
}
}