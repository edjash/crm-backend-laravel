<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            CountriesSeeder::class,
            CompaniesSeeder::class,
            ContactsSeeder::class,
            UsersSeeder::class
            //
        ]);
        // \App\Models\User::factory(10)->create();
    }
}
