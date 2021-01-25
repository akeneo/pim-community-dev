<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Application\Asset\MassDeleteAssets\MassDeleteAssetsLauncherInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use PHPUnit\Framework\Assert;

class MassDeleteAssetsLauncherSpy implements MassDeleteAssetsLauncherInterface
{
    private ?AssetFamilyIdentifier $assetFamilyIdentifier;
    private ?AssetQuery $assetQuery;
    private array $launches = [];

    public function launchForAssetFamilyAndQuery(AssetFamilyIdentifier $assetFamilyIdentifier, AssetQuery $assetQuery): void
    {
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->assetQuery = $assetQuery;
    }

    public function hasLaunchedMassDelete(string $assetFamilyIdentifier, AssetQuery $assetQuery)
    {
        $allLaunches = [];
        foreach ($this->launches as $launches) {
            $allLaunches = array_merge($allLaunches, $launches);
        }

        Assert::assertEquals(
            $assetFamilyIdentifier,
            (string) $this->assetFamilyIdentifier,
            sprintf(
                'Expected mass delete launcher to be launched with %s',
                $assetFamilyIdentifier
            )
        );

        Assert::assertEquals(
            $assetQuery->normalize(),
            $this->assetQuery->normalize(),
            sprintf(
                'Expected mass delete launcher to be launched with %s',
                json_encode($assetQuery)
            )
        );
    }
}
