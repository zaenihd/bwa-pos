<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthenticateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(AuthenticateUserRequest $request)
    {
        $credential = $request->validated();

        $user = User::where('email', $credential['email'])->first();

        if (!$user || !Hash::check($credential['password'], $user->password)) {
            return ApiResponses::error('Invalid Credential', Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponses::success(
            ['token' => $token, 'user' => new UserResource($user)],
            "Login Successfull"
        );
    }

    public function me(Request $request){
        return ApiResponses::success(
            new UserResource($request->user()),
            "User data"
        );
    }

    public function logout(Request $request)
    {
    $request->user()->currentAccessToken()->delete();

    return ApiResponses::success(
        null,
        "Logout Successfull"
    );
    }
    //
}
