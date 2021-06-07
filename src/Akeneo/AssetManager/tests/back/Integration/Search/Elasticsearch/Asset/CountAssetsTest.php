<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Query\Asset\CountAssetsInterface;
use Akeneo\AssetManager\Integration\SearchIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class CountAssetsTest extends SearchIntegrationTestCase
{
    private CountAssetsInterface $countAssets;

    private ?AssetFamilyIdentifier $emptyAssetFamilyIdentifier = null;

    private ?AssetFamilyIdentifier $assetFamilyIdentifiersWithAssets = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->countAssets = $this->get('akeneo_assetmanager.infrastructure.search.elasticsearch.asset.query.count_assets');

        $this->resetDB();
        $this->createAssetFamilyWithAttributes();
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_counts_the_number_of_assets_for_an_asset_family()
    {
        Assert::assertThat(
            $this->countAssets->forAssetFamily($this->emptyAssetFamilyIdentifier),
            Assert::isEmpty()
        );
        Assert::assertThat(
            $this->countAssets->forAssetFamily($this->assetFamilyIdentifiersWithAssets),
            Assert::equalTo(2)
        );
    }

    private function createAssetFamilyWithAttributes(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $this->emptyAssetFamilyIdentifier = AssetFamilyIdentifier::fromString('brand');
        $assetFamilyRepository->create(
            AssetFamily::create(
                $this->emptyAssetFamilyIdentifier,
                [],
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            )
        );
        $this->assetFamilyIdentifiersWithAssets = AssetFamilyIdentifier::fromString('designer');
        $assetFamilyRepository->create(
            AssetFamily::create(
                $this->assetFamilyIdentifiersWithAssets,
                [],
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            )
        );

        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString('starck_designer'),
                $this->assetFamilyIdentifiersWithAssets,
                AssetCode::fromString('stark'),
                ValueCollection::fromValues([])
            )
        );
        $assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString('kartell_designer'),
                $this->assetFamilyIdentifiersWithAssets,
                AssetCode::fromString('kartell'),
                ValueCollection::fromValues([])
            )
        );
        $this->flushAssetsToIndexCache();
        $this->get('akeneo_assetmanager.infrastructure.search.elasticsearch.asset_indexer')->refresh();
    }
}
