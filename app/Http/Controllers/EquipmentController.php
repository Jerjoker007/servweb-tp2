<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipment;
use App\Repository\EquipmentRepositoryInterface;
use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Resources\EquipmentResource;

class EquipmentController extends Controller
{

    private EquipmentRepositoryInterface $equipmentRepository;

    public function __construct(EquipmentRepositoryInterface $equipmentRepository)
    {
        $this->equipmentRepository = $equipmentRepository;
    }

    public function store(StoreEquipmentRequest $request)
    {
        try {
            $user = Auth::user();
            if ($user->role()->name != 'admin')
            {
                abort(FORBIDDEN, "Forbidden");
            }
                
            $request->validated();
            $equipment = $this->equipmentRepository->create($request);
            return (new EquipmentResource($equipment))->response()->setStatusCode(CREATED);
        } catch (Exception $e) {
            abort(SERVER_ERROR, 'Server error');
        }
    }
}
