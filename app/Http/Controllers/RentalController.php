<?php

namespace App\Http\Controllers;

use App\Repository\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;

use OpenApi\Attributes as OA;

class RentalController extends Controller
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    #[OA\Get(
        path: "/api/me/rentals",
        summary: "Récupération des locations actives de l’utilisateur",
        description: "La route est protégée et throttled à 60 requêtes par minute.",
        tags: ["Rentals"],
        security: [["sanctum" => []]],
        responses: [
            new OA\Response(response: 200, description: "Locations récupérées"),
            new OA\Response(response: 401, description: "Non authentifié"),
        ]
    )]
    public function index()
    {
        try {
            $user = Auth::user();

            return response()->json($this->userRepository->getActiveRentals($user))->setStatusCode(OK);
        }
        catch (Exception $e) {
            abort(SERVER_ERROR, 'server error');
        }
    }
}
