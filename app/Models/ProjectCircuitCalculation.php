<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectCircuitCalculation extends Model
{
    protected $fillable = [
        'project_id',
        'circuit_number',
        'room_description',
        'phases',
        'voltage',
        'power_va',
        'power_w',
        'power_factor',
        'project_current_a',
        'calculated_demand_factor',
        'use_manual_demand_factor',
        'manual_demand_factor',
        'calculated_demand_va',
        'use_manual_demand_va',
        'manual_demand_va',
        'demand_current_a',
        'installation_method',
        'temperature_c',
        'grouped_circuits',
        'correction_factor',
        'corrected_current_a',
        'calculated_final_conductor',
        'use_manual_final_conductor',
        'manual_final_conductor',
        'conductor_capacity_a',
        'distance_m',
        'voltage_drop_percent',
        'voltage_drop_v',
        'calculated_breaker',
        'use_manual_breaker',
        'manual_breaker',
        'short_circuit_current_ka',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'use_manual_demand_factor'   => 'boolean',
            'use_manual_demand_va'       => 'boolean',
            'use_manual_final_conductor' => 'boolean',
            'use_manual_breaker'         => 'boolean',
        ];
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // -----------------------------------------------------------------------
    // Helper methods
    // -----------------------------------------------------------------------

    public function getEffectiveDemandFactor(): float|null
    {
        if ($this->use_manual_demand_factor) {
            return $this->manual_demand_factor;
        }

        return $this->calculated_demand_factor;
    }

    public function getEffectiveDemandVa(): float|null
    {
        if ($this->use_manual_demand_va) {
            return $this->manual_demand_va;
        }

        return $this->calculated_demand_va;
    }

    public function getEffectiveFinalConductor(): string|null
    {
        if ($this->use_manual_final_conductor) {
            return $this->manual_final_conductor;
        }

        return $this->calculated_final_conductor;
    }

    public function getEffectiveBreaker(): int|float|null
    {
        if ($this->use_manual_breaker) {
            return $this->manual_breaker;
        }

        return $this->calculated_breaker;
    }
}
