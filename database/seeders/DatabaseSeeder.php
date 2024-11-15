<?php

namespace Database\Seeders;

use App\Models\User;
use Spatie\Permission\Models\Role;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@faeterj-rio.edu.br',
            'password' => bcrypt('#SenhaD1ficil'),
            'public_key' => ''
        ]);

        $admin->assignRole(Role::findByName('admin'));
    }
}
