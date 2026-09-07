<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectConduit extends Model
{
    protected $fillable = [
        'project_id',
        'conduit_number',
        'circuit_numbers',
        'conductor_section_mm2',
        'conductor_qty',
        'installation_method',
        'conduit_diameter_mm',
        'conduit_type',
        'fill_percent',
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
