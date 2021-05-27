<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Persistence\Sql\Asset\RefreshAssets;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\RefreshAssets\FindAllAssetIdentifiers;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class FindAllAssetIdentifiersTest extends SqlIntegrationTestCase
{
    private FindAllAssetIdentifiers $allAssetIdentifiers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->allAssetIdentifiers = $this->get('akeneo_assetmanager.infrastructure.persistence.cli.all_assets_identifiers');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_returns_no_asset_identifiers(): void
    {
        $this->assertEmpty(iterator_to_array($this->allAssetIdentifiers->fetch()));
    }

    /**
     * @test
     */
    public function it_returns_all_asset_identifiers(): void
    {
        $this->createAssets(['red', 'blue']);
        $this->assertAssetsIdentifiers(['red', 'blue'], iterator_to_array($this->allAssetIdentifiers->fetch()));
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function createAssets(array $assetCodes): void
    {
        /** @var AssetFamilyRepositoryInterface $assetFamilyRepository */
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetFamilyRepository->create(
            AssetFamily::create(
                $assetFamilyIdentifier,
                [
                    'fr_FR' => 'Concepteur',
                    'en_US' => 'Designer',
                ],
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            )
        );

        foreach ($assetCodes as $assetCode) {
            $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
            $assetRepository->create(
                Asset::create(
                    AssetIdentifier::fromString($assetCode),
                    $assetFamilyIdentifier,
                    AssetCode::fromString($assetCode),
                    ValueCollection::fromValues([])
                )
            );
        }
    }

    /**
     * @param array $expectedIdentifiers
     * @param AssetIdentifier[] $actualIdentifiers
     */
    private function assertAssetsIdentifiers(array $expectedIdentifiers, array $actualIdentifiers): void
    {
        $normalizedIdentifiers = array_map(fn (AssetIdentifier $identifier) => $identifier->normalize(), $actualIdentifiers);
        sort($normalizedIdentifiers);
        sort($expectedIdentifiers);
        $this->assertEquals($expectedIdentifiers, $normalizedIdentifiers);
    }
}
