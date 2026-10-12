<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserStatus;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            [
                'username' => 'superadmin',
            ],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => 'mans123456',
                'status' => UserStatus::ACTIVE,
            ]
        );

        $user->syncRoles(['super-admin']);

        $user = User::updateOrCreate(
            [
                'username' => 'recep',
            ],
            [
                'name' => 'Receptionist',
                'username' => 'recep',
                'password' => 'mans123456',
                'status' => UserStatus::ACTIVE,
            ]
        );

        $user->syncRoles(['receptionist']);
    }
}
