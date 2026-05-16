<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Roles
        $farmer = Role::firstOrCreate(['name' => 'Farmer']);
        $agronomist = Role::firstOrCreate(['name' => 'Agronomist']);
        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $govOfficer = Role::firstOrCreate(['name' => 'Government Officer']);

        // Define Permissions (we can expand this later)
        $permissions = [
            'view dashboard',
            'manage farms',
            'manage devices',
            'write recommendations',
            'view compliance reports',
            'manage system config',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign Permissions to Roles
        $farmer->syncPermissions(['view dashboard', 'manage farms', 'manage devices']);
        $agronomist->syncPermissions(['view dashboard', 'write recommendations']);
        $admin->syncPermissions(Permission::all());
        $govOfficer->syncPermissions(['view compliance reports']);

        // Assign roles to existing dummy users if they exist
        $demoFarmer = User::where('email', 'farmer@agrosense.in')->first();
        if ($demoFarmer) {
            $demoFarmer->assignRole('Farmer');
        }

        $demoAdmin = User::where('email', 'admin@agrosense.in')->first();
        if ($demoAdmin) {
            $demoAdmin->assignRole('Admin');
        }
    }
}
