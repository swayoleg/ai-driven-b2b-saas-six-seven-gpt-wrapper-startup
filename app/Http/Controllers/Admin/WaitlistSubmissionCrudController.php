<?php

namespace App\Http\Controllers\Admin;

use App\Models\WaitlistSubmission;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * An inbox, not an editor: entries arrive from the five-step wizard on the
 * contact page, so there is nothing to create or edit here.
 */
class WaitlistSubmissionCrudController extends CrudController
{
    use DeleteOperation;
    use ListOperation;
    use ShowOperation;

    public function setup(): void
    {
        CRUD::setModel(WaitlistSubmission::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/waitlist-submission');
        CRUD::setEntityNameStrings('waitlist entry', 'waitlist entries');
        CRUD::orderBy('created_at', 'desc');
    }

    protected function setupListOperation(): void
    {
        CRUD::column('email');
        CRUD::column('company');
        CRUD::column('size')->label('Headcount');
        CRUD::column('urgency');
        CRUD::column('position')->type('number');
        CRUD::column('created_at')->type('datetime')->label('Received');
    }

    protected function setupShowOperation(): void
    {
        CRUD::column('email');
        CRUD::column('company');
        CRUD::column('size')->label('Headcount');
        CRUD::column('maturity');
        CRUD::column('urgency');
        CRUD::column('pain')->label('Desired outcome');
        CRUD::column('budget');
        CRUD::column('position')->type('number');
        CRUD::column('locale');
        CRUD::column('ip')->label('IP');
        CRUD::column('user_agent')->label('User agent');
        CRUD::column('created_at')->type('datetime')->label('Received');
    }
}
