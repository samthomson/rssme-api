<?php

use Illuminate\Database\Seeder;

class PhysicalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // make folders etc

        // public/thumbs
        $sThumbPath = public_path().DIRECTORY_SEPARATOR.'thumbs';
        if(!File::isDirectory($sThumbPath))
        {
            // make it
            File::makeDirectory($sThumbPath,  $mode = 0755, $recursive = false);
        }
    }
}
