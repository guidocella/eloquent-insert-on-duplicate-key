<?php

namespace InsertOnDuplicateKey;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase;

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
        $this->app['config']->set('database.connections.mysql.password', 'xxxx');

        $this->app['config']->set('database.connections.mysql.database', 'eloquent_insert_on_duplicate_key');

        $this->migrate('up');

        $this->app->register(InsertOnDuplicateKeyServiceProvider::class);
    }

    /**
     * Run package database migrations.
     *
     * @param  string $method
     * @return void
     */
    protected function migrate($method)
    {
        $migrator = $this->app['migrator'];

        foreach ($migrator->getMigrationFiles(__DIR__ . '/Migrations') as $file) {
            require_once $file;

            ($migrator->resolve($migrator->getMigrationName($file)))->$method();
        }
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    protected function tearDown()
    {
        $this->migrate('down');

        parent::tearDown();
    }
}
