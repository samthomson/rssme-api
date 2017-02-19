<?php

namespace App\Http\Controllers;

use App\Models\Feeds\Feed;
use App\Models\Feeds\FeedSubscriber;
use App\Models\Feeds\FeedItem;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Validator;
use Hash;
use Illuminate\Http\Request;
use App\User;
use Auth;


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


    public static function everything()
    {
        $oUser = Auth::user();

        \DB::enableQueryLog();

        $oQuery = DB::table('categories')
            ->join('feed_user', function($join)
            {
                $join->on('categories.id', '=', 'feed_user.category_id')
                    ->where('categories.user_id', '=', $oUser->id);
            })
            ->join('feeditems', function($join)
            {
                $join->on('feed_user.feed_id', '=', 'feeditems.feed_id');

                if(Request::has('feed')){
                    $join->where("feeditems.feed_id", "=", Request::get('feed'));
                }
            })
            ->join('feeds', "feeds.id", "=", "feed_user.feed_id");
        $oQuery->orderBy('feeditems.pubDate', 'desc')
            ->select(['feeditems.url as url', 'feeditems.title as title', 'feeds.url as feedurl', 'feeds.id as feed_id', 'feeditems.pubDate as date', 'feed_user.name as name', 'feeditems.thumb as thumb', 'feeds.thumb as feedthumb', 'feed_user.colour as feed_colour', 'categories.id as cat_id']);




        $iPage = Request::input("page", 1);
        $iPerPage = 20;

        $iTotalItems = 0;

        //$maFeedItems = $oQuery->skip(($iPage * $iPerPage)-$iPerPage)->take($iPerPage)->get();
        $maFeedItems = $oQuery->get();

        #print_r($maFeedItems);die();

        $iTotalItems = count($maFeedItems);
        $iTotalPages = ceil($iTotalItems / $iPerPage);

        $maFeedItems = array_slice($maFeedItems, ($iPage * $iPerPage)-$iPerPage, $iPerPage);

        #print_r(DB::getQueryLog());


        $oaFeedItems = [];

        foreach ($maFeedItems as $oFeedItem) {

            $sDate = '';
            $oDate = new Carbon($oFeedItem->date);
            if($oDate->isToday())
                // 10:41 pm
                $sDate = $oDate->format('g:i a');
            else
                // Aug 12
                $sDate = $oDate->format('M j');

            array_push($oaFeedItems,
                [
                "url" => $oFeedItem->url,
                "title" => $oFeedItem->title,
                "feedurl" => $oFeedItem->feedurl,
                "feed_id" => $oFeedItem->feed_id,
                "category_id" => $oFeedItem->cat_id,
                "date" => $sDate,
                "name" => $oFeedItem->name,
                "thumb" => $oFeedItem->thumb !== '' ? $oFeedItem->thumb : $oFeedItem->feedthumb,
                "feed_thumb" => $oFeedItem->feedthumb
                ]
                );
        }

        if(Request::has('feed')){
            $oQuery->where("feeds.id", "=", Request::get('feed'));
        }

        $oUser = Auth::user();
        $oUser->load('userCategories.userFeeds.feed');

        $oData = [
            'iAvailablePages' => $iTotalPages,
            'iPerPage' => $iPerPage
        ];

        $oaCategoryFeeds = [];

        foreach ($oUser->userCategories as $oCategory) {
            // get feeds in category
            $aoFeeds = [];
            foreach($oCategory->userFeeds as $oUserFeed)
            {
                array_push($aoFeeds, ["id" => $oUserFeed->id, "name" => $oUserFeed->name]);
            }
            // now add category with feeds
            array_push($oaCategoryFeeds, ["category_id" => $oCategory->id, "category_name" => $oCategory->name, "feeds" => $aoFeeds]);
        }


        return response()->json([
            'jsonFeedItems' => $oaFeedItems,
            'jsonCategoryFeeds' => $oaCategoryFeeds,
            'data' => $oData
        ]);
    }

    public function getSubscriptions()
    {
        $oUser = Auth::user()->load('subscriptions');

        return response()->json([
            'subscriptions' => $oUser->subscriptions
        ]);
    }

    public function newFeed(Request $request)
    {
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

        $oSubscriber = new FeedSubscriber;
        $oSubscriber->feed_id = $iFeedId;
        $oSubscriber->user_id = Auth::id();
        $oSubscriber->name = $sFeedName;
        $oSubscriber->save();
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
