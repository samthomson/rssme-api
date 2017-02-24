<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\library\Helper;

class WhiteboxTest extends TestCase
{

    private $sAPIPrefix = '/app/v1/';

    public function testAddFreshUserFeed()
    {
        // add feed subscriber when feed doesn't exist
        $this->assertTrue(false);
    }

    public function testAddSecondaryUserFeed()
    {
        // add feed subscriber when feed already exists (another user has added it)
        $this->assertTrue(false);
    }

    public function testDeleteSoleUserFeedAndFeed()
    {
        // delete feed subscriber deletes feed when only one feed subscriber
        $this->assertTrue(false);
    }

    public function testDeleteSubscriberToFeedWithMultipleSubscribers()
    {
        // delete feed subscriber doesn't delete feed when more than one feed subscriber exists
        $this->assertTrue(false);
    }

    public function testDetermineFeedType()
    {
        // determine feed type from source
        $this->assertTrue(false);
    }

    public function testParseAtomFeed()
    {
        // parse atom feed succesfully
        $this->assertTrue(false);
    }

    public function testParseRSSFeed()
    {
        // parse rss feed succesfully
        $this->assertTrue(false);
    }

    public function testHandleMalformedFeed()
    {
        // parse rss feed succesfully

        $asBadFeeds = [
            base_path('tests/resources/malformed-hackerparadise.xml')
        ];

        $sPath = base_path('tests/resources/malformed-hackerparadise.xml');
        foreach($asBadFeeds as $sPath)
        {
            $sFileContents = file_get_contents($sPath);
            $oPossibleFeed = Helper::oXMLStringToFeedObject($sFileContents);
            $this->assertTrue(is_null($oPossibleFeed));
        }
    }

}
