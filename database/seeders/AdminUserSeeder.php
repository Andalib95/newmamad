<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // ایجاد نقش‌های اصلی
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $editorRole = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // ایجاد دسترسی‌های اصلی
        $permissions = [
            'view dashboard',
            'manage users',
            'manage roles',
            'manage articles',
            'manage categories',
            'manage tags',
            'manage comments',
            'manage faqs',
            'manage services',
            'manage team',
            'manage slides',
            'manage videos',
            'manage contacts',
            'manage counters',
            'manage configs',
            'manage redirects',
            'manage menu',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // انتساب تمام دسترسی‌ها به سوپر ادمین
        $superAdminRole->syncPermissions(Permission::all());

        // ایجاد کاربر سوپر ادمین
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin',
                'alias' => 'super-admin',
                'password' => Hash::make('12345678'),
                'index' => true,
                'follow' => true,
                'is_admin' => true,
                'super_admin' => true,
            ]
        );

        $superAdmin->assignRole('super-admin');

        // ایجاد کاربر ادمین معمولی
        $admin = User::firstOrCreate(
            ['email' => 'manager@admin.com'],
            [
                'name' => 'Site Manager',
                'alias' => 'site-manager',
                'password' => Hash::make('12345678'),
                'index' => true,
                'follow' => true,
                'is_admin' => true,
                'super_admin' => false,
            ]
        );

        $admin->assignRole('admin');

        // ایجاد کاربر ویرایشگر
        $editor = User::firstOrCreate(
            ['email' => 'editor@admin.com'],
            [
                'name' => 'Content Editor',
                'alias' => 'content-editor',
                'password' => Hash::make('12345678'),
                'index' => true,
                'follow' => true,
                'is_admin' => false,
                'super_admin' => false,
            ]
        );

        $editor->assignRole('editor');

        $this->command->info('✅ کاربران ادمین با موفقیت ایجاد شدند!');
        $this->command->info('📧 سوپر ادمین: admin@admin.com');
        $this->command->info('🔑 رمز عبور: 12345678');
        $this->command->info('📧 ادمین: manager@admin.com');
        $this->command->info('🔑 رمز عبور: 12345678');
        $this->command->info('📧 ویرایشگر: editor@admin.com');
        $this->command->info('🔑 رمز عبور: 12345678');
    }
}
