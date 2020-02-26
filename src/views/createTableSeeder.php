<?php
/**
 * This view is used by antonyz89\seeder\SeederController.php.
 *
 * The following variables are available in this view:
 */
/* @var $className string the new seeder class name without namespace */
/* @var $namespace string the new seeder class namespace */
/* @var $table string the name table */
/* @var $fields array the fields */
/* @var $customPath string|null custom path to model */

echo "<?php\n";
if (!empty($namespace)) {
    echo "\nnamespace {$namespace};\n";
}

use yii\helpers\Inflector;

function pluralize($word) {
    return mb_strtolower(Inflector::pluralize($word));
}

$vars = [];
?>

use antonyz89\seeder\TableSeeder;
use console\seeder\DatabaseSeeder;
<?php foreach ($fields as $column => $properties) {
    if($foreign = $properties->foreign)
        echo "use {$foreign::className()};\n";
} ?>

/**
 * Handles the creation of seeder `<?= $table ?>`.
 */
class <?= $className ?> extends TableSeeder
{
<?php if ($customPath !== null)
    echo "\npublic \$model_path = '$customPath';\n";
?>
    /**
     * {@inheritdoc}
     */
    function run()
    {
        <?php
            $i = 0;
            foreach ($fields as $column => $properties) {
                if($foreign = $properties->foreign) {
                    $modelName = basename(str_replace('\\', '/', $foreign::className()));
                    $plural = pluralize($modelName);
                    $space = $i++ === 0 ? '' : "\t\t";

                    $vars[] = "$$plural";

                    echo $space . "$$plural = $modelName::find()->all();\n";
                }
            } ?>

        loop(function ($i) <?= !empty($vars) ? 'use ('.implode(', ', $vars).') ' : '' ?>{
            $this->insert($this->tableName, [
                <?php
                    $i = 0;
                    foreach ($fields as $column => $properties) {
                        $space = $i++ === 0 ? '' : "\t\t\t\t";
                        if($foreign = $properties->foreign) {
                            $modelName = basename(str_replace('\\', '/', $foreign::className()));
                            $plural = pluralize($modelName);
                            echo $space . "'$column' => \$this->faker->randomElement($$plural)->$properties->ref_table_id,\n";
                        } else {
                            echo $space . "'$column' => \$this->faker->$properties->faker,\n";
                        }
                    } ?>
            ]);
        }, DatabaseSeeder::<?= strtoupper(substr($table, 3, strlen($table)-5)).'_COUNT' ?>);
    }
}
