<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A crypto address shown on the support page. Not translatable: names,
 * network tags and addresses are technical strings, identical in every locale.
 */
class Wallet extends Model
{
    use CrudTrait;

    protected $fillable = [
        'name',
        'network',
        'address',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActiveOrdered(Builder $query): Builder
    {
        return $query->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
