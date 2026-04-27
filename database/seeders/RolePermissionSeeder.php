<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        //create permissions
        $permissions = [
            'create posts',
            'edit own posts',
            'edit all posts',
            'delete own posts',
            'delete all posts',
            'publish posts',
            'manage users',
            'manage roles'
        ];

        foreach($permissions as $permission){
            Permission::create(['name' => $permission]);
        }

        // create roles and assign permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $editorRole = Role::create(['name' => 'editor']);
        $editorRole->givePermissionTo([
            'create posts',
            'edit all posts',
            'delete all posts',
            'publish posts',
        ]);

        $authorRole = Role::create(['name' => 'author']);
        $authorRole->givePermissionTo([
            'create posts',
            'edit own posts',
            'delete own posts',
        ]);

        $subscriberRole = Role::create(['name' => 'subscriber']);
        // subscribers have no roles. They can just read posts.
    }

}
