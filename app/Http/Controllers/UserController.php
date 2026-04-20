<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Repository\UserRepositoryInterface;

class UserController extends Controller
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function update(UpdateUserRequest $request) {
        try{
            if (!Auth::check()) {
                abort(FORBIDDEN, 'Forbidden');
            }

            $request->validated();
            $user = Auth::user();
            $this->userRepository->update($user->id, [
                'password' => bcrypt($request->input('password')),
            ]);

            $updatedUser = $this->userRepository->getById($user->id);

            return response()->json(new UserResource($updatedUser))->setStatusCode(OK);
        }
        catch (Exception $e) {
            abort(SERVER_ERROR, 'server error');
        }
    }
}
