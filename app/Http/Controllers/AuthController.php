<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Repository\AuthRepositoryInterface;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;

class AuthController extends Controller
{
    private AuthRepositoryInterface $authRepository;

    public function __construct(AuthRepositoryInterface $repository)
    {
        $this->authRepository = $repository;
    }

    #[OA\Post(
        path: "/api/signup",
        summary: "Enregistrement d’un nouvel utilisateur",
        description: "La route est throttled à 5 requête par minute.",
        tags: ["Auth"],
        requestBody:new OA\RequestBody(
            required:true,
            content: new OA\JsonContent(
                required: ["first_name", "last_name", "email", "login", "password", "phone"],
                properties: [
                    new OA\Property(property: "first_name", type: "string", example:"Bob"),
                    new OA\Property(property: "last_name", type: "string", example:"Marley"),
                    new OA\Property(property: "email", type: "string", format: "email", example:"test@example.com"),
                    new OA\Property(property: "login", type: "string", example: "test"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "test123456"),
                    new OA\Property(property: "phone", type: "string", example:"418-555-1234")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201, 
                description: "Utilisateur créé",
                content: new OA\JsonContent(
                    example: [
                        "data" => [
                            "first_name" => "Jeremie",
                            "last_name" => "Paquin",
                            "email" => "test@example.ca",
                            "login" => "test",
                            "phone" => "418-555-1234"
                        ]
                    ]
                )
            ),
            new OA\Response(
                response: 422, 
                description: "Données invalides",
                content: new OA\JsonContent(
                    example: [
                        "message" => "The email field must be a valid email address.",
                        "errors" => [
                            "email"=> [
                                "The email field must be a valid email address."
                            ]
                        ]
                    ]
                )
            ),
            new OA\Response(
                response: 429, 
                description: "Trop de requêtes",
                content: new OA\JsonContent()
            )
        ]
    )]
    public function register(RegisterUserRequest $request) {
        try {
            $request->validated();
            $user = $this->authRepository->create([
                "first_name"=> $request->first_name,
                "last_name"=> $request->last_name,
                "email"=> $request->email,
                "login"=> $request->login,
                "phone"=> $request->phone,
                "role_id"=> 1,
                "password"=> bcrypt($request->password)
            ]);

            return (new UserResource($user))->response()->setStatusCode(CREATED);
        } catch (Exception $e) {
            abort(SERVER_ERROR, 'Server error');
        }
    }

    #[OA\Post(
        path: "/api/signin",
        summary: "Authentification d’un utilisateur existant",
        description: "La route est throttled à 5 requête par minute.",
        tags: ["Auth"],
        requestBody:new OA\RequestBody(
            required:true,
            content: new OA\JsonContent(
                required: ["login", "password"],
                properties: [
                    new OA\Property(property: "login", type: "string", example: "test"),
                    new OA\Property(property: "password", type: "string", format: "password", example: "test123456")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200, 
                description: "Utilisateur connecté",
                content: new OA\JsonContent(
                    example: [
                        "token" => "..."
                    ]
                )
            ),
            new OA\Response(
                response: 401, 
                description: "Non authorisé",
                content: new OA\JsonContent(
                    example: [
                        "message" => "Unauthorized"
                    ]
                )
            ),
            new OA\Response(
                response: 422, 
                description: "Données invalides",
                content: new OA\JsonContent(
                    example: [
                        "message" => "The password field is required.",
                        "errors" => [
                            "password" => [
                                "The password field is required."
                            ]
                        ]
                    ]
                )
            ),
            new OA\Response(
                response: 429, 
                description: "Trop de requêtes",
                content: new OA\JsonContent()
            )
        ]
    )]
    public function login(LoginUserRequest $request) {
        try {
            $request->validated();
            if (Auth::attempt($request->toArray())) {

                $token = $request->user()->createToken('login');

                return (response()->json([ 
                    'token' => $token->plainTextToken
                ]))->setStatusCode(OK);
            } else {
                abort(UNAUTHORIZED, 'Unauthorized');
            }
        } catch (Exception $e) {
            abort(SERVER_ERROR, 'Server error');
        }
    }

    #[OA\Post(
        path: "/api/signout",
        summary: "Révocation des tokens de l’utilisateur",
        description: "La route est throttled à 5 requête par minute.",
        tags: ["Auth"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: "default",
                description: "Default JSON response",
                content: [
                    "application/json" => new OA\JsonContent()
                ]
            ),
            new OA\Response(
                response: 204, 
                description: "Utilisateur deconnecté",
            ),
            new OA\Response(
                response: 401, 
                description: "Non authorisé",
                content: new OA\JsonContent(
                    example: [
                        "message" => "Unauthenticated."
                    ]
                )
            ),
            new OA\Response(
                response: 429, 
                description: "Trop de requêtes",
                content: new OA\JsonContent()
            )
        ]
    )]
    public function logout(Request $request) {
        try {
            //https://laravel.com/docs/12.x/sanctum#revoking-tokens
            $request->user()->tokens()->delete();
            return response()->noContent();
        } catch (Exception $e) {
            abort(SERVER_ERROR, 'Server error');
        }
    }

    #[OA\Post(
        path: "/api/refresh",
        summary: "Rafraichissement du token existant",
        description: "La route est throttled à 5 requête par minute.",
        tags: ["Auth"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Token rafraîchi",
                content: new OA\JsonContent(
                    example: [
                        "token" => "..."
                    ]
                )
            ),
            new OA\Response(
                response: 401, 
                description: "Non authorisé",
                content: new OA\JsonContent(
                    example: [
                        "message" => "Unauthenticated."
                    ]
                )
            ),
            new OA\Response(
                response: 429, 
                description: "Trop de requêtes",
                content: new OA\JsonContent()
            )
        ]
    )]
    public function refresh(Request $request) {
        try {
            //https://laravel.com/docs/12.x/sanctum#revoking-tokens
            $request->user()->currentAccessToken()->delete();
            $token = $request->user()->createToken('login');
            return response()->json([
                'token' => $token->plainTextToken
            ])->setStatusCode(OK);
        } catch (Exception $e) {
            abort(SERVER_ERROR, 'Server error');
        }
    }

    #[OA\Get(
        path: "/api/me",
        summary: "Accès à l’utilisateur connecté",
        description: "La route est throttled à 5 requête par minute.",
        tags: ["Auth"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(
                response: 200, 
                description: "Information de l'utilisateur",
                content: new OA\JsonContent(
                    example: [
                        "data" => [
                            "first_name" => "Jeremie",
                            "last_name" => "Paquin",
                            "email" => "test@example.ca",
                            "login" => "test",
                            "phone" => "418-555-1234"
                        ]
                    ]
                )
            ),
            new OA\Response(
                response: 401, 
                description: "Non authorisé",
                content: new OA\JsonContent(
                    example: [
                        "message" => "Unauthenticated."
                    ]
                )
            ),
            new OA\Response(
                response: 429, 
                description: "Trop de requêtes",
                content: new OA\JsonContent()
            )
        ]
    )]
    public function me() {
        try {
            $user = Auth::user();
            return (new UserResource($user))->response()->setStatusCode(OK);
        } catch (Exception $e) {
            abort(SERVER_ERROR, 'Server error');
        }
    }
}
