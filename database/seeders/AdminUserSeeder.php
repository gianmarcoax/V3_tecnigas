<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@tecnigas.com'],
            [
                'name'               => 'Admin Tecnigas',
                'password'           => bcrypt('tecnigas2026'),
                'email_verified_at'  => now(),
            ]
        );

        $this->command->info('✅ Usuario admin@tecnigas.com creado/actualizado correctamente.');
    }
}
