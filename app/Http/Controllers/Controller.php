<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'TaskCraft API Documentation',
    description: 'API documentation for TaskCraft project',
    contact: new OA\Contact(
        email: 'admin@example.com'
    )
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'Main API Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    name: 'Authorization',
    in: 'header',
    scheme: 'bearer',
    bearerFormat: 'JWT'
)]
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}