<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompaniesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Company::factory()
            ->count(20)
            ->hasAddress(1)
            ->hasEmailAddress(1)
            ->hasPhoneNumber(1)
            ->create();
    }
}
