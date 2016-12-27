<?php

namespace App\Http\Controllers;

use App\Models\Feeds\Feed;
use App\Models\Feeds\UserFeed;
use App\Models\Feeds\FeedItem;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Validator;
use Hash;
use Illuminate\Http\Request;
use App\User;


class AppController extends Controller
{
    public function register(Request $request) {

        $validator = Validator::make($request->all(), [
            'email' => 'required|unique:users|email',
            'password' => 'required',
        ]);


        if ($validator->fails()) {
            return response()->json([
                "errors" => $validator->errors()
            ], 422);
        }

        // stil here? Validation was ok..
        $oUser = new User;

        $oUser->email = $request->get('email');
        $oUser->password = Hash::make($request->get('password'));

        $oUser->save();

        // check email
        return response()->json([
            'Ok'
        ]);

    }

    public function authenticate(Request $request)
    {
        // grab credentials from the request
        $credentials = $request->only('email', 'password');

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

        $bAuthStatus = false;
        $mToken = null;

        if(Auth::attempt([
            'email' => $request->get('email'), 'password' => $request->get('password')
        ])) {
            $bAuthStatus = true;
            $mToken = compact('token')['token'];
        }


        return response()->json([
            'authStatus' => $bAuthStatus,
            'token' => $mToken
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

    public function everything() {
        return response()->json([]);
    }

    public function newFeed(Request $request) {

        if ($request->has('url') && $request->has('name'))
        {
            self::createUniqueUserFeed(
                $request->input('url'),
                $request->input('name'),
                false
            );

            return response("ok", 200);
        }else{
            return response("missing data", 200);
        }
    }

    private static function createUniqueUserFeed($sFeedUrl, $sFeedName, $bScheduleImmediatePull = true)
    {
        // creates a feed url, if there's no feed for current user for url, create it

        // get id of a feed (new or existing)
        $oFeed = Feed::where("url", $sFeedUrl)->first();

        $iFeedId = -1;

        if(!isset($oFeed)){
            $oFeed = new Feed;

            $oFeed->url = $sFeedUrl;

            $oFeed->save();
            $iFeedId = $oFeed->id;

            // pull it
            if($bScheduleImmediatePull){
                // make sure it's in line to be crawled, unless we're calling this from a test stub
                self::scheduleFeedPull($iFeedId);
            }
        }else{
            $iFeedId = $oFeed->id;
        }

        $oUserFeed = new UserFeed;
        $oUserFeed->feed_id = $iFeedId;
        $oUserFeed->user_id = Auth::id();
        $oUserFeed->name = $sFeedName;
        //$oUserFeed->colour = Helper::sRandomUserFeedColour();
        $oUserFeed->save();
    }
    public static function scheduleFeedPull($iFeedId, $iMinutes = 0)
    {
        $oTask = new Task;
        $oTask->processFrom = Carbon::now()->addMinutes($iMinutes);
        $oTask->job = "pull-feed";
        $oTask->detail = $iFeedId;
        $oTask->save();
    }
}
