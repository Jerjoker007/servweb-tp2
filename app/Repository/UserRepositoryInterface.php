<?php

namespace App\Repository;

use App\Http\Requests\CreateReviewRequest;
use App\Models\User;
use App\Repository\RepositoryInterface;

interface UserRepositoryInterface extends RepositoryInterface
{
    function getActiveRentals(User $user);
}