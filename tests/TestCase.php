<?php

namespace Swiftmade\StatamicClearAssets\Tests;

use Statamic\Statamic;
use Statamic\Assets\Asset;
use Statamic\Extend\Manifest;
use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Swiftmade\StatamicClearAssets\Tests\Concerns\ManagesAssetContainers;

class TestCase extends OrchestraTestCase
{
    use ManagesAssetContainers;

    private $basePath = 'vendor/orchestra/testbench-core/laravel';

    /** @var \Statamic\Assets\AssetContainer */
    protected $assetContainers;

    protected function setUp(): void
    {
        $this->setUpContentDirectory();

        parent::setUp();

        config(['filesystems.disks.test' => [
            'driver' => 'local',
            'root' => __DIR__ . '/../tmp',
            'url' => '/test',
        ]]);
    }

    protected function tearDown(): void
    {
        //File::deleteDirectory('vendor/orchestra/testbench-core/laravel/content');
        File::deleteDirectory('tmp');
        Asset::all()->each->delete();

        parent::tearDown();
    }

    protected function setUpContentDirectory()
    {
        $this->initializeDirectory($this->basePath . '/content');

        $this->createAssetContainer('assets');
        $this->createAssetContainer('favicons');
        $this->createAssetContainer('social_images');
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
        if (! file_exists($directory)) {
            mkdir($directory);
        }
    }
}
