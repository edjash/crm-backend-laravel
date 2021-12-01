<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ContactsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Contact::factory()->count(100)->hasAddress(1)->create();
    }
}
