<?= "<?php\n" ?>
namespace antonyz89\seeder;

class DatabaseSeeder extends TableSeeder
{

    const ADMIN_COUNT = 1;
    const USER_COUNT = 40;

    public $skipForeignKeyChecks = true;

    public function run()
    {
        (new UserTableSeeder())->run();
    }

}
