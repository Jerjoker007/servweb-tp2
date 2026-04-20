<?php

namespace App\Repository;

use App\Repository\RepositoryInterface;
use App\Http\Requests\CreateReviewRequest;
use App\Models\User;

interface ReviewRepositoryInterface extends RepositoryInterface
{
    function createReview(User $user, CreateReviewRequest $request);
}