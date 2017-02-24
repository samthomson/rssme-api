<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class BlackboxTest extends TestCase
{

    //dd($this->response->getContent());

    private $sAPIPrefix = '/app';

    public function testRegister()
    {
        // register with invalid credentials (not real email), get 422

        $this->json('POST', $this->sAPIPrefix.'/auth/register', [
            'email' => 'notarealemail',
            'password' => 'irrelevant'
            ])->seeJson(
                ['code' => 422]);

        // register with real email, get 200
        $this->json('POST', $this->sAPIPrefix.'/auth/register',
            [
                'email' => 'sam@sam.sam',
                'password' => 'irrelevant'
            ])
            ->seeJson(
                [
                    'code' => 200
                ]);

    }

    public function testLogin()
    {
        // test login with mismatching email and pass, get
        return $this->assertTrue(false);
    }

    public function testGetSubscriptions()
    {
        $sRouteURl = $this->sAPIPrefix.'/subscriptions';
        // shouldn't work if not logged in
        $this->json('GET', $sRouteURl);
        $this->assertResponseStatus(400); // no token

        $this->login();
        $sHeader = parent::getHeaderForTest();


        // should return 200 if logged in and data valid
        $this->json('GET', $sRouteURl, [], $sHeader)
             ->seeJsonStructure(
                 [
                     'subscriptions' => [
                         '*' => [
                             'id', 'feed_id', 'name'
                         ]
                     ]
                 ]
             );
        $this->assertResponseStatus(200);
    }
    public function testGetFeedItems()
    {
        $sRouteURl = $this->sAPIPrefix.'/feeditems';
        // shouldn't work if not logged in
        $this->json('GET', $sRouteURl);
        $this->assertResponseStatus(400); // no token

        $this->login();
        $sHeader = parent::getHeaderForTest();


        // should return 200 if logged in and data valid
        $this->json('GET', $sRouteURl, [], $sHeader)
             ->seeJsonStructure(
                 [
                     'feeditems' => [
                         '*' => [
                             'url', 'title', 'date', 'thumb', 'feedthumb'
                         ]
                     ]
                 ]
             );
        $this->assertResponseStatus(200);
    }


    public function testAddFeed()
    {
        // shouldn't work if not logged in
        $this->json('POST', $this->sAPIPrefix.'/feeds/new');
        $this->assertResponseStatus(400); // no token

        // shouldn't work if logged in but with invalid data
        $this->login();

        $sHeader = parent::getHeaderForTest();

        // missing data
        $this->json(
            'POST',
            $this->sAPIPrefix.'/feeds/new',
            [],
            $sHeader
            )
             ->seeJson([
                 'success' => false,
             ]);

        // bad data - malformed url
        $this->json(
            'POST',
            $this->sAPIPrefix.'/feeds/new',
            [
                'name' => 'digg',
                'url' => 'digg.com/rss/top.rss'
            ],
            $sHeader
            )
            ->seeJson([
                'success' => false,
            ])
            ->seeJsonStructure([
                'success', 'errors'
            ]);



        // should return 200 if logged in and data valid
        /*
        $response = $this->call(
           'POST',
           $this->sAPIPrefix.'/feeds/new',
           [
               'name' => 'digg',
               'url' => 'http://digg.com/rss/top.rss'
           ],
           [],
           [],
           $sHeader
        );
        $this->assertEquals(200, $response->status()); // bad or missing data
        */
        $this->json(
            'POST',
            $this->sAPIPrefix.'/feeds/new',
            [
                'name' => 'digg',
                'url' => 'http://digg.com/rss/top.rss'
            ],
            $sHeader
            )
             ->seeJsonStructure(
                 [
                     'success'
                 ]
             );
        $this->assertResponseStatus(200);


        // feed should be created in db
        $oFeed = \App\Models\Feeds\Feed::with('subscribers')->where('url','http://digg.com/rss/top.rss')->first();
        $this->assertTrue(isset($oFeed));

        $oSubscriber = $oFeed->subscribers[0];

        $this->assertTrue(isset($oSubscriber));
        $this->assertEquals($oSubscriber->name, 'digg');

        // pull task should be in db
        $oTask = \App\Models\Task::where('detail', $oFeed->id)->first();

        $this->assertTrue(isset($oTask));
    }

    public function testDeleteFeed()
    {
        return $this->assertTrue(false);
    }
}
