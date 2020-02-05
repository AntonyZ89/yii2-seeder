<?php
/** @var string $namespace */
echo "<?php\n";
?>
namespace <?= $namespace ?>;

use antonyz89\seeder\TableSeeder;

class DatabaseSeeder extends TableSeeder
{

    const MODEL_COUNT = 10;

    public $skipForeignKeyChecks = true;

    public function run()
    {
        //(new ModelTableSeeder())->run();
    }

}
