<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PostRequest;
use App\Models\Post;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class PostCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup(): void
    {
        CRUD::setModel(Post::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/post');
        CRUD::setEntityNameStrings('field note', 'field notes');
        CRUD::orderBy('published_at', 'desc');
    }

    protected function setupListOperation(): void
    {
        CRUD::column('title');
        CRUD::column('slug');
        CRUD::column('category');
        CRUD::column('published_at')->type('date');
        CRUD::column('active')->type('boolean');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(PostRequest::class);

        CRUD::field('slug')
            ->hint('Used in the URL: <code>/blog/&lt;slug&gt;</code>. Same slug for all languages.');
        CRUD::field('title');
        CRUD::field('category')
            ->hint('The little tag, e.g. "Field study" / "Litigation".');
        CRUD::field('excerpt')
            ->type('textarea')
            ->hint('One-liner shown on the listing card and under the post title.');
        CRUD::field('body')
            ->type('textarea')
            ->attributes(['rows' => 24, 'style' => 'font-family:monospace;font-size:12px'])
            ->hint('Raw HTML of the article body (<code>&lt;p&gt;</code>, <code>&lt;h2&gt;</code>, <code>&lt;blockquote&gt;</code>, <code>&lt;ul&gt;</code>...). Rendered inside the styled "prose" container.');
        CRUD::field('read_minutes')
            ->type('number')
            ->label('Reading time (minutes)');
        CRUD::field('published_at')
            ->type('date')
            ->hint('The newest published note becomes the featured one on the listing. Empty or future date = not shown.');
        CRUD::field('active')->type('boolean');
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}
