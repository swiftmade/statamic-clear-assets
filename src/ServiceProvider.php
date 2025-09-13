<?php

namespace Swiftmade\StatamicClearAssets;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        /**
         * bootConfig() does not get called during unit tests,
         * so we manually merge the config here.
         */
        if (app()->runningUnitTests()) {
            $this->mergeConfigFrom(
                __DIR__ . '/../config/statamic-clear-assets.php',
                'statamic-clear-assets'
            );
        }

        $this->commands([
            ClearAssets::class,
        ]);
    }
}
