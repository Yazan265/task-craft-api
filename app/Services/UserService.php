<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
   
    public function registerUser(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        return User::create($data);
    }
   
public function loginUser(array $credentials): ?string
{
  
    $token = auth()->guard('api')->attempt($credentials);

    if (!$token) {
        return null; 
    }

    return $token; 
}
}