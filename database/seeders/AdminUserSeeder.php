<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Creates (or resets the password of) the admin panel login.
 *
 * Driven entirely by env so it is safe to run on a deploy target: set
 * ADMIN_EMAIL / ADMIN_PASSWORD there rather than editing this file. Matched on
 * email, so re-running rotates the password instead of adding a second user.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@aib2b.local');
        $password = env('ADMIN_PASSWORD', 'Sixseven!67');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Admin'),
                'password' => Hash::make($password),
            ]
        );

        $this->command?->info("Admin ready: {$email}");

        if ($password === 'Sixseven!67') {
            $this->command?->warn('Using the default password. Set ADMIN_PASSWORD in .env before exposing this.');
        }
    }
}
