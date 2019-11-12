<?php

namespace Mpociot\CaptainHook;

use Illuminate\Database\Eloquent\Model;

/**
 * This file is part of CaptainHook arrrrr.
 *
 * @license MIT
 */
class WebhookLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'webhook_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['webhook_id', 'url', 'payload_format', 'payload', 'status', 'response', 'response_format'];

    /**
     * Retrieve the webhook described by the log.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function webhook()
    {
        return $this->belongsTo(Webhook::class);
    }
}
