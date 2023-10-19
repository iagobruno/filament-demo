<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        $email = 'admin@admin.com';
        $password = '12345678';
        \App\Models\User::factory()->create(
            compact('email', 'password')
        );
        dump("Admin account created succesfully!
Email: $email
Password: $password");
    }
}
