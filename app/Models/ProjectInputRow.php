<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProjectInputRow extends Model
{
    protected $fillable = [
        'project_id',
        'room_id',
        'circuit_number',
        'room_type',
        'description',
        'load_type',
        'specific_power_va',
        'quantity',
        'is_below_minimum',
        'minimum_va',
        'tue_description',
        'area_m2',
        'perimeter_m',
        'power_factor',
        'phases',
        'voltage',
        'installation_method',
        'grouped_circuits',
        'temperature_c',
        'distance_m',
        'voltage_drop_percent',
        'sort_order',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(ProjectRoom::class, 'room_id');
    }

    public function loadRow(): HasOne
    {
        return $this->hasOne(ProjectLoadRow::class, 'input_row_id');
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeByCircuit(Builder $query, int|string $circuitNumber): Builder
    {
        return $query->where('circuit_number', $circuitNumber);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
