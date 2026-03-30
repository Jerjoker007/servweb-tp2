<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;

class AuthController extends Controller
{
    public function register(RegisterUserRequest $request) {
        try {
            $request->validated();
            $user = User::create($request->toArray());
            $user->password = bcrypt($request->password);
            $user->save();

            return (new UserResource($user))->response()->setStatusCode(CREATED);
        } catch (Exception $e) {
            abort(SERVER_ERROR, 'Server error');
        }
    }

    public function login(LoginUserRequest $request) {
        try {
            $request->validated();
            if (Auth::attempt($request->toArray())) {

                $token = $request->user()->createToken('login');

                return (response()->json([ 
                    'token' => $token->plainTextToken
                ]))->setStatusCode(OK);
            } else {
                abort(UNAUTHORIZED, 'Unauthorized');
            }
        } catch (Exception $e) {
            abort(SERVER_ERROR, 'Server error');
        }
    }

    public function logout(Request $request) {
        try {
            //https://laravel.com/docs/12.x/sanctum#revoking-tokens
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'message' => "Logged out"
            ])->setStatusCode(OK);
        } catch (Exception $e) {
            abort(SERVER_ERROR, 'Server error');
        }
    }

    public function refreshToken(Request $request) {
        try {
            //https://laravel.com/docs/12.x/sanctum#revoking-tokens
            $request->user()->currentAccessToken()->delete();
            $token = $request->user()->createToken('login');
            return response()->json([
                'token' => $token->plainTextToken
            ])->setStatusCode(OK);
        } catch (Exception $e) {
            abort(SERVER_ERROR, 'Server error');
        }
    }

    public function getUser() {
        try {
            $user = Auth::user();
            return (new UserResource($user))->response()->setStatusCode(OK);
        } catch (Exception $e) {
            abort(SERVER_ERROR, 'Server error');
        }
    }
}
