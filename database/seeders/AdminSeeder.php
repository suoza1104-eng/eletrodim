<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@eletrodim.com.br'],
            [
                'name'     => 'Administrador',
                'role'     => 'admin',
                'password' => bcrypt('Admin@2026'),
                'status'   => 'active',
                'phone'    => null,
            ]
        );
    }
}
