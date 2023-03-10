<?php

namespace Swiftmade\StatamicClearAssets\Tests\Concerns;

use Statamic\Assets\AssetContainer;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait ManagesAssetContainers
{
    protected function createAssetContainer($handle)
    {
        $assetsDir = $this->basePath . '/content/assets';

        if (! file_exists($assetsDir)) {
            mkdir($assetsDir);
        }

        $path = $assetsDir . '/' . $handle . '.yaml';

        file_put_contents($path, join("\n", [
            "title: '{$handle}'",
            "disk: '{$handle}'",
        ]));
    }

    protected function getAssetContainer($handle = null)
    {
        if (is_null($handle)) {
            $handle = 'assets';
        }

        return AssetContainer::find($handle);
    }

    protected function assertContainerFileCount($handle, $length)
    {
        $this->assertEquals(
            $length,
            $this->getAssetContainer($handle)->assets()->count(),
            'Could not find the expected number of files in the container "' . $handle . '".'
        );
    }

    /**
     * If no container is provided, the default container (assets) will be used.
     */
    protected function saveFileToContainer(UploadedFile $file, AssetContainer $container = null)
    {
        if (is_null($container)) {
            $container = $this->getAssetContainer();
        }

        $container->makeAsset($file->getFilename())->upload($file);
    }
}
