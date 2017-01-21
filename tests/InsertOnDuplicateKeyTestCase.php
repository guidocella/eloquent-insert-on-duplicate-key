<?php

namespace InsertOnDuplicateKey;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\ClassFinder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Schema;

abstract class InsertOnDuplicateKeyTestCase extends TestCase
{
    /**
     * Creates the application.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../vendor/laravel/laravel/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->app['config']->set('database.connections.mysql.username', 'root');
        $this->app['config']->set('database.connections.mysql.database', 'eloquent_insert_on_duplicate_key');

        if (Schema::hasTable('users')) {
            $this->truncateTables();
        } else {
            $this->migrate();
        }

        $this->app->register(InsertOnDuplicateKeyServiceProvider::class);
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

        foreach ($this->app['db']->select('SHOW TABLES') as $table) {
            $this->app['db']->table($table->$name)->truncate();
        }
    }
}
