<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectServiceEntrance extends Model
{
    protected $table = 'project_service_entrance';

    protected $fillable = [
        'project_id',
        'installed_load_kw',
        'probable_demand_kva',
        'phases',
        'pole_position',
        'supply_type',
        'supply_range',
        'wires',
        'breaker_a',
        'phase_conductor_mm2',
        'protection_conductor_mm2',
        'pvc_conduit_mm',
        'steel_conduit_mm',
        'grounding_conductor',
        'grounding_electrodes',
        'concrete_pole_type',
        'steel_pole_type',
        'pontalete_type',
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
