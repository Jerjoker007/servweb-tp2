<?php

namespace App\Repository\Eloquent;

use App\Models\Equipment;
use App\Repository\EquipmentRepositoryInterface;
use App\Repository\Eloquent\BaseRepository;

class EquipmentRepository extends BaseRepository implements EquipmentRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(Equipment::class);
    }
}