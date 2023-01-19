<?php

namespace Swiftmade\StatamicClearAssets;

use Statamic\Providers\AddonServiceProvider;
use function config_path;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        $this->commands([
            ClearAssets::class,
        ]);

        $this->publishes([__DIR__.'/../config/clear-assets.php' => config_path('statamic/clear-assets.php')], 'statamic-clear-assets-config');
        $this->mergeConfigFrom(__DIR__.'/../config/clear-assets.php', 'statamic.clear-assets');
    }
}
