<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDps extends Model
{
    protected $fillable = [
        'project_id',
        'dps_number',
        'location_type',
        'phase_neutral_voltage',
        'short_circuit_current_ka',
        'dps_class',
        'min_up',
        'uc_v',
        'in_value',
        'iimp',
        'icc',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
