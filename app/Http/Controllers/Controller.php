<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

define("OK", 200);
define("CREATED", 201);
define("NO_CONTENT", 204);
define("UNAUTHORIZED", 401);
define("FORBIDDEN", 403);
define("NOT_FOUND", 404);
define("CONFLICT", 409);
define("INVALID_DATA", 422);
define("SERVER_ERROR", 500);

#[OA\Info(title: "API d'un service d'authentification", version: "1.0")]
//https://github.com/DarkaOnLine/L5-Swagger/wiki/Examples#laravel-sanctum
#[OA\SecurityScheme(
    securityScheme: "sanctum",
    type: "apiKey",
    description: "Entrer le token sous le format (Bearer TOKEN)",
    name: "Authorization",
    in: "header"
)]
abstract class Controller
{
    //
}
