<?php

namespace App\Http\Controllers;

use App\Repository\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class RentalController extends Controller
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }


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
