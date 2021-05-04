<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Persistence\Sql\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\SqlFindSearchableAssets;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindSearchableAssetsTest extends SqlIntegrationTestCase
{
    /** @var SqlFindSearchableAssets */
    private $findSearchableAssets;

    public function setUp(): void
    {
        parent::setUp();

        $this->findSearchableAssets = $this->get('akeneo_assetmanager.infrastructure.search.elasticsearch.asset.query.find_searchable_assets');
        $this->resetDB();
        $this->loadAssetFamilyAndAttributes();
    }

    /**
     * @test
     */
    public function it_returns_null_if_it_does_not_find_by_asset_identifier()
    {
        Assert::assertNull(
            $this->findSearchableAssets->byAssetIdentifier(AssetIdentifier::fromString('wrong_identifier'))
        );
    }

    /**
     * @test
     */
    public function it_returns_a_searchable_asset_item()
    {
        $searchableAsset = $this->findSearchableAssets->byAssetIdentifier(AssetIdentifier::fromString('stark_designer_fingerprint'));

        $labelIdentifier = $this->getAttributeAsLabelIdentifier('designer');
        Assert::assertEquals('stark_designer_fingerprint', $searchableAsset->identifier);
        Assert::assertEquals('stark', $searchableAsset->code);
        Assert::assertEquals('designer', $searchableAsset->assetFamilyIdentifier);
        Assert::assertSame(['fr_FR' => 'Philippe Starck'], $searchableAsset->labels);
        Assert::assertSame([
            'name'                      => [
                'data' => 'Philippe stark',
                'locale' => null,
                'channel' => null,
                'attribute' => 'name',
            ],
            $labelIdentifier . '_fr_FR' => [
                'data'      => 'Philippe Starck',
                'locale'    => 'fr_FR',
                'channel'   => null,
                'attribute' => $labelIdentifier,
            ],
        ], $searchableAsset->values);
    }


    /**
     * @test
     */
    public function it_returns_empty_array_if_it_does_not_find_by_asset_identifiers()
    {
        $searchableAssets = $this->findSearchableAssets->byAssetIdentifiers([AssetIdentifier::fromString('wrong_identifier')]);
        Assert::assertEquals(0, iterator_count($searchableAssets));
    }

    /**
     * @test
     */
    public function it_returns_searchable_asset_items_by_asset_identifiers()
    {
        $searchableAssets = $this->findSearchableAssets->byAssetIdentifiers([
            AssetIdentifier::fromString('stark_designer_fingerprint')
        ]);

        $labelIdentifier = $this->getAttributeAsLabelIdentifier('designer');
        $searchableAssets = iterator_to_array($searchableAssets);
        Assert::assertCount(1, $searchableAssets);
        $searchableAsset = current($searchableAssets);
        Assert::assertEquals('stark_designer_fingerprint', $searchableAsset->identifier);
        Assert::assertEquals('stark', $searchableAsset->code);
        Assert::assertEquals('designer', $searchableAsset->assetFamilyIdentifier);
        Assert::assertSame(['fr_FR' => 'Philippe Starck'], $searchableAsset->labels);
        Assert::assertSame(
            [
                'name'                      => [
                    'data'      => 'Philippe stark',
                    'locale'    => null,
                    'channel'   => null,
                    'attribute' => 'name',
                ],
                $labelIdentifier . '_fr_FR' => [
                    'data'      => 'Philippe Starck',
                    'locale'    => 'fr_FR',
                    'channel'   => null,
                    'attribute' => $labelIdentifier,
                ],
            ],
            $searchableAsset->values
        );
    }

    /**
     * @test
     */
    public function it_returns_null_if_it_does_not_find_by_asset_family_identifier()
    {
        $items = $this->findSearchableAssets->byAssetFamilyIdentifier(
            AssetFamilyIdentifier::fromString('wrong_asset_family')
        );
        $count = 0;
        foreach ($items as $searchItem) {
            $count++;
        }
        Assert::assertEquals(0, $count, 'There was some searchable item found. expected 0.');
    }

    /**
     * @test
     */
    public function it_returns_searchable_asset_items_by_asset_family()
    {
        $searchableAssets = $this->findSearchableAssets->byAssetFamilyIdentifier(
            AssetFamilyIdentifier::fromString('designer')
        );

        $labelIdentifier = $this->getAttributeAsLabelIdentifier('designer');
        $searchableAssets = iterator_to_array($searchableAssets);
        Assert::assertCount(1, $searchableAssets);
        $searchableAsset = current($searchableAssets);
        Assert::assertEquals('stark_designer_fingerprint', $searchableAsset->identifier);
        Assert::assertEquals('stark', $searchableAsset->code);
        Assert::assertEquals('designer', $searchableAsset->assetFamilyIdentifier);
        Assert::assertSame(['fr_FR' => 'Philippe Starck'], $searchableAsset->labels);
        Assert::assertSame(
            [
                'name'                      => [
                    'data'      => 'Philippe stark',
                    'locale'    => null,
                    'channel'   => null,
                    'attribute' => 'name',
                ],
                $labelIdentifier . '_fr_FR' => [
                    'data'      => 'Philippe Starck',
                    'locale'    => 'fr_FR',
                    'channel'   => null,
                    'attribute' => $labelIdentifier,
                ],
            ],
            $searchableAsset->values
        );
    }

    private function loadAssetFamilyAndAttributes(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
        $designer = $this->createDesigner();
        $this->createStark($designer);

        $brand = $this->createBrand();
        $this->createFatboy($brand);
    }

    /**
     * @return AssetFamily
     *
     */
    private function createDesigner(): AssetFamily
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamilyRepository->create(
            AssetFamily::create(
                AssetFamilyIdentifier::fromString('designer'),
                [
                    'fr_FR' => 'Concepteur',
                    'en_US' => 'Designer',
                ],
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            )
        );
        $result = $assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString('designer'));

        return $result;
    }

    private function createBrand(): AssetFamily
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamilyRepository->create(
            AssetFamily::create(
                AssetFamilyIdentifier::fromString('brand'),
                [
                    'fr_FR' => 'Marque',
                    'en_US' => 'Brand',
                ],
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            )
        );
        $result = $assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString('designer'));

        return $result;
    }

    private function createStark(AssetFamily $designer): void
    {
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString('stark_designer_fingerprint'),
                AssetFamilyIdentifier::fromString('designer'),
                AssetCode::fromString('stark'),
                ValueCollection::fromValues([
                    Value::create(
                        $designer->getAttributeAsLabelReference()->getIdentifier(),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        TextData::fromString('Philippe Starck')
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('name'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        TextData::fromString('Philippe stark')
                    )
                ])
            )
        );
    }

    private function createFatboy(AssetFamily $brand): void
    {
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString('fatboy_brand_fingerprint'),
                AssetFamilyIdentifier::fromString('brand'),
                AssetCode::fromString('fatboy'),
                ValueCollection::fromValues([
                    Value::create(
                        $brand->getAttributeAsLabelReference()->getIdentifier(),
                        ChannelReference::noReference(),
                        LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                        TextData::fromString('Fatboy is a new branding company')
                    ),
                    Value::create(
                        AttributeIdentifier::fromString('name'),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        TextData::fromString('Fatboy inc.')
                    )
                ])
            )
        );
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function getAttributeAsLabelIdentifier($identifier): string
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamily = $assetFamilyRepository->getByIdentifier(AssetFamilyIdentifier::fromString($identifier));
        $result = $assetFamily->getAttributeAsLabelReference()->normalize();

        return $result;
    }
}
