<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectServiceEntrance extends Model
{
    protected $fillable = [
        'project_id',
        'supply_voltage',
        'phases',
        'total_demand_va',
        'total_demand_w',
        'total_demand_kva',
        'total_demand_kw',
        'power_factor',
        'entry_current_a',
        'entry_conductor_section_mm2',
        'entry_conductor_qty',
        'neutral_conductor_section_mm2',
        'pe_conductor_section_mm2',
        'entry_breaker_a',
        'entry_conduit_diameter_mm',
        'entry_conduit_type',
        'short_circuit_current_ka',
        'notes',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
