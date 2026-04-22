<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateReviewRequest;
use App\Repository\ReviewRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\NotUserRentalException;

use OpenApi\Attributes as OA;

class ReviewController extends Controller
{
    private ReviewRepositoryInterface $reviewRepository;

    public function __construct(ReviewRepositoryInterface $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }

    #[OA\Post(
        path: "/api/reviews",
        summary: "Création d’un nouvel avis",
        description: "La route est throttled à 60 requêtes par minute.",
        tags: ["Reviews"],
        security: [["sanctum" => []]],
        requestBody:new OA\RequestBody(
            required:true,
            content: new OA\JsonContent(
                required: ["rental_id", "rating", "comment"],
                properties: [
                    new OA\Property(property: "rental_id", type: "integer", example: 1),
                    new OA\Property(property: "rating", type: "integer", example: 5),
                    new OA\Property(property: "comment", type: "string", example: "Great rental experience!")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201, 
                description: "Avis créé",
                content: new OA\JsonContent(
                    example: [
                        "data" => [
                            "rental_id" => 1,
                            "rating" => 5,
                            "comment" => "Great rental experience!"
                        ]
                    ]
                )
            ),
            new OA\Response(
                response: 401, 
                description: "Non authentifié",
                content: new OA\JsonContent()
            ),
            new OA\Response(
                response: 403, 
                description: "Le client n'a pas fait cette location",
                content: new OA\JsonContent()
            ),
            new OA\Response(
                response: 404, 
                description: "Location non trouvée",
                content: new OA\JsonContent()
            ),
            new OA\Response(
                response: 409, 
                description: "Conflit - Un avis existe déjà pour cette location",
                content: new OA\JsonContent()
            ),
            new OA\Response(
                response: 422, 
                description: "Données invalides",
                content: new OA\JsonContent()
            ),

        ]
    )]
    public function store(CreateReviewRequest $request)
    {
        try{
            $request->validated();

            $user = Auth::user();
            $review = $this->reviewRepository->createReview($user, $request);

            if (!$review) {
                abort(CONFLICT, 'A review already exists for this rental');
            }

            return response()->json($review)->setStatusCode(CREATED);
        }
        catch(NotUserRentalException $e) {
            abort($e->status(), $e->message());
        }
        catch (QueryException $e) {
            abort(NOT_FOUND, 'Rental not found');
        }
        catch (Exception $e) {
            abort(SERVER_ERROR, 'server error');
        }
    }
}