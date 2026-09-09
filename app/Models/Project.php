<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Project extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'client_name',
        'client_phone',
        'client_email',
        'address',
        'city',
        'state',
        'observations',
        'floor_plan_json',
        'input_mode',
        'floors_count',
        'status',
        'progress_step',
        'progress_percent',
        'share_token',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status'           => 'string',
            'progress_percent' => 'decimal:2',
            'completed_at'     => 'datetime',
        ];
    }

    // -----------------------------------------------------------------------
    // Relationships
    // -----------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(ProjectSettings::class);
    }

    public function inputRows(): HasMany
    {
        return $this->hasMany(ProjectInputRow::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(ProjectRoom::class)->orderBy('sort_order');
    }

    public function loadRows(): HasMany
    {
        return $this->hasMany(ProjectLoadRow::class);
    }

    public function circuitCalculations(): HasMany
    {
        return $this->hasMany(ProjectCircuitCalculation::class);
    }

    public function phaseDistributions(): HasMany
    {
        return $this->hasMany(ProjectPhaseDistribution::class);
    }

    public function conduits(): HasMany
    {
        return $this->hasMany(ProjectConduit::class);
    }

    public function dps(): HasMany
    {
        return $this->hasMany(ProjectDps::class);
    }

    public function idrs(): HasMany
    {
        return $this->hasMany(ProjectIdr::class);
    }

    public function serviceEntrance(): HasOne
    {
        return $this->hasOne(ProjectServiceEntrance::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ProjectReport::class);
    }

    // -----------------------------------------------------------------------
    // Helper methods
    // -----------------------------------------------------------------------

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function generateShareToken(): string
    {
        $this->share_token = (string) Str::uuid();
        $this->save();

        return $this->share_token;
    }
}
