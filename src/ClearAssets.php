<?php

namespace Swiftmade\StatamicClearAssets;

use Statamic\Assets\Asset;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Illuminate\Support\Facades\File;
use Statamic\Assets\AssetCollection;

class ClearAssets extends Command
{
    use RunsInPlease;

    protected $name = 'statamic:assets:clear';

    protected $description = "Delete unused assets.";

    private $choice;

    const CMD_DELETE_ALL = 'Delete All';
    const CMD_LIST = 'List';

    public function handle()
    {
        $unusedAssets = $this->filterUnused(Asset::all());

        if ($unusedAssets->isEmpty()) {
            return $this->info('No unused assets found.');
        }

        $unusedAssets
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
                $this->choice === self::CMD_LIST,
                fn ($assets) => $this->table(
                    ['Asset', 'Size'],
                    $assets->map(
                        fn ($asset) => [
                            $asset->path(),
                            $this->readableFilesize($asset->size()),
                        ]
                    )
                ),
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
        $choices = [
            self::CMD_DELETE_ALL,
            self::CMD_LIST,
        ];

        $this->choice = $this->choice(
            'What would you like to do?',
            $choices,
            0
        );
    }

    private function readableFilesize($bytes)
    {
        return sprintf('%.2f', $bytes / 1024 / 1024) . ' MB';
    }
}
