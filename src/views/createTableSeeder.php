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

function pluralize($word) {
    return mb_strtolower(Inflector::pluralize($word));
}

function extractModelName($class) {
    $_ = explode('\\', $class);
    return array_pop($_);
}

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
        <?php
            $i = 0;
            foreach ($fields as $column => $properties) {
                if($foreign = $properties->foreign) {
                    $foreignModelName = extractModelName($foreign::className());
                    $plural = pluralize($foreignModelName);
                    $space = $i++ === 0 ? '' : "\t\t";

                    $vars[] = "$$plural";

                    echo $space . "$$plural = $foreignModelName::find()->all();\n";
                }
            } ?>

        loop(function ($i) <?= count($vars) ? 'use ('. implode(', ', $vars) .') ' : '' ?>{
            $this->insert(<?= $modelName ?>::tableName(), [
                <?php
                    $i = 0;
                    foreach ($fields as $column => $properties) {
                        $space = $i++ === 0 ? '' : "\t\t\t\t";
                        if($foreign = $properties->foreign) {
                            $foreignModelName = extractModelName($foreign::className());
                            $plural = pluralize($foreignModelName);
                            echo $space . "'$column' => \$this->faker->randomElement($$plural)->$properties->ref_table_id,\n";
                        } else {
                            echo $space . "'$column' => \$this->faker->$properties->faker,\n";
                        }
                    } ?>
            ]);
        }, DatabaseSeeder::<?= strtoupper(preg_replace("/[{%}]/", '', $table)).'_COUNT' ?>);
    }
}
