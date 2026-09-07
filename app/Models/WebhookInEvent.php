<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookInEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'source',
        'event_type',
        'payload_json',
        'received_at',
        'process_status',
        'processed_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'received_at'    => 'datetime',
            'processed_at'   => 'datetime',
            'process_status' => 'string',
        ];
    }
}
