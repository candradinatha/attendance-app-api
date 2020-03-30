<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Hash;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\AuthResource;
use App\User;
use GuzzleHttp\Psr7\ServerRequest;
use Laravel\Passport\Client;

class UserController extends Controller
{
    //

    public function login(LoginRequest $request)
    {
        $user = User::where('phone', $request->credential)
                ->orWhere('email', $request->credential)
                ->orWhere('employee_id', $request->credential)
                ->first();

        $access = $this->attemptLogin($request, $user);

        return new AuthResource([
            'access' => $access,
            'user' => $user
        ]);
    }
    
    public function register(RegisterRequest $request)
    {
        $user = new User($request->all());
        $user->password = bcrypt($request->password);
        $user->save();
        
        return new AuthResource([
            'access' => $this->requestTokens($request),
            'user' => $user
        ]);
    }

    private function attemptLogin($request, $user)
    {
        Validator::make($request->all(), [
            'credential' => ['required', function ($attr, $value, $message) use ($user) {
                if ($user === null) {
                    throw new \Illuminate\Auth\AuthenticationException('Unauthenticated');
                } 
            }],
            'password' => ['required', function ($attr, $value, $message) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    return $message('Wrong Password');
                }
            }]
        ])->validate();

        $request->merge([
            'username' => $user->email
        ]);

        return $this->requestTokens($request);
    }

    private function requestTokens($request)
    {
        $http     = new \GuzzleHttp\Client;
        $passport = Client::where('personal_access_client', false)
                    ->first();

        $request->merge([
            'grant_type'    => 'password',
            'client_id'     => $passport->id,
            'client_secret' => $passport->secret,
            'scope'         => '*',
            'provider'      => 'users',
        ]);

        if(!$request->exists('username')) {
            $request->merge([
                'username' => $request->email,
            ]);
        }

        $_POST = $request->only([
            'username', 
            'password',
            'grant_type',
            'client_id',
            'client_secret',
            'scope',
            'provider',
        ]);

        $response = app(\Laravel\Passport\Http\Controllers\AccessTokenController::class)
            ->issueToken(ServerRequest::fromGlobals());

        return json_decode((string) $response->content(), true);
    }
}
