<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Application\Asset\LinkAssets\ProductLinkRuleLauncherInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use PHPUnit\Framework\Assert;

class ProductLinkRuleLauncherSpy implements ProductLinkRuleLauncherInterface
{
    /** @var array */
    private $launches;

    public function launch(AssetFamilyIdentifier $assetFamilyIdentifier, array $assetCodes): void
    {
        $runId = sprintf('run_%s', uniqid());

        foreach ($assetCodes as $assetCode) {
            $this->launches[$runId][] = $this->fingerprint($assetFamilyIdentifier->normalize(), $assetCode->normalize());
        }
    }

    public function assertHasRunForAsset(string $assetFamilyIdentifier, string $assetCode): void
    {
        $allLaunches = [];
        foreach ($this->launches as $launches) {
            $allLaunches = array_merge($allLaunches, $launches);
        }

        Assert::assertContains(
            $this->fingerprint($assetFamilyIdentifier, $assetCode),
            $allLaunches,
            sprintf(
                'Expected rules launcher to run for asset family %s and asset code %s',
                $assetFamilyIdentifier,
                $assetCode
            )
        );
    }

    /**
     * This method checks that all $assetCodes given has been run in the same job launch, it fails if not.
     */
    public function assertHasRunForAssetsInSameLaunch(string $assetFamilyIdentifier, array $assetCodes): void
    {
        $fingerprintedCodes = array_map(function ($assetCode) use ($assetFamilyIdentifier) {
            return $this->fingerprint($assetFamilyIdentifier, $assetCode);
        }, $assetCodes);

        $allPresent = false;

        foreach ($this->launches as $launches) {
            if (!$allPresent) {
                $allPresent = count(array_intersect($launches, $fingerprintedCodes)) === count($assetCodes);
            }
        }

        Assert::assertTrue($allPresent, sprintf(
            'Expected assets "%s" to be run in the same launch for asset family %s',
            implode(', ', $assetCodes),
            $assetFamilyIdentifier
        ));
    }

    private function fingerprint(string $assetFamilyIdentifier, string $assetCode): string
    {
        return sprintf('%s_%s', $assetFamilyIdentifier, $assetCode);
    }
}
