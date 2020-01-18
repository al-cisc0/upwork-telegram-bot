<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UpworkJob extends Model
{
    protected $fillable = ['feed_id', 'hash'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function feed()
    {
        return $this->belongsTo(Feed::class);
    }

    /**
     * Filter jobs by given hash
     *
     * @param $query
     * @param string $hash
     * @return mixed
     */
    public function scopeOfHash($query, string $hash)
    {
        return $query->where('hash','=',$hash);
    }
}
