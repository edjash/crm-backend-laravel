<?php

namespace Database\Seeders;

use App\Models\Contact;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ContactsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function createAvatarFiles()
    {
        $avatarCfg = Config::get('crm.avatars');
        $files = Storage::allFiles('seed_avatars/');

        foreach ($avatarCfg as $name => $target) {
            $path = storage_path('app/' . trim($target['dir'], '/'));
            if (!is_dir($path) || !is_writable($path)) {
                $this->command->line("Error: Path '$path' does not exist or is not writable.");
                exit;
            }

            if (in_array($name, ['tmp', 'root'])) {
                continue;
            }
            //delete all files in target directory
            $tfiles = Storage::allFiles($target['dir']);
            Storage::delete($tfiles);
            //copy and size new files from seed directory
            foreach ($files as $file) {
                $fname = basename($file);
                $src = 'seed_avatars/' . $fname;
                $dst = rtrim($target['dir'], '/') . '/' . $fname;
                //resize new image
                if ($target['width'] && $target['height']) {
                    $xsrc = storage_path('app/' . $src);
                    $xdst = storage_path('app/' . $dst);
                    $img = Image::make($xsrc);
                    $img->resize($target['width'], $target['height']);
                    $img->save($xdst);
                } else {
                    Storage::copy($src, $dst);
                }
            }
        }
    }

    public function run()
    {
        $faker = Faker::create();
        $this->createAvatarFiles();

        $files = Storage::allFiles('public/avatars/large/');
        shuffle($files);

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
            $lastName = $faker->lastName();

            Contact::factory()
                ->hasAddress(1)
                ->hasEmailAddress(1)
                ->hasPhoneNumber(1)
                ->hasNotes(rand(0, 10))
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
