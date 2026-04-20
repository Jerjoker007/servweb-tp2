<?php

namespace App\Repository\Eloquent;

use App\Models\Review;
use App\Repository\ReviewRepositoryInterface;
use App\Repository\Eloquent\BaseRepository;
use App\Models\User;
use App\Http\Requests\CreateReviewRequest;

class ReviewRepository extends BaseRepository implements ReviewRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(Review::class);
    }


    public function createReview(User $user, CreateReviewRequest $request)
    {
        $rentalId = $request->input('rental_id');

        $user->rentals()->findOrFail($rentalId);

        $alreadyReviewed = Review::where('rental_id', $rentalId)->exists();

        if ($alreadyReviewed) {
            return null;
        }

        return Review::create([
            'rental_id' => $rentalId,
            'user_id' => $user->id,
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment'),
        ]);
    }
}