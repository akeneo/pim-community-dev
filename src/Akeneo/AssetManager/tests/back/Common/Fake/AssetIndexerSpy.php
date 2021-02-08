<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetIndexerInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use PHPUnit\Framework\Assert;

/**
 * Asset indexer spy used for tests to check if it has been called with the right parameters.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetIndexerSpy implements AssetIndexerInterface
{
    /** @var array */
    private $indexedAssetFamilies = [];

    /** @var bool */
    private $isIndexRefreshed = false;

    /**
     * Indexes all assets belonging to the given asset family.
     */
    public function indexByAssetFamily(AssetFamilyIdentifier $assetFamilyIdentifier): void
    {
        $this->indexedAssetFamilies[] = $assetFamilyIdentifier->normalize();
    }

    public function refresh(): void
    {
        $this->isIndexRefreshed = true;
    }

    public function assertAssetFamilyNotIndexed(string $assetFamilyIdentifier)
    {
        Assert::assertNotContains($assetFamilyIdentifier, $this->indexedAssetFamilies);
    }

    public function assertAssetFamilyIndexed(string $assetFamilyIdentifier)
    {
        Assert::assertContains($assetFamilyIdentifier, $this->indexedAssetFamilies);
    }

    public function assertIndexRefreshed()
    {
        Assert::assertTrue($this->isIndexRefreshed, 'Index should be refreshed');
    }

    public function index(AssetIdentifier $assetIdentifier)
    {
    }

    public function indexByAssetIdentifiers(array $assetIdentifiers)
    {
    }

    /**
     * Remove a asset from the index
     */
    public function removeAssetByAssetFamilyIdentifierAndCode(
        string $assetFamilyIdentifier,
        string $assetCode
    ) {
    }

    public function removeByAssetFamilyIdentifierAndCodes(
        string $assetFamilyIdentifier,
        array $assetCodes
    ) {
    }
}
