<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Repository\UserRepositoryInterface;

use OpenApi\Attributes as OA;

class UserController extends Controller
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    #[OA\Patch(
        path: "/api/me",
        summary: "Mise à jour du mot de passe utilisateur",
        description: "La route est protégée et throttled à 60 requêtes par minute.",
        tags: ["Users"],
        security: [["sanctum" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "password", type: "string", format: "password", example: "newpassword123"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "newpassword123")
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Utilisateur mis à jour"),
            new OA\Response(response: 401, description: "Non authentifié"),
            new OA\Response(response: 422, description: "Données invalides"),
        ]
    )]
    public function update(UpdateUserRequest $request) {
        try{
            $request->validated();
            $user = Auth::user();
            $this->userRepository->update($user->id, [
                'password' => bcrypt($request->input('password')),
            ]);

            $updatedUser = $this->userRepository->getById($user->id);

            return response()->json(new UserResource($updatedUser))->setStatusCode(OK);
        }
        catch (Exception $e) {
            abort(SERVER_ERROR, 'server error');
        }
    }
}
