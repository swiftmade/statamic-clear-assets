<?php

namespace Swiftmade\StatamicClearAssets;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    public function bootAddon()
    {
        $this->commands([
            ClearAssets::class,
        ]);
    }
}
