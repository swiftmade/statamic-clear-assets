<?php

namespace Swiftmade\StatamicClearAssets\Tests;

use Statamic\Statamic;
use Statamic\Extend\Manifest;
use Statamic\Providers\StatamicServiceProvider;
use Swiftmade\StatamicClearAssets\ServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            StatamicServiceProvider::class,
            ServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Statamic' => Statamic::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app->make(Manifest::class)->manifest = [
            'swiftmade/statamic-clear-assets' => [
                'id' => 'swiftmade/statamic-clear-assets',
                'namespace' => 'Swiftmade\\StatamicClearAssets\\',
            ],
        ];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $configs = ['assets', 'cp', 'forms', 'routes', 'static_caching', 'sites', 'stache', 'system', 'users'];

        foreach ($configs as $config) {
            $app['config']->set("statamic.$config", require __DIR__ . "/../vendor/statamic/cms/config/{$config}.php");
        }
    }

    public function tearDown(): void
    {

        // destroy $app
        if ($this->app) {
            $this->callBeforeApplicationDestroyedCallbacks();

            // this is the issue.
            // $this->app->flush();

            $this->app = null;
        }

        // call the parent teardown
        parent::tearDown();
    }
}
