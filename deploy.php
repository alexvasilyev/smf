#!/usr/bin/env php
<?php

define('ROOT_DIR', dirname(__FILE__));

$arguments = $_SERVER['argv'];

if (count($arguments) < 2) {
    die('Please, give me some food!'.PHP_EOL);
}

$key = $arguments[1];

$propertiesPath = ROOT_DIR.'/config/properties.ini';
if (!file_exists($propertiesPath)) {
    die('Can\'t find properties.ini'.PHP_EOL);
}

$properties = parse_ini_file($propertiesPath, true);
if (!isset($properties[$key])) {
    die("Section '{$key}' not configured\n");
}

$properties = $properties[$key];

if (!isset($properties['host'])) {
    die('You must define a "host" entry.'.PHP_EOL);
}

if (!isset($properties['dir'])) {
    die('You must define a "dir" entry.'.PHP_EOL);
}

$dir = $properties['dir'];
if ('local' == $properties['type']) {
    $parameters = '--exclude-from=config/rsync_exclude.txt';

    $dryRun = (isset($arguments[2]) && '--go' == $arguments[2]) ? '' : '--dry-run';

    $command = "rsync -v -r -p --delete --links {$dryRun} {$parameters} ./ {$dir}";
} else {
    $host = $properties['host'];
    $user = isset($properties['user']) ? $properties['user'].'@' : '';
    $dir = "{$user}{$host}:$dir";

    if (substr($dir, -1) != '/') {
        $dir .= '/';
    }

    $ssh = 'ssh';
    $sshParameters = array();
    if (isset($properties['port'])) {
        $port = $properties['port'];
        $sshParameters[] = "-p$port";
    }
    if (isset($properties['key'])) {
        $sshKey = $properties['key'];
        $sshParameters[] = "-i $sshKey";
    }
    if (count($sshParameters)) {
        $ssh = '-e "'.$ssh.' '.join(' ', $sshParameters).'"';
    }
}

$dryRun = (isset($arguments[2]) && '--go' == $arguments[2]) ? '' : '--dry-run';

$parameters = '--exclude-from=config/rsync_exclude.txt';

$command = "rsync -v -r -p --delete --links {$dryRun} {$parameters} {$ssh} ./ {$dir}";

echo $command, PHP_EOL;
system($command);
