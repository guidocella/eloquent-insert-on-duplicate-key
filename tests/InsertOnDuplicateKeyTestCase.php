<?php

namespace InsertOnDuplicateKey;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Filesystem\ClassFinder;
use Illuminate\Filesystem\Filesystem;
use PHPUnit_Framework_TestCase;

abstract class InsertOnDuplicateKeyTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        $db = new DB;

        $db->addConnection([
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'eloquent_insert_on_duplicate_key',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
        ]);

        $db->bootEloquent();
        $db->setAsGlobal();

        if (DB::table('information_schema.tables')->where('table_schema', 'eloquent_insert_on_duplicate_key')->exists()) {
            $this->truncateTables();
        } else {
            $this->migrate();
        }
    }

    /**
     * Run package database migrations.
     *
     * @return void
     */
    protected function migrate()
    {
        $fileSystem = new Filesystem;
        $classFinder = new ClassFinder;

        foreach ($fileSystem->files(__DIR__ . '/Migrations') as $file) {
            $fileSystem->requireOnce($file);
            $migrationClass = $classFinder->findClass($file);

            (new $migrationClass)->up();
        }
    }

    /**
     * Truncate the database tables.
     *
     * @return void
     */
    protected function truncateTables()
    {
        /**
         * The name of the field of the classes returned from DB::select('SHOW TABLES')
         * whose value is the name of each table.
         *
         * @var string
         */
        $name = 'Tables_in_eloquent_insert_on_duplicate_key';

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        foreach (DB::select('SHOW TABLES') as $table) {
            DB::table($table->$name)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Assert that a given where condition exists in the database.
     *
     * @param  string  $table
     * @param  array  $data
     * @return void
     */
    protected function seeInDatabase($table, array $data)
    {
        $this->assertTrue(
            DB::table($table)->where($data)->exists(),
            sprintf(
                'Unable to find row in database table [%s] that matched attributes [%s].', $table, json_encode($data)
            )
        );
    }

    /**
     * Assert that a given where condition does not exists in the database.
     *
     * @param  string  $table
     * @param  array  $data
     * @return void
     */
    protected function dontSeeInDatabase($table, array $data)
    {
        $this->assertFalse(
            DB::table($table)->where($data)->exists(),
            sprintf(
                'Found unexpected records in database table [%s] that matched attributes [%s].', $table, json_encode($data)
            )
        );
    }
}
