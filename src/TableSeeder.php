<?php

namespace antonyz89\seeder;

use antonyz89\seeder\helpers\CreatedAtUpdatedAt;
use Faker\Generator;
use Faker\Provider\pt_BR\Address;
use Faker\Provider\pt_BR\Company;
use Faker\Provider\pt_BR\Person;
use Faker\Provider\pt_BR\PhoneNumber;
use Yii;
use yii\base\NotSupportedException;
use yii\db\Exception;
use yii\db\Migration;

/**
 * Class TableSeeder
 * @package console\seeder
 *
 * @property Generator $faker
 * @property string $tableName
 * @property boolean $skipForeignKeyChecks
 * @property string $model_path
 */
abstract class TableSeeder extends Migration
{
    use CreatedAtUpdatedAt;

    public $faker;
    public $tableName;
    public $skipForeignKeyChecks = false;
    public $model_path;
    private $insertedColumns = [];
    private $batch = [];
    public $insertType = self::BY_BATCH;

    /** Use BY_INSERT to use `::insert()` method instead seeder with `::batchInsert()` on desctruction of class */
    const BY_INSERT = 1;
    const BY_BATCH = 2;

    /**
     * TableSeeder constructor.
     * @param array $config
     * @throws Exception
     * @throws NotSupportedException
     */
    public function __construct(array $config = [])
    {
        if ($this->model_path === null)
            $this->model_path = SeederController::$modelsPath;

        $this->faker = \Faker\Factory::create();
        $this->faker->addProvider(new Address($this->faker));
        $this->faker->addProvider(new Company($this->faker));
        $this->faker->addProvider(new Person($this->faker));
        $this->faker->addProvider(new PhoneNumber($this->faker));

        if (!$this->skipForeignKeyChecks) {
            $class = str_replace('TableSeeder', '', array_slice(explode('\\', static::class), -1, 1)[0]);
            $this->tableName = ("$this->model_path\\$class")::tableName();

            $this->disableForeginKeyChecks();
            $this->truncateTable($this->tableName);
            $this->enableForeginKeyChecks();
        }

        parent::__construct($config);
    }

    public function __destruct()
    {
        if ($this->insertType === self::BY_BATCH) {
            foreach ($this->batch as $table => $values)
                $this->batchInsert($table, $values['columns'], $values['rows']);
        }

        self::checkMissingColumns($this->insertedColumns);
    }

    abstract function run();

    /**
     * @throws Exception
     * @throws NotSupportedException
     */
    public function disableForeginKeyChecks()
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
    }

    /**
     * @throws Exception
     * @throws NotSupportedException
     */
    public function enableForeginKeyChecks()
    {
        Yii::$app->db->createCommand()->checkIntegrity(true)->execute();
    }

    public function insert($table, $columns)
    {
        $columnNames = Yii::$app->db->getTableSchema($table)->columnNames;

        $this->generate();

        if (in_array('created_at', $columnNames))
            $columns['created_at'] = $this->createdAt;

        if (in_array('updated_at', $columnNames))
            $columns['updated_at'] = $this->updatedAt;

        $this->insertedColumns[$table] = array_keys($columns);

        if ($this->insertType === self::BY_BATCH) {
            $this->batch[$table]['columns'] = array_keys($columns);
            $this->batch[$table]['rows'][] = array_values($columns);
        } else {
            parent::insert($table, $columns);
        }
    }

    public function truncateTable($table)
    {
        $this->db = Yii::$app->db;
        parent::truncateTable($table);
    }

    private static function checkMissingColumns($insertedColumns)
    {
        $missingColumns = [];

        foreach ($insertedColumns as $table => $columns) {
            $tableColumns = Yii::$app->db->getTableSchema($table)->columns;

            foreach ($tableColumns as $column) {
                if ($column->name === 'id') continue;

                if (!in_array($column->name, $columns))
                    $missingColumns[$table][] = [$column->name, $column->dbType];
            }
        }


        if (!empty($missingColumns)) {
            echo "    > " . str_pad(' MISSING COLUMNS ', 70, '#', STR_PAD_BOTH) . "\n";
            foreach ($missingColumns as $table => $columns) {
                echo "    > " . str_pad("# TABLE: $table", 69, ' ') . "#\n";
                foreach ($columns as [$column, $type])
                    echo "    > " . str_pad("#    $column => $type", 69, ' ') . "#\n";
            }
            echo "    > " . str_pad('', 70, '#') . "\n";
        }
    }

    public static function create()
    {
        return new static();
    }
}
