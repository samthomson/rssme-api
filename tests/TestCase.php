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
        parent::setUp();

        Artisan::call('migrate');
        Artisan::call('db:seed');

        // test files
        $saImages = [
            'logo' => 'png',
            'branding1' => 'jpg',
            'branding2' => 'jpg',
            'pdf' => 'pdf',
            'pdf-disguised' => 'jpg',
            'corrupt' => 'jpg'
        ];

        foreach ($saImages as $sName => $sExt) {
            \File::copy(
                public_path()."\seed\\".$sName.".".$sExt,
                public_path()."\seed\\".$sName."-test.".$sExt
            );
        }

    }

    public function tearDown()
    {
        /*  */
        //Artisan::call('migrate:refresh');
        //parent::tearDown();
    }
}
