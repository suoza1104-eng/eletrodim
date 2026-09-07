<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectLoad extends Model
{
    protected $fillable = [
        'project_id',
        'room_id',
        'load_type',
        'description',
        'quantity',
        'power_va',
        'unit_va',
        'is_auto',
        'sort_order',
        'phases',
        'voltage_v',
        'installation_method',
        'temperature_c',
        'grouped_circuits',
        'fp',
        'power_w',
        'current_a',
        'grouping_factor',
        'temperature_factor',
        'corrected_current_a',
        'use_manual_va',
        'manual_va',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(ProjectRoom::class, 'room_id');
    }
}
