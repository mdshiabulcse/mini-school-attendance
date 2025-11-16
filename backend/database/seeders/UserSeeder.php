<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'System Administrator',
            'email' => 'admin@school.com',
            'phone' => '+1234567890',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create Teacher Users
        $teachers = [
            [
                'name' => 'John Mathematics',
                'email' => 'john.math@school.com',
                'phone' => '+1234567891',
                'password' => Hash::make('teacher123'),
                'role' => 'teacher',
            ],
            [
                'name' => 'Sarah Science',
                'email' => 'sarah.science@school.com',
                'phone' => '+1234567892',
                'password' => Hash::make('teacher123'),
                'role' => 'teacher',
            ],
            [
                'name' => 'Mike English',
                'email' => 'mike.english@school.com',
                'phone' => '+1234567893',
                'password' => Hash::make('teacher123'),
                'role' => 'teacher',
            ]
        ];

        foreach ($teachers as $teacher) {
            User::create($teacher);
        }

        // Create Parent Users
        $parents = [
            [
                'name' => 'Robert Johnson',
                'email' => 'robert@parent.com',
                'phone' => '+1234567894',
                'password' => Hash::make('parent123'),
                'role' => 'parent',
            ],
            [
                'name' => 'Lisa Williams',
                'email' => 'lisa@parent.com',
                'phone' => '+1234567895',
                'password' => Hash::make('parent123'),
                'role' => 'parent',
            ]
        ];

        foreach ($parents as $parent) {
            User::create($parent);
        }

        $this->command->info('Users created successfully!');
        $this->command->info('Admin: admin@school.com / admin123');
        $this->command->info('Teacher: john.math@school.com / teacher123');
        $this->command->info('Parent: robert@parent.com / parent123');
    }
}
