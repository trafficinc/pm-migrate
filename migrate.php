#!/usr/bin/env php
<?php

/*
PM Migrate: PHP, MySQL Migration Lib.
*/

if (php_sapi_name() !== 'cli') {
    exit;
}

require __DIR__ . '/vendor/autoload.php';


use App\Commands\Migrate;
use App\Config\Config;
use App\Cli;


$cli = new Cli();

$cli->registerCommand('migrate:version', function (array $argv) use ($cli) {
   (new Migrate(new Config()))->version();
});

$cli->registerCommand('migrate:add', function (array $argv) use ($cli) {
    (new Migrate(new Config()))->add($argv);
});

$cli->registerCommand('migrate', function (array $argv) use ($cli) {
    (new Migrate(new Config()))->migrate($argv);
});


$cli->registerCommand('help', function (array $argv) use ($cli) {
    $menu = " _____ __  __  __  __ ___ _____ _____ _____ ____ _____
/  _  /  \/  -/  \/  /___/   __/  _  /  _  /    /   __\
|   __|  \/  -|  \/  |   |  |_ |  _  |  _  \-  -|   __|
\__/  \__ \__ \__ \__\___\_____\__|\_\__|__/|__|\_____/
+-------------------------------+----------------------------------------------------------------------+
|  usage: php pm-migrate/migrate.php [options]                                                         |
+-------------------------------+----------------------------------------------------------------------+
|  migrate:version              | Get migration version                                                |
+-------------------------------+----------------------------------------------------------------------+
|  migrate:add [migration_name] |  Add a migration file.                                               |
+-------------------------------+----------------------------------------------------------------------+
|  migrate                      |  Run the migration file for the current version.                     |
+-------------------------------+----------------------------------------------------------------------+";
    $cli->getPrinter()->display( $menu );
});

$cli->runCommand($argv);