<?php

use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Feeds\Feed;
use App\Models\Feeds\UserFeed;
use App\Models\Feeds\FeedItem;
use App\Models\Feeds\FeedSubscriber;

use App\Http\Controllers\Feeds;

use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // test user
        $sSeedEmail = 'test@email.com';

        $oUser = new User;
        $oUser->email = $sSeedEmail;
        $oUser->password = Hash::make('password');
        $oUser->save();


        // add a feed
        $oFeed = new Feed;
        $oFeed->url = 'http://samt.testfeed.xml';
        $oFeed->save();

        $iFeedId = $oFeed->id;

        // subscribe user to that feed
        $oSubscriber = new FeedSubscriber;
        $oSubscriber->feed_id = $iFeedId;
        $oSubscriber->user_id = $oUser->id;
        $oSubscriber->name = 'sam pretend feed';
        $oSubscriber->save();

        // make some items for that feed
        for($iItem = 0; $iItem < 5; $iItem++)
        {
            // 'url', 'title', 'date', 'thumb', 'feedthumb'
            $oFeedItem = new FeedItem;
            $oFeedItem->feed_id = $iFeedId;
            $oFeedItem->url = $oFeed->url."/item/".$iItem;
            $oFeedItem->title = "test item ".$iItem;
            $oFeedItem->guid = $iItem."_".uniqid();
            $oFeedItem->pubDate = Carbon::now()->addDay(-1 * $iItem);
            $oFeedItem->thumb = $oFeed->url."/item/".$iItem."thumb.jpg";
            //$oFeedItem->feedthumb = $oFeed->url."/item/".$iItem."feedthumb.jpg";
            $oFeedItem->save();
        }

    }
}
