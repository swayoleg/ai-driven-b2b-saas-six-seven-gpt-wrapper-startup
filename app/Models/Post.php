<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Backpack\CRUD\app\Models\Traits\SpatieTranslatable\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use CrudTrait;
    use HasTranslations;

    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'body',
        'category',
        'read_minutes',
        'published_at',
        'active',
    ];

    protected $translatable = [
        'title',
        'excerpt',
        'body',
        'category',
    ];

    protected $casts = [
        'active' => 'boolean',
        'published_at' => 'date',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /** The post that follows this one chronologically, wrapping around to the newest. */
    public function next(): ?Post
    {
        return static::published()
            ->where('id', '!=', $this->id)
            ->where('published_at', '<', $this->published_at)
            ->orderByDesc('published_at')
            ->first()
            ?? static::published()->where('id', '!=', $this->id)->orderByDesc('published_at')->first();
    }
}
