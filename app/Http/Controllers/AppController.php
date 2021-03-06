<?php

namespace App\Http\Controllers;

use App\Models\Feeds\Feed;
use App\Models\Feeds\FeedSubscriber;
use App\Models\Feeds\FeedItem;
use App\Models\Task;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use Validator;
use Hash;
use Illuminate\Http\Request;
use App\User;
use Auth;
use DB;

use Carbon\Carbon;


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

        return response()->json(
            [
                'subscriptions' => $oUser->subscriptions
            ]
        );
    }

    public function getFeedItems(Request $request)
    {
        $oUser = Auth::user()->load('subscriptions');

        $oQuery = DB::table('feeditems', function($join)
            {
                $join->on('feed_subscriber.feed_id', '=', 'feeditems.feed_id')
                    ;

                if($request->has('feed')){
                    $join->where("feeditems.feed_id", "=", Request::get('feed'));
                }
            })
            ->join('feed_subscriber', "feeditems.feed_id", "=", "feed_subscriber.feed_id")
            ->join('feeds', "feeds.id", "=", "feed_subscriber.feed_id")
            ->where('feed_subscriber.user_id', '=', $oUser->id);

            /*
            if($request->has('cursor')){
                $oQuery->where("feeditems.id", "<", $request->input('cursor'));
            }
            */

            if($request->has('feed')){
                $oQuery->where("feeds.id", "=", $request->input('feed'));
            }


        $oQuery->orderBy('feeditems.pubDate', 'desc')
            ->select(['feeditems.url as url', 'feeditems.title as title', 'feeds.url as feedurl', 'feeds.id as feed_id', 'feeditems.pubDate as date', 'feed_subscriber.name as name', 'feeditems.thumb as thumb', 'feeds.thumb as feedthumb', 'feeditems.id as id']);


        $iPage = $request->input("cursor", 1);
        $iPerPage = 20;

        $iTotalItems = 0;

        //$maFeedItems = $oQuery->skip(($iPage * $iPerPage)-$iPerPage)->take($iPerPage)->get();
        $maFeedItems = $oQuery->get()->all();

        #print_r($maFeedItems);die();

        $iTotalItems = count($maFeedItems);
        $iTotalPages = ceil($iTotalItems / $iPerPage);

        $maFeedItems = array_slice($maFeedItems, ($iPage * $iPerPage)-$iPerPage, $iPerPage);


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
                "date" => $sDate,
                "name" => $oFeedItem->name,
                "thumb" => $oFeedItem->thumb !== '' ? $oFeedItem->thumb : $oFeedItem->feedthumb,
                "feed_thumb" => $oFeedItem->feedthumb,
                "item_id" => $oFeedItem->id
                ]
            );
        }

        return response()->json(
            [
                'feeditems' => $maFeedItems
            ]
        );


    }

    public function newFeed(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'url' => 'required|url',
            'name' => 'required'
        ]);

        if (!$validator->fails())
        {
            Feeds::createUniqueUserFeed(
                $request->input('url'),
                $request->input('name'),
                true
            );

            return response()->json(['success' => true]);
        }else{
            return response()->json(
                [
                    'success' => false,
                    'errors' => $validator->errors()->all()
                ]
            );
        }
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
