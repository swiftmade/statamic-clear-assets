<?php

namespace Swiftmade\StatamicClearAssets\Tests;

use Illuminate\Http\UploadedFile;
use Swiftmade\StatamicClearAssets\ClearAssets;

class ClearAssetsTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_when_there_are_no_unused_assets()
    {
        $this->artisan(ClearAssets::class)->expectsOutput('No unused assets found.');
    }

    /**
     * @test
     */
    public function it_detects_unused_asset_and_skips_used_asset()
    {
        $this->createAsset('tallinn.jpg');
        $this->createAsset('ankara.jpg');

        $this->useAsset('ankara.jpg');

        $this->artisan(ClearAssets::class)
            ->expectsOutput('Found 1 unused asset, taking up 0.06 MB of storage.')
            ->expectsQuestion('Delete these files?', false)
            ->doesntExpectOutput('Removing tallinn.jpg');
    }

    /**
     * @test
     */
    public function it_detects_multiple_unused_assets()
    {
        $this->createAsset('ankara.jpg');
        $this->createAsset('tallinn.jpg');

        $this->artisan(ClearAssets::class)
            ->expectsOutput('Found 2 unused assets, taking up 0.10 MB of storage.')
            ->expectsQuestion('Delete these files?', false)
            ->doesntExpectOutput('Removing tallinn.jpg');
    }

    /**
     * @test
     */
    public function it_can_clear_assets()
    {
        $this->createAsset('ankara.jpg');
        $this->createAsset('tallinn.jpg');

        $this->artisan(ClearAssets::class)
            ->expectsOutput('Found 2 unused assets, taking up 0.10 MB of storage.')
            ->expectsQuestion('Delete these files?', true)
            ->expectsOutput('Removing ankara.jpg')
            ->expectsOutput('Removing tallinn.jpg');

        $this->assertEquals(0, $this->assetContainer->listContents()->count());
    }

    private function createAsset($filename)
    {
        $file = new UploadedFile(
            __DIR__ . '/fixtures/' . $filename,
            $filename,
            'image/jpeg'
        );

        $this->assetContainer->makeAsset($filename)->upload($file);
    }

    private function useAsset($filename)
    {
        $files = base_path('content');
        file_put_contents($files . '/test.yaml', $filename);
    }
}
