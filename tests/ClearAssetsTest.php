<?php

namespace Swiftmade\StatamicClearAssets\Tests;

use Swiftmade\StatamicClearAssets\ClearAssets;

class ClearAssetsTest extends TestCase
{
    /**
     * @test
     */
    public function it_registers_the_command()
    {
        $this->artisan(ClearAssets::class);
    }
}
