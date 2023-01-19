<?php

namespace Swiftmade\StatamicClearAssets\Tests;

use Statamic\Statamic;
use Statamic\Assets\Asset;
use Statamic\Extend\Manifest;
use Statamic\Assets\AssetContainer;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /** @var \Statamic\Assets\AssetContainer */
    protected $assetContainer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initializeDirectory('vendor/orchestra/testbench-core/laravel/content');

        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__ . '/../tmp',
            'url' => '/test',
        ]]);


        $this->assetContainer = (new AssetContainer)
            ->handle('test_container')
            ->disk('test')
            ->save();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory('vendor/orchestra/testbench-core/laravel/content');
        File::deleteDirectory('tmp');
        Asset::all()->each->delete();

        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            \Rebing\GraphQL\GraphQLServiceProvider::class,
            \Statamic\Providers\StatamicServiceProvider::class,
            \Wilderborn\Partyline\ServiceProvider::class,
            \Swiftmade\StatamicClearAssets\ServiceProvider::class,
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
                'namespace' => 'Swiftmade\\StatamicClearAssets',
            ],
        ];
    }

    protected function resolveApplicationConfiguration($app)
    {
        parent::resolveApplicationConfiguration($app);

        $configs = [
            'assets', 'cp', 'forms', 'routes', 'static_caching',
            'sites', 'stache', 'system', 'users',
        ];

        foreach ($configs as $config) {
            $app['config']->set("statamic.$config", require(__DIR__ . "/../vendor/statamic/cms/config/{$config}.php"));
        }

        $app['config']->set('statamic.users.repository', 'file');
    }

    protected function initializeDirectory($directory)
    {
        if (File::isDirectory($directory)) {
            File::deleteDirectory($directory);
        }

        File::makeDirectory($directory, 0755, true);
    }
}
