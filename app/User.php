<?php

namespace App;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'telegram_id', 'password', 'is_active', 'is_admin', 'mode', 'is_banned'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Filter users by telegram id
     *
     * @param $query
     * @param string $telegramId
     * @return mixed
     */
    public function scopeOfTelegramId($query, string $telegramId)
    {
        return $query->where('telegram_id','=',$telegramId);
    }

    /**
     * Choose admins only
     *
     * @param $query
     * @return mixed
     */
    public function scopeIsAdmin($query)
    {
        return $query->where('is_admin','=',1);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function feeds()
    {
        return $this->hasMany(Feed::class);
    }
}
