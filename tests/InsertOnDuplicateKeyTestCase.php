<?php

namespace InsertOnDuplicateKey;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\ClassFinder;
use Illuminate\Filesystem\Filesystem;
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
        $fileSystem = new Filesystem;
        $classFinder = new ClassFinder;

        foreach ($fileSystem->files(__DIR__ . '/Migrations') as $file) {
            $fileSystem->requireOnce($file);
            $migrationClass = $classFinder->findClass($file);

            (new $migrationClass)->$method();
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
