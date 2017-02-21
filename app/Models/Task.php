<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class Task extends Model
{
    protected $table = 'tasks';
    public $timestamps = false;

    public static function next()
    {
    	/* return next available item or null */

    	$oTask = Task::where('processFrom', '<', Carbon::now())->first();

    	return $oTask;
    }

}
