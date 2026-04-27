<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
                 RolePermissionSeeder::class
            ]);

        //create a test admin user
       $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        $admin->assignRole('admin');

        //create a test author user
       $author = User::factory()->create([
            'name' => 'Author User',
            'email' => 'author@example.com',
        ]);

        $author->assignRole('author');
    }
}
