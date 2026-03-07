<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin users
        User::create([
            'employee_id' => null,
            'email' => 'admin@hrm.com',
            'password' => Hash::make('password'),
            'role' => 1, // Admin
            'status' => 1, // Active
            'email_verified_at' => now(),
        ]);

        // Create Manager users
        User::create([
            'employee_id' => null,
            'email' => 'manager@hrm.com',
            'password' => Hash::make('password'),
            'role' => 2, // Manager
            'status' => 1, // Active
            'email_verified_at' => now(),
        ]);

        // Create Employee users
        User::create([
            'employee_id' => null,
            'email' => 'employee@hrm.com',
            'password' => Hash::make('password'),
            'role' => 3, // Employee
            'status' => 1, // Active
            'email_verified_at' => now(),
        ]);
    }
}
