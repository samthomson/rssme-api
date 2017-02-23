<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Validator;
use Hash;
use Illuminate\Http\Request;
use App\Models\User;

use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Auth;

class AuthController extends Controller
{

    public function register(Request $request) {

        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users|email',
            'password' => 'required',
        ]);


        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors()->all(),
                'code' => 422
            ], 200);
        }

        // stil here? Validation was ok..
        $oUser = new User;

        $oUser->email = $request->get('email');
        $oUser->password = Hash::make($request->get('password'));

        $oUser->save();

        $credentials = $request->only(['email', 'password']);
        $token = JWTAuth::attempt($credentials);

        // all good, user registered/created
        return response()->json(
            [
                'code' => 200,
                'token' => compact('token')['token']
            ], 200
        );

    }

    public function authenticate(Request $request) {
        // validate params, attempt to find user, log them in, handle fails with thought out http responses

        $bAuthed = false;
        $sToken = null;

        $credentials = $request->only(['email', 'password']);

        if(isset($request->email) && isset($request->password) && Auth::attempt($credentials)) {

            try {
                // attempt to verify the credentials and create a token for the user
                if (! $token = JWTAuth::attempt($credentials)) {
                    return response()->json(['error' => 'invalid_credentials'], 401);
                }
            } catch (JWTException $e) {
                // something went wrong whilst attempting to encode the token
                return response()->json(['error' => 'could_not_create_token'], 500);
            }
            // all good so return the token
            $bAuthed = true;
            $sToken = compact('token')['token'];
        } else {
            // auth failed, return error
            $sErrorResponse = 'Auth Fail';

            if(!isset($request->email) || !isset($request->password))
            {
                $sErrorResponse = 'missing or wrong credentials';
            }
            return response($sErrorResponse, 401);
        }


        return response()->json([
            'authenticated' => $bAuthed,
            'token' => $sToken
        ]);

    }


    public function getAuthenticatedUser(Request $request)
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        // the token is valid and we have found the user via the sub claim
        return response()->json(compact('user'));

    }

}
