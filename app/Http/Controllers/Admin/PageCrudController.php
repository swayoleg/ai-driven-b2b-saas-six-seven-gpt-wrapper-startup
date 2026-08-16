<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\PageRequest;
use App\Models\Page;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class PageCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup(): void
    {
        CRUD::setModel(Page::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/page');
        CRUD::setEntityNameStrings('page', 'pages');
    }

    protected function setupListOperation(): void
    {
        CRUD::column('slug');
        CRUD::column('title');
        CRUD::column('template')->type('select_from_array')->options(Page::TEMPLATES);
        CRUD::column('active')->type('boolean');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(PageRequest::class);

        CRUD::field('slug')
            ->hint('Used in the URL, e.g. <code>pricing</code>. The homepage uses <code>home</code>.');
        CRUD::field('template')
            ->type('select_from_array')
            ->options(Page::TEMPLATES)
            ->allows_null(false)
            ->hint('"Default" renders the Content field as-is. "Blog listing" renders the intro fields plus the published Field Notes.');
        CRUD::field('title')
            ->hint('Shown in the browser tab (unless Meta title is set) and as the heading on the blog template.');
        CRUD::field('eyebrow')
            ->hint('Small uppercase label above the heading (blog template).');
        CRUD::field('lead')
            ->type('textarea')
            ->hint('Intro paragraph under the heading (blog template).');
        CRUD::field('content')
            ->type('textarea')
            ->attributes(['rows' => 24, 'style' => 'font-family:monospace;font-size:12px'])
            ->hint('Raw HTML. For the default template this is the whole <code>&lt;main&gt;</code> element. For the blog template it is appended after the post list (e.g. the newsletter card).');
        CRUD::field('meta_title');
        CRUD::field('meta_description')->type('textarea');
        CRUD::field('active')->type('boolean');
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}
