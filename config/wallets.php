<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Donation wallets
    |--------------------------------------------------------------------------
    |
    | Addresses for the support page. They live in the environment rather than
    | in WalletSeeder so real addresses never reach the public repository.
    |
    | Only the initial seed reads this — once seeded, wallets are ordinary rows
    | edited in the admin panel. WalletSeeder skips any entry whose address is
    | unset, so a clone without these variables simply gets no wallets instead
    | of a page full of blanks.
    |
    | The labels are not secret and stay here; only the addresses come from env.
    |
    */

    'seed' => [
        ['name' => 'Bitcoin', 'network' => 'BTC', 'address' => env('WALLET_BTC')],
        ['name' => 'Ethereum', 'network' => 'ETH · ERC-20', 'address' => env('WALLET_ETH_ERC20')],
        ['name' => 'USDC', 'network' => 'BASE', 'address' => env('WALLET_USDC_BASE')],
        ['name' => 'USDC', 'network' => 'SOL', 'address' => env('WALLET_USDC_SOL')],
        ['name' => 'TRON', 'network' => 'TRX · TRC-20', 'address' => env('WALLET_TRX_TRC20')],
        ['name' => 'USDT', 'network' => 'TRC-20', 'address' => env('WALLET_USDT_TRC20')],
        ['name' => 'USDT', 'network' => 'TON', 'address' => env('WALLET_USDT_TON')],
        ['name' => 'USDT', 'network' => 'SOL', 'address' => env('WALLET_USDT_SOL')],
        ['name' => 'GRAM', 'network' => 'TON', 'address' => env('WALLET_GRAM_TON')],
    ],

];
