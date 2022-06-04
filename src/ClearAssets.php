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

    private $continue = false;

    public function handle()
    {
        $unusedAssets = $this->filterUnused(Asset::all());

        if ($unusedAssets->isEmpty()) {
            return $this->info('No unused assets found.');
        }

        $unusedAssets
            ->tap(fn ($assets) => $this->comment(
                sprintf(
                    'Found %d unused %s, taking up %.2f MB of storage.',
                    $assets->count(),
                    Str::plural('asset', $assets->count()),
                    $this->sizeInMegabytes($assets)
                )
            ))
            ->tap(fn () => $this->continue = $this->confirm('Delete these files?'))
            ->when(
                $this->continue,
                fn ($assets) => $assets->each(fn ($asset) => $this->removeAsset($asset))
            );
    }

    private function sizeInMegabytes(AssetCollection $assets)
    {
        return $assets->sum(fn (Asset $asset) => $asset->size()) / 1024 / 1024;
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
}
