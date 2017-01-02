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
        return $this->assertTrue(true);
    }

    public function testGetFeeds()
    {
        return $this->assertTrue(true);
    }

    public function testAddFeed()
    {
        return $this->assertTrue(true);
    }

    public function testDeleteFeed()
    {
        return $this->assertTrue(true);
    }
}
