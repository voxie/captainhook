<?php

namespace Mpociot\CaptainHook\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Mpociot\CaptainHook\CaptainHookServiceProvider;

class TestCase extends Orchestra
{
    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function getPackageProviders($app)
    {
        return [CaptainHookServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');

        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('queue.driver', 'sync');

        $app['config']->set('captain_hook.transformer', function ($eventData, $webhook) {
            return json_encode($eventData);
        });
        $app['config']->set('captain_hook.listeners', ['eloquent.*']);
        $app['config']->set('captain_hook.log.storage_quantity', 50);
    }

    protected function setUpDatabase()
    {
        $this->artisan('migrate', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__.'/../src/database'),
        ]);
    }
}
