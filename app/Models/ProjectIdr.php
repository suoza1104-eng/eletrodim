<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectIdr extends Model
{
    protected $table = 'project_idr';

    protected $fillable = [
        'project_id',
        'idr_number',
        'phases',
        'has_neutral',
        'breaker_in_a',
        'short_circuit_current_ka',
        'nominal_current_a',
        'poles',
        'residual_current',
        'icc',
        'idr_status',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
