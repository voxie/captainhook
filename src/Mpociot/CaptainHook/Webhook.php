<?php

namespace Mpociot\CaptainHook;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

/**
 * This file is part of CaptainHook arrrrr.
 *
 * @property integer id
 * @property integer tenant_id
 * @property string  event
 * @property string  url
 * @license MIT
 */
class Webhook extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'webhooks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['id', 'url', 'event', 'tenant_id'];

    /**
     * Cache key to use to store loaded webhooks.
     *
     * @var string
     */
    const CACHE_KEY = 'mpociot.captainhook.hooks';

    /**
     * Register 'created', 'updated', and 'deleted' event handlers
     * that will take care of clearing the cache whenever a webhook
     * model instance is changed.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($results) {
            Cache::forget(self::CACHE_KEY);
        });

        static::updated(function ($results) {
            Cache::forget(self::CACHE_KEY);
        });

        static::deleted(function ($results) {
            Cache::forget(self::CACHE_KEY);
        });
    }

    /**
     * Retrieve the logs for a given hook.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany(WebhookLog::class);
    }

    /**
     * Retrieve the logs for a given hook.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lastLog()
    {
        return $this->hasOne(WebhookLog::class)
            ->orderBy('created_at', 'DESC');
    }
}
