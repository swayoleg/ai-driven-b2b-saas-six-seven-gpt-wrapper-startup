<?php

namespace App\Http\Controllers\Admin;

use App\Models\Subscriber;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * An inbox, not an editor: subscribers arrive from the newsletter form on the
 * blog page, so there is nothing to create or edit here.
 */
class SubscriberCrudController extends CrudController
{
    use DeleteOperation;
    use ListOperation;
    use ShowOperation;

    public function setup(): void
    {
        CRUD::setModel(Subscriber::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/subscriber');
        CRUD::setEntityNameStrings('subscriber', 'subscribers');
        CRUD::orderBy('created_at', 'desc');
    }

    protected function setupListOperation(): void
    {
        CRUD::column('email');
        CRUD::column('locale');
        CRUD::column('ip')->label('IP');
        CRUD::column('created_at')->type('datetime')->label('Subscribed');
    }

    protected function setupShowOperation(): void
    {
        $this->setupListOperation();

        CRUD::column('user_agent')->label('User agent');
        CRUD::column('updated_at')->type('datetime')->label('Last seen');
    }
}
