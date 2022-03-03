<?php
$basedir = rtrim(__DIR__, '/') . '/';
$apacheUser = getenv('APACHE_RUN_USER');
if (!$apacheUser) {
    $output = exec('apachectl -S 2>/dev/null | grep User');
    $apacheUser = preg_match('/name="([^"]+)"/', $output, $match) ? $match[1] : get_current_user();
}

$user = get_current_user();
$group = $apacheUser;
$mode = 775;

function output($str, $abort = false)
{
    echo "* " . $str . "\n";
    if ($abort) {
        echo "Aborted.\n";
        exit();
    }
}

function set_perms($dir, $recursive)
{
    global $user, $group, $mode;
    $r = ($recursive) ? ' -R' : '';
    `chown$r $user:$group $dir`;
    `chmod$r $mode $dir`;
}

echo "Ownership of specific files and folders will be set to: $user:$group\n";
echo "Mode of specific files and folders will be set to: $mode\n";
//storage directory
output("Setting permissions for storage directory.");
set_perms($basedir . 'storage', true);
//bootstrap directory
output("Setting permissions for bootstrap/cache directory.");
set_perms($basedir . 'bootstrap/cache', true);

//storage/app/public/avatar Directory
$avatars_dir = $basedir . 'storage/app/public/avatars';
if (!is_dir($avatars_dir)) {
    output('avatars directory does not exist. Creating...');
    if (@mkdir($avatars_dir)) {
        output("Created avatars directory");
    } else {
        output("Error: Failed to create avatars directory", true);
    }
}
output("Setting permissions for avatars directory.");
set_perms($avatars_dir, true);

//storage/app/public/tmp_avatars Directory
$tmp_avatars_dir = $basedir . 'storage/app/public/tmp_avatars';
if (!is_dir($tmp_avatars_dir)) {
    output('tmp_avatars directory does not exist. Creating...');
    if (@mkdir($tmp_avatars_dir)) {
        output("Created tmp_avatars directory");
    } else {
        output("Error: Failed to create tmp_avatars directory", true);
    }
}
output("Setting permissions for tmp_avatars directory.");
set_perms($tmp_avatars_dir, true);

//Symlink from public/storage to storage/app/public
$link_target = $basedir . 'storage/app/public';
$link = $basedir . 'public/storage';
if (!is_link($link)) {
    output('Symlink to public storage does not exist. Creating...');
    if (@symlink($link_target, $link)) {
        output("Created symlink to public storage directory");
    } else {
        output("Error: Failed to create symlink for public storage", true);
    }
}

//Successful
echo "Finished.\n";
