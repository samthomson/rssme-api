<?php

use Illuminate\Database\Seeder;

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
        DB::table('users')->insert([
            'email' => $sSeedEmail,
            'password' => Hash::make('password')
        ]);
    }
}
