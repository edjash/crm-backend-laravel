<?php

namespace Database\Seeders;

use App\Models\Contact;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

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
            $title = str_replace('.', '', $faker->title($gender));
            $firstName = $faker->firstName($gender);
            while ($lastName = $faker->lastName()) {
                if ($lastName != 'Morissette') {
                    break;
                }
            }

            if (strpos($fname, 'female_3') === 0) {
                $title = 'Miss';
                $firstName = 'Misty';
                $lastName = 'S.';
            }
            if (strpos($fname, 'female_4') === 0) {
                $title = 'Miss';
                $firstName = 'Sasha';
                $lastName = 'S.';
            }

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
                    'title' => $title,
                    'firstname' => $firstName,
                    'lastname' => $lastName,
                    'avatar' => $fname,
                ]);
        }

        Contact::factory()
            ->count(20)
            ->hasAddress(1)
            ->hasEmailAddress(1)
            ->hasPhoneNumber(1)
            ->create();
    }
}
