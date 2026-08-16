<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\WalletRequest;
use App\Models\Wallet;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

class WalletCrudController extends CrudController
{
    use CreateOperation;
    use DeleteOperation;
    use ListOperation;
    use ShowOperation;
    use UpdateOperation;

    public function setup(): void
    {
        CRUD::setModel(Wallet::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/wallet');
        CRUD::setEntityNameStrings('wallet', 'wallets');
        CRUD::orderBy('sort_order')->orderBy('id');
    }

    protected function setupListOperation(): void
    {
        CRUD::column('sort_order')->label('Order');
        CRUD::column('name');
        CRUD::column('network');
        CRUD::column('address');
        CRUD::column('active')->type('boolean');
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(WalletRequest::class);

        CRUD::field('name')
            ->hint('Coin name shown in bold, e.g. "Bitcoin" / "USDT".');
        CRUD::field('network')
            ->hint('The little outlined tag, e.g. "BTC" / "ETH · ERC-20" / "TRC-20".');
        CRUD::field('address')
            ->hint('The receiving address. Copied verbatim by the Copy button — no formatting is applied.');
        CRUD::field('sort_order')
            ->type('number')
            ->label('Order')
            ->default(0)
            ->hint('Low numbers first. Seeded wallets are spaced by 10 so rows can be slotted in between.');
        CRUD::field('active')->type('boolean');
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }
}
