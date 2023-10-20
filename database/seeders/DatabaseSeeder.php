<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\{Project, Tag, Release, User};

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
        User::factory()
            ->has(
                Project::factory(2)
                    ->has(
                        Release::factory(10)
                            ->has(
                                Tag::factory(3)
                                    ->state(function (array $attributes, Release $release) {
                                        return ['project_id' => $release->project_id];
                                    })
                            )
                    )
            )
            ->create(compact('email', 'password'));

        dump("Admin account created succesfully!
Email: $email
Password: $password");
    }
}
