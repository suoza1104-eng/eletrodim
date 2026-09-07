<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPhaseDistribution extends Model
{
    protected $table = 'project_phase_distribution';

    protected $fillable = [
        'project_id',
        'circuit_number',
        'use_phase_r',
        'use_phase_s',
        'use_phase_t',
        'load_r_va',
        'load_s_va',
        'load_t_va',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
