<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration\Persistence\InMemory;

use Akeneo\AssetManager\Common\Fake\InMemoryAssetRepository;
use Akeneo\AssetManager\Common\Fake\InMemoryFindAssetIdentifiersByAssetFamily;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class InMemoryFindIdentifiersByAssetFamilyTest extends TestCase
{
    private InMemoryFindAssetIdentifiersByAssetFamily $query;

    private InMemoryAssetRepository $assetRepository;

    /**
     * @test
     */
    public function it_returns_empty_iterable_if_asset_family_does_not_exist()
    {
        $this->assertIdentifiers(AssetFamilyIdentifier::fromString('non_existing_family'), []);
    }

    /**
     * @test
     */
    public function it_returns_the_asset_identifiers_of_an_asset_family()
    {
        $thronesAssetIdentifiers = [];
        foreach (['starck', 'lannister', 'tyrell', 'martell', 'tully'] as $assetCode) {
            $thronesAssetIdentifiers[] = $this->createAsset('thrones', $assetCode);
        }
        $packshotAssetIdentifiers = [];
        foreach (['first', 'second', 'third'] as $assetCode) {
            $packshotAssetIdentifiers[] = $this->createAsset('packshot', $assetCode);
        }

        $this->assertIdentifiers(AssetFamilyIdentifier::fromString('thrones'), $thronesAssetIdentifiers);
        $this->assertIdentifiers(AssetFamilyIdentifier::fromString('packshot'), $packshotAssetIdentifiers);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->assetRepository = new InMemoryAssetRepository(new EventDispatcher());
        $this->query = new InMemoryFindAssetIdentifiersByAssetFamily(
            $this->assetRepository
        );
    }

    private function createAsset(string $assetFamilyIdentifier, string $assetCode): AssetIdentifier
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
        $assetCode = AssetCode::fromString($assetCode);
        $assetIdentifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $assetCode);

        $this->assetRepository->create(
            Asset::create(
                $assetIdentifier,
                $assetFamilyIdentifier,
                $assetCode,
                ValueCollection::fromValues([])
            )
        );

        return $assetIdentifier;
    }

    private function assertIdentifiers(AssetFamilyIdentifier $assetFamilyidentifier, array $expectedIdentifiers)
    {
        $actualIdentifiers = $this->query->find($assetFamilyidentifier);
        Assert::assertIsIterable($actualIdentifiers);

        $expectedAssetIdentifiers = array_map(
            fn (string $identifier): AssetIdentifier => AssetIdentifier::fromString($identifier),
            $expectedIdentifiers
        );

        Assert::assertEqualsCanonicalizing($expectedAssetIdentifiers, iterator_to_array($actualIdentifiers));
    }
}
