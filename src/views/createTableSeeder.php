<?php
/**
 * This view is used by mootensai\seeder\SeederController.php.
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

use mootensai\seeder\TableSeeder;
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
    function run($count = 10)
    {
        loop(function ($count) <?= count($vars) ? 'use ('. implode(', ', $vars) .') ' : null ?>{
            $this->insert(<?= $modelName ?>::tableName(), [
                <?php
                    $i = 0;
                    foreach ($fields as $column => $properties) {
                        $space = $i++ === 0 ? '' : "\t\t\t\t";
                        if($foreign = $properties->foreign) {
                            $count = '\$count';
                        
                            echo $space . "'$column' => \$this->faker->numberBetween(1, \$count),\n";
                        } else {
                            echo $space . "'$column' => \$this->faker->$properties->faker,\n";
                        }
                    } ?>
            ]);
        }, $count);
    }
}
