<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Faker\Factory as Faker;
use App\Models\Contact;

class ContactsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $files = Storage::allFiles('/seed_avatars/');

        foreach ($files as $file) {
            $fname = basename($file);
            if ((strpos($fname, 'female_') === false)
                && (strpos($fname, 'male_') === false)
            ) {
                continue;
            }

            $gender = (strpos($fname, 'female') === 0) ? 'female' : 'male';
            $dest = '/public/avatars/' . $fname;

            if (Storage::exists($dest)) {
                Storage::delete($dest);
            }

            if (!Storage::copy('/seed_avatars/' . $fname, $dest)) {
                continue;
            }

            Contact::factory()
                ->hasAddress(1)
                ->hasEmailAddress(1)
                ->hasPhoneNumber(1)
                ->create([
                    'title' => str_replace('.', '', $faker->title($gender)),
                    'firstname' => $faker->firstName($gender),
                    'lastname' => $faker->lastName,
                    'avatar' => $fname
                ]);
        }

        return;
        Contact::factory()
            ->count(20)
            ->hasAddress(1)
            ->hasEmailAddress(1)
            ->hasPhoneNumber(1)
            ->create();
    }
}
