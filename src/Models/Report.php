<?php

namespace MBLSolutions\Report\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes;

    /** {@inheritDoc} */
    protected $guarded = [
        'id',
        'fields',
        'selects',
        'joins'
    ];

    protected $casts = [
        'display_limit' => 'integer',
        'show_data' => 'boolean',
        'show_totals' => 'boolean',
        'active' => 'boolean'
    ];

    /**
     * Get the fields that belong to the report
     *
     * @return HasMany
     */
    public function fields(): HasMany
    {
        return $this->hasMany(ReportField::class);
    }

    /**
     * Get the selects that belong to the report
     *
     * @return HasMany
     */
    public function selects(): HasMany
    {
        return $this->hasMany(ReportSelect::class)->orderBy('column_order');
    }

    /**
     * Get the joins that belong to the report
     *
     * @return HasMany
     */
    public function joins(): HasMany
    {
        return $this->hasMany(ReportJoin::class);
    }

    /**
     * Should Data be shown on Report
     *
     * @return bool
     */
    public function shouldShowData(): bool
    {
        return $this->show_data;
    }

    /**
     * Should Totals be shown on Report
     *
     * @return bool
     */
    public function shouldShowTotals(): bool
    {
        return $this->show_totals;
    }

}