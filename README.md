PM MIGRATE
==========

PM Migrate is an easy to use PHP/MySQL migrations library for those who want to keep their migrations in SQL in a minimal way, it handles upward migrations.

How to Use
==========

1.) Git Clone or Download this project into your webroot. (Or same level as `artisan` in Laravel)

2.) Edit the `example.config`, fill in your config values and save the file as `.config`

3.) Add new migration: `php pm-migrate/migrate.php migrate:add [migration-name-without-spaces]`

4.) Open/Edit the migration file [migrations/migrate-000x-name.php] with needed migration SQL code.

5.) To migrate to the latest version: `php pm-migrate/migrate.php migrate`

Running `migrate` will create a .version file that will track your migration versions. You can also go to the help menu `php pm-migrate/migrate.php help` for more options.

New migrations, when "added" will appear under the `/migrations` folder. You can then add as much SQL code as needed like so:

```php

<?php

use App\Config\Config;
use App\Commands\Migrate;


$query = "ALTER TABLE roles ADD COLUMN role_name VARCHAR(15) AFTER type;";

(new Migrate(new Config()))->query($query);

```
OR

```php

<?php

use App\Config\Config;
use App\Commands\Migrate;


$query1 = "ALTER TABLE roles ADD COLUMN role_name VARCHAR(15) AFTER type;";

(new Migrate(new Config()))->query($query1);

$query2 = "ALTER TABLE users ADD COLUMN user_name VARCHAR(150) AFTER email;";

(new Migrate(new Config()))->query($query2);

$query2 = "ALTER TABLE users ADD COLUMN password VARCHAR(15) AFTER user_name;";

(new Migrate(new Config()))->query($query2);

```
