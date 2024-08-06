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
    public function it_can_exit_without_doing_anything()
    {
        $this->createAsset('tallinn.jpg');
        $this->createAsset('ankara.jpg');
        $this->useAsset('ankara.jpg');

        $this->artisan(ClearAssets::class)
            ->expectsTable(['Asset', 'Size'], [
                ['tallinn.jpg', '0.06 MB'],
            ])
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
    public function it_ignores_containers()
    {
        $this->createAsset('ankara.jpg', 'social_images');
        $this->createAsset('tallinn.jpg', 'favicons');

        $this->artisan(ClearAssets::class)->expectsOutput('No unused assets found.');
    }

    /**
     * @test
     */
    public function it_ignores_filenames()
    {
        config(['statamic-clear-assets.ignore_filenames' => ['ankara*']]);

        $this->createAsset('ankara.jpg');

        $this->artisan(ClearAssets::class)->expectsOutput('No unused assets found.');
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

        $this->assertContainerFileCount('assets', 0);
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

        $this->assertContainerFileCount('assets', 1);
    }

    /**
     * @test
     */
    public function it_skips_confirmation_in_no_interaction_mode()
    {
        $this->createAsset('ankara.jpg');
        $this->createAsset('tallinn.jpg');

        $this->artisan(ClearAssets::class, ['--force' => true])
            ->expectsOutput('Removing ankara.jpg')
            ->expectsOutput('Removing tallinn.jpg');

        $this->assertContainerFileCount('assets', 0);
    }

    private function createAsset($filename, $container = 'assets')
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test_' . $filename);
        copy(__DIR__ . '/fixtures/' . $filename, $tmpFile);

        $file = new UploadedFile(
            $tmpFile,
            $filename,
            'image/jpeg',
            null,
            true
        );

        $this->saveFileToContainer(
            $file,
            $this->getAssetContainer($container)
        );
    }

    private function useAsset($filename)
    {
        $files = base_path('content');
        file_put_contents($files . '/test.yaml', $filename);
    }
}
