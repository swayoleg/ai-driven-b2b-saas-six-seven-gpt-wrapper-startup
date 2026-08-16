<?php

namespace Database\Seeders;

use App\Models\Wallet;
use Illuminate\Database\Seeder;

/**
 * Seeds the support page's donation addresses from config/wallets.php, which
 * reads them out of the environment — deliberately not from this file, so the
 * repository never carries a real address.
 *
 * Idempotent: matched on name+network, so re-running after changing an address
 * in .env updates the existing row rather than adding a second one.
 */
class WalletSeeder extends Seeder
{
    public function run(): void
    {
        $seeded = 0;
        $skipped = [];

        foreach (config('wallets.seed', []) as $i => $wallet) {
            if (blank($wallet['address'] ?? null)) {
                $skipped[] = $wallet['name'].' ('.$wallet['network'].')';

                continue;
            }

            Wallet::updateOrCreate(
                ['name' => $wallet['name'], 'network' => $wallet['network']],
                [
                    'address' => $wallet['address'],
                    'sort_order' => ($i + 1) * 10,
                    'active' => true,
                ]
            );

            $seeded++;
        }

        $this->command?->info("Wallets seeded: {$seeded}");

        if ($skipped !== []) {
            $this->command?->warn('No address in .env, skipped: '.implode(', ', $skipped));
        }
    }
}
