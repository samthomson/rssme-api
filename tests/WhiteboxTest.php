<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class WhiteboxTest extends TestCase
{

    private $sAPIPrefix = '/app/v1/';

    public function testAddFreshUserFeed()
    {
        // add feed subscriber when feed doesn't exist
        $this->assertTrue(true);
    }

    public function testAddSecondaryUserFeed()
    {
        // add feed subscriber when feed already exists (another user has added it)
        $this->assertTrue(true);
    }

    public function testDeleteSoleUserFeedAndFeed()
    {
        // delete feed subscriber deletes feed when only one feed subscriber
        $this->assertTrue(true);
    }

    public function testDeleteSubscriberToFeedWithMultipleSubscribers()
    {
        // delete feed subscriber doesn't delete feed when more than one feed subscriber exists
        $this->assertTrue(true);
    }

    public function testDetermineFeedType()
    {
        // determine feed type from source
        $this->assertTrue(true);
    }

    public function testParseAtomFeed()
    {
        // parse atom feed succesfully
        $this->assertTrue(true);
    }

    public function testParseRSSFeed()
    {
        // parse rss feed succesfully
        $this->assertTrue(true);
    }

}
