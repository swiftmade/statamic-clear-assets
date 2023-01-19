<?php

namespace Swiftmade\StatamicClearAssets;

use Statamic\Assets\Asset;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Illuminate\Support\Facades\File;
use Statamic\Assets\AssetCollection;
use Statamic\Facades\AssetContainer;
use function config;
use function count;
use function in_array;

class ClearAssets extends Command
{
    use RunsInPlease;

    protected $name = 'statamic:assets:clear';

    protected $description = "Delete unused assets.";

    private $choice;

    const CMD_DELETE_ALL = 'Delete all';
    const CMD_DELETE_BY_CHOICE = 'Choose what to delete';
    const CMD_EXIT = 'Don\'t do anything';

    public static $choices = [
        self::CMD_DELETE_ALL,
        self::CMD_DELETE_BY_CHOICE,
        self::CMD_EXIT,
    ];

    public function handle()
    {
        $unusedAssets = $this->filterUnused(
            $this->assetsFromContainers(
                config('statamic.clear-assets.containers')
            )
        );

        if ($unusedAssets->isEmpty()) {
            return $this->info('No unused assets found.');
        }

        $unusedAssets
            ->tap(fn ($assets) => $this->listAssets($assets))
            ->tap(fn ($assets) => $this->comment(
                sprintf(
                    'Found %d unused %s, taking up %s of storage.',
                    $assets->count(),
                    Str::plural('asset', $assets->count()),
                    $this->readableFilesize(
                        $assets->sum->size()
                    )
                )
            ))
            ->tap(fn () => $this->presentChoices())
            ->when(
                $this->choice === self::CMD_DELETE_ALL,
                fn ($assets) => $assets->each(fn ($asset) => $this->removeAsset($asset))
            )
            ->when(
                $this->choice === self::CMD_DELETE_BY_CHOICE,
                fn ($assets) => $assets->each(function ($asset) {
                    if ($this->confirm('Delete "' . $asset->path() . '" ?')) {
                        $this->removeAsset($asset);
                    }
                })
            );
    }

    private function listAssets(AssetCollection $assets)
    {
        $this->table(
            ['Asset', 'Size'],
            $assets->map(
                fn ($asset) => [
                    $asset->path(),
                    $this->readableFilesize($asset->size()),
                ]
            )
        );
    }

    private function filterUnused(AssetCollection $assets)
    {
        collect(File::allFiles(base_path('content')))->each(function ($contentFile) use ($assets) {
            $contents = file_get_contents($contentFile);

            $assets->each(function ($asset, $index) use ($contents, $assets) {
                // If asset is used in content, then remove it from unused list.
                if (strpos($contents, $asset->path()) !== false) {
                    $assets->forget($index);
                }
            });
        });

        return $assets->values();
    }

    private function removeAsset(Asset $asset)
    {
        $this->line('Removing ' . $asset->path());
        $asset->delete();
    }

    private function presentChoices()
    {
        $this->choice = $this->choice(
            'What would you like to do?',
            self::$choices,
            0
        );
    }

    private function readableFilesize($bytes)
    {
        return sprintf('%.2f MB', $bytes / 1024 / 1024);
    }

    private function assetsFromContainers($containers)
    {
        if (count($containers) === 0) {
            return Asset::all();
        }

        $assets = AssetContainer::all()
            ->filter(fn ($container) => in_array($container->handle(), $containers))
            ->flatMap->assets();

        return AssetCollection::make($assets);
    }
}
