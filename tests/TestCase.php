<?php

abstract class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    public function setUp()
    {
        $this->refreshApplication();
        parent::setUp();

        Artisan::call('migrate');
        Artisan::call('db:seed');

    }

    public function tearDown()
    {
        Artisan::call('migrate:refresh');
        parent::tearDown();
    }

    public function getHeaderForTest($sEmail = "test@email.com", $sPassword = "password") {

        return [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->login($sEmail, $sPassword)
        ];
    }

    public function login($sEmail = "test@email.com", $sPassword = "password")
    {
        $response = $this->call('POST', '/app/auth/login', ["email" => $sEmail, "password" => $sPassword]);

        return json_decode($response->getContent())->token;
    }
}
