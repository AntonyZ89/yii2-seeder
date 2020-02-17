yii2-seeder
===================

It is widget to yii2 framework to seeder database.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist antonyz89/yii2-seeder dev-master
```

or add

```
"antonyz89/yii2-seeder": "dev-master"
```

to the require section of your `composer.json` file.

USAGE
--------
**console/config/main.php**
```
'controllerMap' => [
    'seeder' => [
        'class' => 'antonyz89\seeder\SeederController'
    ],
],
```


SEEDER COMMANDS
--------------

`yii seeder` Seed all tables in `Database::run()`

`yii seeder model_name` Seed a table

`yii seeder/create model_name` Create a TableSeeder in `console\seeder\tables`

For seeder, if the model is not at the root of the `common/models`, just add the folder where the model is located inside the `common/models` directory.

Example:

`yii seeder/create entity/user`

`entity` is the folder where `User` (model) is located inside the `common/models` directory.

To change the default path for models, just change the `$modelPath` variable in `SeederController`

**Only Seeders within `DatabaseSeeder::run()` will be used in `yii seeder` command**

SEEDER
---------
 
**EXAMPLE TEMPLATE**
```php
<?php

namespace console\seeder\tables;

use common\models\user\User;
use console\seeder\DatabaseSeeder;
use console\seeder\TableSeeder;
use Yii;

class UserTableSeeder extends TableSeeder
{

    // optional
    public $model_path = 'common\\models\\user'; // only necessary if the Seeder model is not in 'common\models' folder

    function run()
    {
        loop(function ($i) {
            $this->insert($this->tableName, [
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

By default, all TableSeeder truncate the table before inserting new data, if you didn't want that to happen in a Seeder, just overwrite `$skipForeignKeyChecks`:

```php
public $skipForeignKeyChecks = true
```


**default in TableSeeder:** 
```php
public $skipForeignKeyChecks = false;

...

// truncate table
$this->disableForeginKeyChecks();
$this->truncateTable($this->tableName);
$this->enableForeginKeyChecks();
```


If the seeder model is not located in `common\models` just overwrite `$model_path`:

```php
public $model_path = 'model\\directory\\here';
```


**default in TableSeeder:** 
```php
public $model_path = 'common\\models';
```

At the end of every Seeder, if any columns have been forgotten, a notification with all the missing columns will appear



```console
    > #################### MISSING COLUMNS ###################
    > # TABLE: {{%user}}                                     #
    > #    name => varchar(255)                              #
    > #    age => int(2)                                     #
    > ########################################################
```
