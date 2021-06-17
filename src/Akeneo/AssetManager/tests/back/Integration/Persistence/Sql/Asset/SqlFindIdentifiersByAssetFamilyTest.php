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

namespace Akeneo\AssetManager\Integration\Persistence\Sql\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\SqlFindAssetIdentifiersByAssetFamily;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

class SqlFindIdentifiersByAssetFamilyTest extends SqlIntegrationTestCase
{
    private SqlFindAssetIdentifiersByAssetFamily $findIdentifiersByAssetFamily;

    private AssetRepositoryInterface $assetRepository;

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
    public function it_returns_empty_iterable_if_asset_family_does_not_have_any_asset()
    {
        $infos = $this->fixturesLoader->assetFamily('designer')->load();
        $assetfamilyIdentifier = $infos['asset_family']->getIdentifier();

        $this->assertIdentifiers($assetfamilyIdentifier, []);
    }

    /**
     * @test
     */
    public function it_returns_the_asset_identifiers_of_an_asset_family()
    {
        $this->fixturesLoader->assetFamily('thrones')->load();
        $thronesAssetIdentifiers = [];
        foreach (['starck', 'lannister', 'tyrell', 'martell', 'tully'] as $assetCode) {
            $thronesAssetIdentifiers[] = $this->createAsset('thrones', $assetCode);
        }

        $this->fixturesLoader->assetFamily('packshot')->load();
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
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
        $this->findIdentifiersByAssetFamily = $this->get(
            'akeneo_assetmanager.infrastructure.persistence.query.find_identifiers_by_asset_family'
        );
        $this->assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
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
        $actualIdentifiers = $this->findIdentifiersByAssetFamily->find($assetFamilyidentifier);
        Assert::assertIsIterable($actualIdentifiers);

        $expectedAssetIdentifiers = array_map(
            fn (string $identifier): AssetIdentifier => AssetIdentifier::fromString($identifier),
            $expectedIdentifiers
        );

        Assert::assertEqualsCanonicalizing($expectedAssetIdentifiers, iterator_to_array($actualIdentifiers));
    }
}
