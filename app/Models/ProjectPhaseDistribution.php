<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPhaseDistribution extends Model
{
    protected $fillable = [
        'project_id',
        'circuit_number',
        'room_description',
        'assigned_phase',
        'demand_va',
        'demand_w',
        'demand_current_a',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
