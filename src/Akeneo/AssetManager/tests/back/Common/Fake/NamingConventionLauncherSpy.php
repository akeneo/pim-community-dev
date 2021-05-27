<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\NamingConventionLauncherInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use PHPUnit\Framework\Assert;

class NamingConventionLauncherSpy implements NamingConventionLauncherInterface
{
    private array $launches = [];

    public function launchForAllAssetFamilyAssets(AssetFamilyIdentifier $assetFamilyIdentifier): void
    {
        $id = sprintf('run_%s', uniqid());

        $this->launches[$id] = (string)$assetFamilyIdentifier;
    }

    public function assertHasNoJob()
    {
        Assert::assertEmpty($this->launches, 'At least a job was launched');
    }

    public function assertHasJobForAssetFamily(string $assetFamilyIdentifier)
    {
        Assert::assertContainsEquals(
            $assetFamilyIdentifier,
            $this->launches,
            sprintf('There was no job for "%s"', $assetFamilyIdentifier)
        );
    }
}
