<?php

namespace antonyz89\seeder;

use console\seeder\DatabaseSeeder;
use yii\base\Model;
use yii\console\Controller;
use yii\console\Exception;
use yii\console\ExitCode;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;

class SeederController extends Controller
{

    /** @var string the default command action. */
    public $defaultAction = 'seed';

    public $seederRoot = 'console\\seeder';
    public $tablesPath = "console\\seeder\\tables";
    public static $modelsPath = 'common\\models';
    public $templateFile = 'vendor\\antonyz89\\yii2-seeder\\src\\views\\createTableSeeder.php';
    public $databaseFile = 'vendor\\antonyz89\\yii2-seeder\\src\\views\\DatabaseSeeder.php';

    /** @var ActiveRecord|Model */
    protected $model = null;

    protected function getClass($path, $end = "\n")
    {
        if (class_exists($path))
            return new $path;

        $this->stdout("Class $path not exists. $end");
        return null;
    }

    public function actionSeed($name = null)
    {
        if ($name) {
            $seederClass = "$this->tablesPath\\{$name}TableSeeder";
            if ($seeder = $this->getClass($seederClass))
                $seeder->run();
        } else {
            (new DatabaseSeeder())->run();
        }
    }

    /**
     * Creates a new seeder.
     *
     * This command creates a new seeder using the available seeder template.
     * After using this command, developers should modify the created seeder
     * skeleton by filling up the actual seeder logic.
     *
     * ```
     * yii seeder/create model_name
     * ```
     *
     * In order to generate a namespaced seeder, you should specify a namespace before the seeder's name.
     * For example:
     *
     * ```
     * yii seeder/create 'common/models/example/User'
     * ```
     *
     * @param string $model_name the name of the new seeder. This should only contain
     * letters, digits, underscores and/or slashes.
     *
     * @return int ExitCode::OK
     * @throws Exception if the name argument is invalid.
     * @throws \yii\base\Exception
     */
    public function actionCreate($model_name)
    {
        if (!preg_match('/^[\w\/]+$/', $model_name)) {
            throw new Exception('The seeder name should contain letters, digits, underscore and/or slash characters only.');
        }

        $modelsPath = self::$modelsPath;

        if (strpos($model_name, '/')) {
            $_ = explode('/', $model_name);
            $model_name = array_pop($_);
            $modelsPath .= DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $_);
        }

        $this->model = $this->getClass($modelsPath . DIRECTORY_SEPARATOR . $model_name);

        if ($this->model === null)
            return ExitCode::OK;

        $this->createDataBaseSeederFile();

        $className = Inflector::camelize($model_name) . 'TableSeeder';

        $file = $this->tablesPath . DIRECTORY_SEPARATOR . $className . '.php';
        if ($this->confirm("Create new seeder '$file'?")) {
            $content = $this->generateSeederSourceCode([
                'className' => $className,
                'namespace' => $this->tablesPath,
                'table' => $this->model::tableName(),
                'fields' => $this->generateFields(),
            ]);
            FileHelper::createDirectory($this->tablesPath);

            if (!file_exists($file) || $this->confirm("\n'$className' already exists, overwrite?\nAll data will be lost irreversibly!")) {
                file_put_contents($file, $content, LOCK_EX);
                $this->stdout("New seeder created successfully.\n", Console::FG_GREEN);
            }

        }

        return ExitCode::OK;
    }

    protected function generateSeederSourceCode($params)
    {
        return $this->renderFile($this->templateFile, $params);
    }

    public function generateFields()
    {
        $schema = $this->model->tableSchema;

        $columns = $schema->columns;
        $foreignKeys = $schema->foreignKeys;
        $fields = [];

        foreach ($foreignKeys as $fk_str => $foreignKey) {
            unset($foreignKeys[$fk_str]);
            $table = array_shift($foreignKey);
            $column = array_key_first($foreignKey);

            $errorMsg = "Foreign Key for '$column' column will be ignored and a common column will be generated.\n";

            $model = $this->getClass(self::$modelsPath . DIRECTORY_SEPARATOR . Inflector::camelize($table), $errorMsg);
            $foreignKeys[$column] = $model;
        }

        foreach ($columns as $column => $data) {
            if (in_array($column, ['id', 'created_at', 'updated_at'])) continue;

            $foreign = null;
            $ref_table_id = null;

            if (isset($foreignKeys[$column])) {
                $foreign = $foreignKeys[$column];
                $ref_table_id = $foreign->tableSchema->primaryKey[0];
            }

            switch ($data->type) {
                case 'integer':
                    $fields[$column] = ['faker' => 'numberBetween(0, 10)'];
                    break;
                case 'date':
                    $fields[$column] = ['faker' => 'date()'];
                    break;
                case 'datetime':
                    $fields[$column] = ['faker' => 'dateTime()'];
                    break;
                default:
                    $fields[$column] = ['faker' => 'text'];
            }

            $fields[$column] = (object)ArrayHelper::merge($fields[$column], [
                'foreign' => $foreign,
                'ref_table_id' => $ref_table_id
            ]);
        }
        return (object)$fields;
    }

    protected function createDataBaseSeederFile()
    {
        $file = "$this->seederRoot\\DatabaseSeeder.php";

        if (!file_exists($file)) {
            FileHelper::createDirectory($this->seederRoot);
            $content = $this->renderFile($this->databaseFile, [
                'namespace' => $this->seederRoot,
            ]);

            file_put_contents($file, $content, LOCK_EX);
        }
    }
}
