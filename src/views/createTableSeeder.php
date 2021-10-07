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
/* @var $modelNamespace string */
/* @var $modelName string */

echo "<?php\n";
if (!empty($namespace)) {
    echo "\nnamespace {$namespace};\n";
}

use yii\helpers\Inflector;

$vars = [];
?>

use antonyz89\seeder\TableSeeder;
use console\seeder\DatabaseSeeder;
<?php foreach ($fields as $column => $properties) {
    if($foreign = $properties->foreign)
        echo "use {$foreign::className()};\n";
} ?>
<?= "use $modelNamespace\\$modelName;\n" ?>

/**
 * Handles the creation of seeder `<?= $table ?>`.
 */
class <?= $className ?> extends TableSeeder
{
    /**
     * {@inheritdoc}
     */
    function run()
    {
        loop(function ($i) <?= count($vars) ? 'use ('. implode(', ', $vars) .') ' : null ?>{
            $this->insert(<?= $modelName ?>::tableName(), [
                <?php
                    $i = 0;
                    foreach ($fields as $column => $properties) {
                        $space = $i++ === 0 ? '' : "\t\t\t\t";
                        if($foreign = $properties->foreign) {
                            $count = strtoupper(preg_replace("/[{%}]/", '', $foreign::tableName())) . '_COUNT';
                        
                            echo $space . "'$column' => \$this->faker->numberBetween(1, DatabaseSeeder::$count),\n";
                        } else {
                            echo $space . "'$column' => \$this->faker->$properties->faker,\n";
                        }
                    } ?>
            ]);
        }, DatabaseSeeder::<?= strtoupper(preg_replace("/[{%}]/", '', $table)) ?>_COUNT);
    }
}
