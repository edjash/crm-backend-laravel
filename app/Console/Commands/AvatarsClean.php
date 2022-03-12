<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AvatarsClean extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'avatars:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete orphaned avatar files and temporary avatar files created 24 hours ago';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Delete temporary avatars created over 24 hours ago
     */
    private function cleanTmpAvatars()
    {
        $files = Storage::files('public/tmp_avatars');
        $path = storage_path('app');
        $yesterday = strtotime("-1 day");

        foreach ($files as $file) {
            $fname = $path . '/' . $file;
            if (filectime($fname) > $yesterday) {
                Storage::delete($file);
            }
        }
    }

    /**
     * Delete orphaned avatar files
     */
    private function cleanAvatars()
    {
        $files = Storage::files('public/avatars');
        $path = storage_path('app');

        foreach ($files as $file) {
            $basename = basename($file);
            if (!DB::table('contacts')->where('avatar', '=', $basename)->exists()) {
                Storage::delete($file);
            }
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->cleanTmpAvatars();
        $this->cleanAvatars();
        return 0;
    }
}
