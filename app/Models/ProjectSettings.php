<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectSettings extends Model
{
    protected $fillable = [
        'project_id',
        'default_voltage',
        'default_phases',
        'default_power_factor',
        'default_installation_method',
        'default_temperature_c',
        'default_voltage_drop_percent',
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
