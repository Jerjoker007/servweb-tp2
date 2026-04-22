<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Equipment;
use App\Repository\EquipmentRepositoryInterface;
use App\Http\Requests\EquipmentRequest;
use App\Http\Resources\EquipmentResource;
use App\Exceptions\EquipmentInUseException;

class EquipmentController extends Controller
{

    private EquipmentRepositoryInterface $equipmentRepository;

    public function __construct(EquipmentRepositoryInterface $repository)
    {
        $this->equipmentRepository = $repository;
    }

    #[OA\Post(
        path: "/api/equipment",
        summary: "Enregistrement d’un nouvel équipement.",
        description: "La route est throttled à 60 requête par minute et nécessite être un admin.",
        tags: ["Equipment"],
        security: [["sanctum" => []]],
        requestBody:new OA\RequestBody(
            required:true,
            content: new OA\JsonContent(
                required: ["name", "description", "daily_price", "category_id"],
                properties: [
                    new OA\Property(property: "name", type: "string", example:"Ski"),
                    new OA\Property(property: "description", type: "string", example:"Ski alpin"),
                    new OA\Property(property: "daily_price", type: "string", format: "email", example: 10.99),
                    new OA\Property(property: "category_id", type: "integer", example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201, 
                description: "Équipement créé",
                content: new OA\JsonContent(
                    example: [
                        "data" => [
                            "name" => "Ski",
                            "description" => "Ski alpin",
                            "daily_price" => 10.99,
                            "category_id" => 1,
                        ]
                    ]
                )
            ),
            new OA\Response(
                response: 401, 
                description: "Non connecté",
                content: new OA\JsonContent(
                    example: [
                        "message" => "Unauthenticated."
                    ]
                )
            ),
            new OA\Response(
                response: 403, 
                description: "Non authorisé",
                content: new OA\JsonContent(
                    example: [
                        "message" => "Forbidden"
                    ]
                )
            ),
            new OA\Response(
                response: 422, 
                description: "Données invalides",
                content: new OA\JsonContent(
                    example: [
                        "message" => "The name field is required.",
                        "errors" => [
                            "name"=> [
                                "The name field is required."
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
    public function store(EquipmentRequest $request)
    {
        try {
            if (Auth::user()->role->name != 'admin')
            {
                abort(FORBIDDEN, "Forbidden");
            }
                
            $request->validated();
            $equipment = $this->equipmentRepository->create($request->toArray());
            return (new EquipmentResource($equipment))->response()->setStatusCode(CREATED);

        } catch (Exception $e) {
            abort(SERVER_ERROR, 'Server error');
        }
    }

    #[OA\Put(
        path: "/api/equipment/{id}",
        summary: "Changement d’un équipement.",
        description: "La route est throttled à 60 requête par minute et nécessite être un admin.",
        tags: ["Equipment"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Equipment ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        requestBody:new OA\RequestBody(
            required:true,
            content: new OA\JsonContent(
                required: ["name", "description", "daily_price", "category_id"],
                properties: [
                    new OA\Property(property: "name", type: "string", example:"Ski"),
                    new OA\Property(property: "description", type: "string", example:"Ski alpin"),
                    new OA\Property(property: "daily_price", type: "string", format: "email", example: 10.99),
                    new OA\Property(property: "category_id", type: "integer", example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201, 
                description: "Équipement créé",
                content: new OA\JsonContent(
                    example: [
                        "data" => [
                            "name" => "Ski",
                            "description" => "Ski alpin",
                            "daily_price" => 10.99,
                            "category_id" => 1,
                        ]
                    ]
                )
            ),
            new OA\Response(
                response: 401, 
                description: "Non connecté",
                content: new OA\JsonContent(
                    example: [
                        "message" => "Unauthenticated."
                    ]
                )
            ),
            new OA\Response(
                response: 403, 
                description: "Non authorisé",
                content: new OA\JsonContent(
                    example: [
                        "message" => "Forbidden"
                    ]
                )
            ),
            new OA\Response(
                response: 404, 
                description: "Équipement non trouvé",
                content: new OA\JsonContent(
                    example: [
                        "message" => "No query results for model [App\\Models\\Equipment] 50"
                    ]
                )
            ),
            new OA\Response(
                response: 422, 
                description: "Données invalides",
                content: new OA\JsonContent(
                    example: [
                        "message" => "The name field is required.",
                        "errors" => [
                            "name"=> [
                                "The name field is required."
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
    public function update(EquipmentRequest $request, int $id)
    {
        try {
            if (Auth::user()->role->name != 'admin')
            {
                abort(FORBIDDEN, "Forbidden");
            }
                
            $request->validated();
            $equipment = $this->equipmentRepository->update($id, $request->toArray());
            return (new EquipmentResource($equipment))->response()->setStatusCode(OK);
            
        } catch (QueryException $e) {
            abort(NOT_FOUND, 'Invalid Id');
        } catch (Exception $e) {
            abort(SERVER_ERROR, 'Server error');
        }
    }

    #[OA\Delete(
        path: "/api/equipment/{id}",
        summary: "Supprimer un équipement.",
        description: "La route est throttled à 60 requête par minute et nécessite être un admin.",
        tags: ["Equipment"],
        security: [["sanctum" => []]],
        parameters: [
            new OA\Parameter(
                name: "id",
                description: "Equipment ID",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer", example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: "default",
                description: "Réponse JSON par défaut.",
                content: [
                    "application/json" => new OA\JsonContent()
                ]
            ),
            new OA\Response(
                response: 204, 
                description: "Équipement supprimé",
            ),
            new OA\Response(
                response: 401, 
                description: "Non connecté",
                content: new OA\JsonContent(
                    example: [
                        "message" => "Unauthenticated."
                    ]
                )
            ),
            new OA\Response(
                response: 403, 
                description: "Non authorisé",
                content: new OA\JsonContent(
                    example: [
                        "message" => "Forbidden"
                    ]
                )
            ),
            new OA\Response(
                response: 404, 
                description: "Équipement non trouvé",
                content: new OA\JsonContent(
                    example: [
                        "message" => "No query results for model [App\\Models\\Equipment] 50"
                    ]
                )
            ),
            new OA\Response(
                response: 409, 
                description: "Équipement présentement utilisé",
                content: new OA\JsonContent(
                    example: [
                        "message" => "Equipment is in used and cannot be deleted."
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
    public function destroy(int $id)
    {
        try {
            if (Auth::user()->role->name != 'admin')
            {
                abort(FORBIDDEN, "Forbidden");
            }

            $this->equipmentRepository->delete($id);
            return response()->noContent();
        
        } catch (QueryException $e) {
            abort(NOT_FOUND, 'Invalid Id');
        } catch (EquipmentInUseException $e){
            abort($e->status(), $e->message());
        } catch (Exception $e) {
            abort(SERVER_ERROR, 'Server error');
        }
    }
}
