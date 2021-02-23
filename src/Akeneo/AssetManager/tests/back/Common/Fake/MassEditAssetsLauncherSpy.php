<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Application\Asset\MassDeleteAssets\MassDeleteAssetsLauncherInterface;
use Akeneo\AssetManager\Application\Asset\MassEditAssets\MassEditAssetsLauncherInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use PHPUnit\Framework\Assert;

class MassEditAssetsLauncherSpy implements MassEditAssetsLauncherInterface
{
    private ?AssetFamilyIdentifier $assetFamilyIdentifier = null;
    private ?AssetQuery $assetQuery = null;
    private ?array $editValueCommands = null;

    public function launchForAssetFamily(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetQuery $assetQuery,
        array $editValueCommands
    ): void {
        $this->assetFamilyIdentifier = $assetFamilyIdentifier;
        $this->assetQuery = $assetQuery;
        $this->editValueCommands = $editValueCommands;
    }

    public function assertHasNoRun()
    {
        Assert::assertNull($this->assetFamilyIdentifier, 'Expected no mass edit run');
        Assert::assertNull($this->assetQuery, 'Expected no mass edit run');
        Assert::assertNull($this->editValueCommands, 'Expected no mass edit run');
    }

    public function hasLaunchedMassEdit(string $assetFamilyIdentifier, AssetQuery $assetQuery, array $editValueCommands)
    {
        Assert::assertEquals(
            $assetFamilyIdentifier,
            (string) $this->assetFamilyIdentifier,
            sprintf(
                'Expected mass edit launcher to be launched with %s',
                $assetFamilyIdentifier
            )
        );

        Assert::assertEquals(
            $assetQuery->normalize(),
            $this->assetQuery->normalize(),
            sprintf(
                'Expected mass edit launcher to be launched with %s',
                json_encode($assetQuery)
            )
        );

        Assert::assertCount(count($editValueCommands), $this->editValueCommands);
        array_map(function ($expectedEditValueCommand, $actualEditValueCommand) {
            Assert::assertEquals(
                $expectedEditValueCommand->normalize(),
                $actualEditValueCommand->normalize()
            );
        }, $editValueCommands, $this->editValueCommands);
    }
}
