<?php

namespace App\Models\Feeds;

use Illuminate\Database\Eloquent\Model;

class FeedSubscriber extends Model
{
    protected $table = 'feed_subscriber';
    public $timestamps = false;

    public function feed()
    {
    	return $this->hasOne('App\Models\Feeds\Feed', 'id', 'feed_id');
    	//return $this->belongsTo('App\Feeds\Feed', 'feed_id', 'id');
    }
}
