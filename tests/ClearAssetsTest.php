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
            ->expectsChoice('What would you like to do?', ClearAssets::CMD_EXIT, ClearAssets::$choices)
            ->doesntExpectOutput('Removing tallinn.jpg');
    }

    /**
     * @test
     */
    public function it_can_list_unused_assets()
    {
        $this->createAsset('ankara.jpg');
        $this->createAsset('tallinn.jpg');

        $this->artisan(ClearAssets::class)
            ->expectsTable(['Asset', 'Size'], [
                ['ankara.jpg', '0.04 MB'],
                ['tallinn.jpg', '0.06 MB'],
            ])
            ->expectsOutput('Found 2 unused assets, taking up 0.10 MB of storage.')
            ->expectsChoice('What would you like to do?', ClearAssets::CMD_EXIT, ClearAssets::$choices)
            ->doesntExpectOutput('Removing tallinn.jpg');
    }

    /**
     * @test
     */
    public function it_deletes_all_unused_assets()
    {
        $this->createAsset('ankara.jpg');
        $this->createAsset('tallinn.jpg');

        $this->artisan(ClearAssets::class)
            ->expectsOutput('Found 2 unused assets, taking up 0.10 MB of storage.')
            ->expectsChoice('What would you like to do?', ClearAssets::CMD_DELETE_ALL, ClearAssets::$choices)
            ->expectsOutput('Removing ankara.jpg')
            ->expectsOutput('Removing tallinn.jpg');

        $this->assertEquals(0, $this->assetContainer->listContents()->count());
    }

    /**
     * @test
     */
    public function it_confirms_deletion_one_by_one()
    {
        $this->createAsset('ankara.jpg');
        $this->createAsset('tallinn.jpg');

        $this->artisan(ClearAssets::class)
            ->expectsOutput('Found 2 unused assets, taking up 0.10 MB of storage.')
            ->expectsChoice('What would you like to do?', ClearAssets::CMD_DELETE_BY_CHOICE, ClearAssets::$choices)
            ->expectsQuestion('Delete "ankara.jpg" ?', true)
            ->expectsOutput('Removing ankara.jpg')
            ->expectsQuestion('Delete "tallinn.jpg" ?', false)
            ->doesntExpectOutput('Removing tallinn.jpg');

        $this->assertEquals(1, $this->assetContainer->listContents()->count());
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
