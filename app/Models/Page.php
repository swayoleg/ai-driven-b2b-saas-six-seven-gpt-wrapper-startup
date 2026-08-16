<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Backpack\CRUD\app\Models\Traits\SpatieTranslatable\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use CrudTrait;
    use HasTranslations;

    protected $fillable = [
        'slug',
        'template',
        'title',
        'eyebrow',
        'lead',
        'content',
        'meta_title',
        'meta_description',
        'active',
    ];

    protected $translatable = [
        'title',
        'eyebrow',
        'lead',
        'content',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public const TEMPLATES = [
        'default' => 'Default (raw HTML body)',
        'blog' => 'Blog listing',
    ];
}
