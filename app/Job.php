<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    /**
     * Filter by queue
     *
     * @param $query
     * @param string $queue
     * @return mixed
     */
    public function scopeOfQueue($query, string $queue)
    {
        return $query->where('queue','=',$queue);
    }
}
