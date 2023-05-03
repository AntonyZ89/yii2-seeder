yii2-seeder
===================

<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=YATHVT293SXDL&source=url">
  <img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" alt="Donate with PayPal" />
</a>

--

[![Latest Stable Version](https://poser.pugx.org/mootensai/yii2-seeder/v/stable)](https://packagist.org/packages/mootensai/yii2-seeder)
[![Total Downloads](https://poser.pugx.org/mootensai/yii2-seeder/downloads)](https://packagist.org/packages/mootensai/yii2-seeder)
[![Latest Unstable Version](https://poser.pugx.org/mootensai/yii2-seeder/v/unstable)](https://packagist.org/packages/mootensai/yii2-seeder)
[![License](https://poser.pugx.org/mootensai/yii2-seeder/license)](https://packagist.org/packages/mootensai/yii2-seeder)

- [Installation](#installation)
- [Usage](#usage)
- [Seeder Commands](#seeder-commands)
- [Seeder Template](#seeder)
- [DatabaseSeeder Class](#databaseseeder)

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist mootensai/yii2-seeder dev-master
```

or add

```
"mootensai/yii2-seeder": "dev-master"
```

to the require section of your `composer.json` file.

## USAGE

**console/config/main.php**
```
'controllerMap' => [
    'seeder' => [
        'class' => 'mootensai\seeder\SeederController'
    ],
],
```


## SEEDER COMMANDS

`yii seeder` Seed all tables in `Database::run()`

`yii seeder [name]` Seed a table

`yii seeder [name]:[funtion_name]` Seed a table and run a specific function from selected TableSeeder
- `name` without TableSeeder (e.g `yii seeder user` for `UserTableSeeder`)

`yii seeder/create model_name` Create a TableSeeder in `console\seeder\tables`

For seeder, if the model is not at the root of the `common/models`, just add the folder where the model is located inside the `common/models` directory.

Example:

`yii seeder/create entity/user`

`entity` is the folder where `User` (model) is located inside the `common/models` directory.

To change the default path for models, just change the `$modelNamespace` variable in `SeederController`

**Only Seeders within `DatabaseSeeder::run()` will be used in `yii seeder` command**

## SEEDER
 
**EXAMPLE TEMPLATE**
```php
<?php

namespace console\seeder\tables;

use common\models\user\User;
use console\seeder\DatabaseSeeder;
use mootensai\seeder\TableSeeder;
use Yii;

class UserTableSeeder extends TableSeeder
{
    function run()
    {
        loop(function ($i) {
            $this->insert(User::tableName(), [
                'email' => "user$i@gmail.com",
                'name' => $this->faker->name,
                'document' => $this->faker->numerify('##############'),
                'street' => $this->faker->streetName,
                'number' => $this->faker->numerify('###'),
                'zip_code' => $this->faker->postcode,
                'city' => $this->faker->city,
                'status' => User::STATUS_USER_ACTIVE
                //created_at and updated_at are automatically added
            ]);
        }, DatabaseSeeder::USER_COUNT);
    }
}
```

By default, all TableSeeder truncate the table before inserting new data, if you didn't want that to happen in a Seeder, just overwrite `$skipTruncateTables`:

```php
public $skipTruncateTables = true;
```


**default in TableSeeder:** 
```php
public $skipTruncateTables = false;

...

// truncate table
$this->disableForeignKeyChecks();
$this->truncateTable(/* table names */);
$this->enableForeignKeyChecks();
```

At the end of every Seeder, if any columns have been forgotten, a message with all the missing columns will appear


```console
    > #################### MISSING COLUMNS ###################
    > # TABLE: {{%user}}                                     #
    > #    name => varchar(255)                              #
    > #    age => int(2)                                     #
    > ########################################################
```

## DatabaseSeeder

`DatabaseSeeder` will be created on first `yii seeder/create model`

Here you will put all TableSeeder in `::run()`

to run, use `yii seeder` or `yii seeder [name]`

- `name` without TableSeeder (e.g `yii seeder user` for `UserTableSeeder`)

**DatabaseSeeder template:**

**`DatabaseSeeder` localization is `console\seeder`**
```php
class DatabaseSeeder extends TableSeeder
{

    const MODEL_COUNT = 10;

    public function run()
    {
        ModelTableSeeder::create()->run();
    }

}
```
