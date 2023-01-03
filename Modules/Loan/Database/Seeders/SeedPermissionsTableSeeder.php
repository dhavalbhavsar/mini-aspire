<?php

namespace Modules\Loan\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SeedPermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();


        $permissions = config('loan.permissions');

        $permissionNames = array_keys($permissions);

        //Create permissions

        foreach($permissionNames as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        //Attach to the roles
        foreach(config('auth.roles') as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);

            $attachPermissions = [];

            foreach ($permissions as $permissionName => $permissionRoles) {
                if(in_array($roleName, $permissionRoles)) 
                    $attachPermissions[] = $permissionName;
            }

            $role->givePermissionTo($attachPermissions);
        }
    
    }
}
