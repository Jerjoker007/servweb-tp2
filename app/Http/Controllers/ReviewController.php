<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateReviewRequest;
use App\Repository\ReviewRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    private ReviewRepositoryInterface $reviewRepository;

    public function __construct(ReviewRepositoryInterface $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }


    public function store(CreateReviewRequest $request)
    {
        try{
            if (!Auth::check()) {
                abort(FORBIDDEN, 'Forbidden');
            }

            $request->validated();

            $user = Auth::user();
            $review = $this->reviewRepository->createReview($user, $request);

            if (!$review) {
                abort(CONFLICT, 'A review already exists for this rental');
            }

            return response()->json($review)->setStatusCode(CREATED);
        }
        catch (QueryException $e) {
            abort(NOT_FOUND, 'Rental not found');
        }
        catch (Exception $e) {
            abort(SERVER_ERROR, 'server error');
        }
    }
}