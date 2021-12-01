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
            ContactsSeeder::class,
            CompaniesSeeder::class
        ]);
        // \App\Models\User::factory(10)->create();
    }
}
