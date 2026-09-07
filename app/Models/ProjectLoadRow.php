<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectLoadRow extends Model
{
    protected $fillable = [
        'project_id',
        'input_row_id',
        'circuit_number',
        'room_description',
        'lighting_va',
        'outlet_100_qty',
        'outlet_600_qty',
        'outlet_1000_qty',
        'tue_va',
        'power_factor',
        'power_w',
        'power_va',
        'phases',
        'voltage',
        'project_current_a',
        'error_message',
    ];

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function inputRow(): BelongsTo
    {
        return $this->belongsTo(ProjectInputRow::class, 'input_row_id');
    }
}
