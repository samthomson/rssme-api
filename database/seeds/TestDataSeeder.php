<?php

use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Feeds\Feed;
use App\Models\Feeds\UserFeed;
use App\Models\Feeds\FeedItem;
use App\Models\Feeds\FeedSubscriber;

use App\Http\Controllers\Feeds;

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


        $oFeed = new Feed;
        $oFeed->url = 'http://samt.testfeed.xml';
        $oFeed->save();

        $iFeedId = $oFeed->id;

        $oSubscriber = new FeedSubscriber;
        $oSubscriber->feed_id = $iFeedId;
        $oSubscriber->user_id = $oUser->id;
        $oSubscriber->name = 'sam pretend feed';
        $oSubscriber->save();

    }
}
