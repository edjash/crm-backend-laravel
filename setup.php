<?php
$basedir = rtrim(__DIR__, '/') . '/';
$config = require $basedir . 'config/crm.php';

if (!is_array($config['avatars'])) {
    output("Config file config/crm.php is not valid.", true);
}
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
    $basedir . 'storage/app/seed_avatars',
    $basedir . 'storage/app/socialmedia',
    $basedir . 'storage/app/public',
    $basedir . 'storage/app/public/socialmedia',
    $basedir . 'storage/app/public/socialmedia/24x24',
];

//avatar directories
foreach ($config['avatars'] as $name => $target) {
    $directories[] = $basedir . 'storage/app' . $target['dir'];
}

foreach ($directories as $dir) {
    create_and_set($dir);
}

//copy social media icons to public
$sm_src = $basedir . 'storage/app/socialmedia/*';
$sm_dst = $basedir . 'storage/app/public/socialmedia/';
shell_exec("cp -R $sm_src $sm_dst");

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
