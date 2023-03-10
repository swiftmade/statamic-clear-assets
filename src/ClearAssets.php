<?php

namespace Swiftmade\StatamicClearAssets;

use Statamic\Assets\Asset;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Assets\AssetCollection;
use Symfony\Component\Process\Process;

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
        $unusedAssets = $this->filterUnused(Asset::all());

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
        // We're going to recursively scan these folders for matches
        $scanDirectories = config('statamic-clear-assets.scan_folders', ['content']);

        return $assets
            ->filter(function ($asset) use ($scanDirectories) {
                // Grep flags:
                // -r recursive
                // -F fixed string
                // -w whole word
                // -l list filenams
                $grep = new Process(
                    array_merge(
                        ['grep', '-rFwl', $asset->path()],
                        $scanDirectories
                    ),
                    base_path()
                );

                $grep->run();

                return empty($grep->getOutput());
            })
            ->values();
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
}
