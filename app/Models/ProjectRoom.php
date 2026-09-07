<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectRoom extends Model
{
    protected $fillable = [
        'project_id',
        'room_type',
        'description',
        'area_m2',
        'perimeter_m',
        'sort_order',
        'floor_number',
        'lighting_va_calculated',
        'lighting_va_manual',
        'use_manual_lighting',
        'lighting_rule_description',
        'tug_rule_group',
        'tug_qty_calculated',
        'tug_qty_manual',
        'tug_qty_600_manual',
        'tug_qty_100_manual',
        'use_manual_tug_qty',
        'tug_va_calculated',
        'tug_va_manual',
        'use_manual_tug_va',
        'tug_rule_description',
        'total_minimum_va_calculated',
        'total_minimum_va_final',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function inputRows(): HasMany
    {
        return $this->hasMany(ProjectInputRow::class, 'room_id')->orderBy('sort_order');
    }
}
