<?php
$basedir = rtrim(__DIR__, '/') . '/';

if (is_file($basedir . 'setup.lock')) {
    output("setup.lock file detected, cannot proceed.", true);
} else {
    if (!@file_put_contents($basedir . 'setup.lock', time())) {
        output("Failed to create setup.lock file, cannot proceed.", true);
    }
}

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

function create_and_set($dir)
{
    $name = strstr($dir, 'storage/');
    if (!is_dir($dir)) {
        output("$name directory does not exist. Creating...");
        if (@mkdir($dir)) {
            output("Created $name directory");
        } else {
            output("Error: Failed to create $name directory: '$dir'", true);
        }
    }
    output("Setting permissions for $name directory.");
    set_perms($dir, true);
}

echo "Ownership of specific files and folders will be set to: $user:$group\n";
echo "Mode of specific files and folders will be set to: $mode\n";
//storage directory
output("Setting permissions for storage directory.");
set_perms($basedir . 'storage', true);
//bootstrap directory
output("Setting permissions for bootstrap/cache directory.");
set_perms($basedir . 'bootstrap/cache', true);

$directories = [
    $basedir . 'storage/app/public',
    $basedir . 'storage/app/avatars',
    $basedir . 'storage/app/avatars/tmp',
    $basedir . 'storage/app/avatars/seed',
    $basedir . 'storage/app/avatars/small',
    $basedir . 'storage/app/avatars/medium',
    $basedir . 'storage/app/avatars/large',
];

foreach ($directories as $dir) {
    create_and_set($dir);
}

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
