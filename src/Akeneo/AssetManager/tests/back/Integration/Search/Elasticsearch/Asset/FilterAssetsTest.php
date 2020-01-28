<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\OptionCollectionData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\OptionData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Asset\FindIdentifiersForQueryInterface;
use Akeneo\AssetManager\Domain\Query\Asset\IdentifiersForQueryResult;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Integration\SearchIntegrationTestCase;

/**
 * Testing the search usecases to filter on attributes:
 * - option
 * - option_collection
 * - asset
 * - asset_collection
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class FilterAssetsTest extends SearchIntegrationTestCase
{
    /** @var FindIdentifiersForQueryInterface */
    private $findIdentifiersForQuery;

    /** @var AssetFamilyIdentifier */
    private $assetFamilyIdentifier;

    /** @var AttributeIdentifier */
    private $attributeIdentifier;

    public function setUp(): void
    {
        parent::setUp();

        $this->findIdentifiersForQuery = $this->get('akeneo_assetmanager.infrastructure.search.elasticsearch.asset.query.find_identifiers_for_query');

        $this->resetDB();
        $this->loadAssetFamily();
    }

    /**
     * @test
     */
    public function it_finds_all_assets_having_a_simple_option()
    {
        $this->loadAttributeWithOptions('main_color_designers_fingerprint', ['red', 'blue']);
        $this->loadAssetHavingOption('stark', 'red');
        $this->get('akeneo_assetmanager.client.asset')->refreshIndex();

        $searchResultEcommerceEnUS = $this->searchAssets(
            'ecommerce', 'en_US', ['main_color_designers_fingerprint' => ['red']]
        );
        $searchResultMobileFrFR = $this->searchAssets(
            'mobile', 'fr_FR', ['main_color_designers_fingerprint' => ['red']]
        );
        $emptySearchResult = $this->searchAssets(
            'ecommerce', 'en_US', ['main_color_designers_fingerprint' => ['blue']]
        );

        $this->assertContains('stark', $searchResultEcommerceEnUS->normalize()['identifiers']);
        $this->assertContains('stark', $searchResultMobileFrFR->normalize()['identifiers']);
        $this->assertEmpty($emptySearchResult->normalize()['identifiers']);
    }

    /**
     * @test
     */
    public function it_finds_all_assets_having_a_simple_option_on_a_specific_channel_and_locale()
    {
        $this->loadAttributeWithOptions('main_color_designers_fingerprint', ['red', 'blue'], true, true);
        $this->loadAssetHavingOption('stark', 'red', 'ecommerce', 'en_US');
        $this->get('akeneo_assetmanager.client.asset')->refreshIndex();

        $searchResultEcommerceEnUS = $this->searchAssets(
            'ecommerce', 'en_US', ['main_color_designers_fingerprint' => ['red']]
        );
        $identifiers = $searchResultEcommerceEnUS->normalize()['identifiers'];
        $this->assertContains('stark', $identifiers);

        $this->expectException('\LogicException');
        $this->searchAssets(
            'mobile', 'fr_FR', ['main_color_designers_fingerprint' => ['red']]
        );
    }

    /**
     * @test
     */
    public function it_searches_all_assets_having_multiple_options()
    {
        $this->loadAttributeWithOptionCollection('main_color_designers_fingerprint', ['red', 'blue', 'green']);
        $this->loadAssetHavingOptionCollection('stark', ['red', 'blue']);
        $this->get('akeneo_assetmanager.client.asset')->refreshIndex();

        $searchResultEcommerceEnUS = $this->searchAssets(
            'ecommerce', 'en_US', ['main_color_designers_fingerprint' => ['red', 'blue']]
        );
        $searchResultMobileFrFR = $this->searchAssets(
            'mobile', 'fr_FR', ['main_color_designers_fingerprint' => ['blue', 'green']]
        );
        $emptySearchResult = $this->searchAssets(
            'ecommerce', 'en_US', ['main_color_designers_fingerprint' => ['green']]
        );

        $this->assertContains('stark', $searchResultEcommerceEnUS->normalize()['identifiers']);
        $this->assertContains('stark', $searchResultMobileFrFR->normalize()['identifiers']);
        $this->assertEmpty($emptySearchResult->normalize()['identifiers']);
    }

    /**
     * @test
     */
    public function it_searches_all_assets_having_multiple_options_on_a_specific_channel_and_locale()
    {
        $this->loadAttributeWithOptionCollection('main_color_designers_fingerprint', ['red', 'blue', 'green'], true, true);
        $this->loadAssetHavingOptionCollection('stark', ['red', 'blue'], 'ecommerce', 'en_US');
        $this->get('akeneo_assetmanager.client.asset')->refreshIndex();

        $searchResultEcommerceEnUS = $this->searchAssets(
            'ecommerce', 'en_US', ['main_color_designers_fingerprint' => ['red', 'blue']]
        );
        $identifiers = $searchResultEcommerceEnUS->normalize()['identifiers'];
        $this->assertContains('stark', $identifiers);

        $this->expectException('\LogicException');
        $this->searchAssets(
            'mobile', 'fr_FR', ['main_color_designers_fingerprint' => ['red']]
        );
    }

    /**
     * @test
     */
    public function it_searches_all_assets_linked_to_another_asset()
    {
        $this->loadLinkedAssets('city', ['paris']);
        $this->loadLinkedAssetAttribute('main_city_designers_fingerprint', 'city');
        $this->loadAssetHavingLinkedAsset('stark', 'paris');
        $this->get('akeneo_assetmanager.client.asset')->refreshIndex();

        $searchResultEcommerceEnUS = $this->searchAssets(
            'ecommerce', 'en_US', ['main_city_designers_fingerprint' => ['paris']]
        );
        $searchResultMobileFrFR = $this->searchAssets(
            'mobile', 'fr_FR', ['main_city_designers_fingerprint' => ['paris']]
        );
        $emptySearchResult = $this->searchAssets(
            'ecommerce', 'en_US', ['main_city_designers_fingerprint' => ['london']]
        );

        $this->assertContains('stark', $searchResultEcommerceEnUS->normalize()['identifiers']);
        $this->assertContains('stark', $searchResultMobileFrFR->normalize()['identifiers']);
        $this->assertEmpty($emptySearchResult->normalize()['identifiers']);
    }

    /**
     * @test
     */
    public function it_searches_all_assets_linked_to_another_asset_on_a_specific_channel_and_locale()
    {
        $this->loadLinkedAssets('city', ['paris']);
        $this->loadLinkedAssetAttribute('main_city_designers_fingerprint', 'city', true, true);
        $this->loadAssetHavingLinkedAsset('stark', 'paris', 'ecommerce', 'en_US');
        $this->get('akeneo_assetmanager.client.asset')->refreshIndex();

        $searchResultEcommerceEnUS = $this->searchAssets(
            'ecommerce', 'en_US', ['main_city_designers_fingerprint' => ['paris']]
        );
        $identifiers = $searchResultEcommerceEnUS->normalize()['identifiers'];
        $this->assertContains('stark', $identifiers);

        $this->expectException('\LogicException');
        $this->searchAssets(
            'mobile', 'fr_FR', ['main_city_designers_fingerprint' => ['paris']]
        );
    }

    /**
     * @test
     */
    public function it_searches_all_assets_linked_to_multiple_assets()
    {
        $this->loadLinkedAssets('city', ['paris', 'barcelona']);
        $this->loadLinkedAssetCollectionAttribute('main_cities_designers_fingerprint', 3, 'city');
        $this->loadAssetHavingLinkedAssetCollection(
            'stark',
            [
                'main_cities_designers_fingerprint' => ['paris', 'barcelona'],
            ]
        );
        $this->get('akeneo_assetmanager.client.asset')->refreshIndex();

        $searchResultEcommerceEnUS = $this->searchAssets(
            'ecommerce', 'en_US', ['main_cities_designers_fingerprint' => ['paris', 'barcelona']]
        );
        $searchResultMobileFrFR = $this->searchAssets(
            'mobile', 'fr_FR', ['main_cities_designers_fingerprint' => ['barcelona', 'london']]
        );
        $emptySearchResult = $this->searchAssets(
            'ecommerce', 'en_US', ['main_cities_designers_fingerprint' => ['london']]
        );

        $this->assertContains('stark', $searchResultEcommerceEnUS->normalize()['identifiers']);
        $this->assertContains('stark', $searchResultMobileFrFR->normalize()['identifiers']);
        $this->assertEmpty($emptySearchResult->normalize()['identifiers']);
    }

    /**
     * @test
     */
    public function it_searches_all_assets_with_two_filters_on_values()
    {
        $this->loadLinkedAssets('city', ['paris', 'barcelona']);
        $this->loadLinkedAssets('country', ['france']);
        $this->loadLinkedAssetCollectionAttribute('main_cities_designers_fingerprint', 3, 'city');
        $this->loadLinkedAssetCollectionAttribute('main_countries_designers_fingerprint', 4, 'country');
        $this->loadAssetHavingLinkedAssetCollection(
            'stark',
            [
                'main_cities_designers_fingerprint' => ['paris', 'barcelona'],
                'main_countries_designers_fingerprint' => ['france']
            ]
        );
        $this->get('akeneo_assetmanager.client.asset')->refreshIndex();

        $searchResultEcommerceEnUS = $this->searchAssets(
            'ecommerce',
            'en_US',
            [
                'main_cities_designers_fingerprint' => ['paris', 'barcelona'],
                'main_countries_designers_fingerprint' => ['france']
            ]
        );

        $this->assertContains('stark', $searchResultEcommerceEnUS->normalize()['identifiers']);
    }

    /**
     * @test
     */
    public function it_searches_all_assets_linked_to_multiple_assets_on_a_specific_channel_and_locale()
    {
        $this->loadLinkedAssets('city', ['paris', 'barcelona']);
        $this->loadLinkedAssetCollectionAttribute('main_cities_designers_fingerprint', 3, 'city', true, true);
        $this->loadAssetHavingLinkedAssetCollection(
            'stark',
            [
                'main_cities_designers_fingerprint' => ['paris', 'barcelona'],
            ],
            'ecommerce',
            'en_US'
        );
        $this->get('akeneo_assetmanager.client.asset')->refreshIndex();

        $searchResultEcommerceEnUS = $this->searchAssets(
            'ecommerce',
            'en_US',
            [
                'main_cities_designers_fingerprint' => ['paris', 'barcelona']
            ]
        );
        $identifiers = $searchResultEcommerceEnUS->normalize()['identifiers'];
        $this->assertContains('stark', $identifiers);

        $this->expectException('\LogicException');
        $this->searchAssets(
            'mobile',
            'fr_FR',
            [
                'main_cities_designers_fingerprint' => ['paris']
            ]
        );
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadAttributeWithOptions(string $attributeIdentifier, array $options, bool $isScopable = false, bool $isLocalizable = false): void
    {
        $this->attributeIdentifier = AttributeIdentifier::fromString($attributeIdentifier);
        $optionAttribute = OptionAttribute::create(
            $this->attributeIdentifier,
            $this->assetFamilyIdentifier,
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean($isScopable),
            AttributeValuePerLocale::fromBoolean($isLocalizable)
        );

        $attributeOptions = array_map(
            function (string $optionCode) {
                return AttributeOption::create(
                    OptionCode::fromString($optionCode),
                    LabelCollection::fromArray([])
                );
            },
            $options
        );
        $optionAttribute->setOptions($attributeOptions);

        /** @var AttributeRepositoryInterface $attributeRepository */
        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $attributeRepository->create($optionAttribute);
    }

    private function loadAttributeWithOptionCollection(string $attributeIdentifier, array $options, bool $isScopable = false, bool $isLocalizable = false): void
    {
        $this->attributeIdentifier = AttributeIdentifier::fromString($attributeIdentifier);
        $optionCollectionAttribute = OptionCollectionAttribute::create(
            $this->attributeIdentifier,
            $this->assetFamilyIdentifier,
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean($isScopable),
            AttributeValuePerLocale::fromBoolean($isLocalizable)
        );

        $attributeOptions = array_map(
            function (string $optionCode) {
                return AttributeOption::create(
                    OptionCode::fromString($optionCode),
                    LabelCollection::fromArray([])
                );
            },
            $options
        );
        $optionCollectionAttribute->setOptions($attributeOptions);

        /** @var AttributeRepositoryInterface $attributeRepository */
        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $attributeRepository->create($optionCollectionAttribute);
    }

    private function loadAssetHavingOption(string $assetCode, string $optionCode, string $channel = null, string $locale = null)
    {
        /** @var AssetRepositoryInterface $assetRepository */
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString($assetCode),
                $this->assetFamilyIdentifier,
                AssetCode::fromString($assetCode),
                ValueCollection::fromValues([
                    Value::create(
                        $this->attributeIdentifier,
                        (null !== $channel) ? ChannelReference::createfromNormalized($channel) : ChannelReference::noReference(),
                        (null !== $locale) ? LocaleReference::createFromNormalized($locale) : LocaleReference::noReference(),
                        OptionData::createFromNormalize($optionCode)
                    ),
                ])
            )
        );
        $this->flushAssetsToIndexCache();
    }

    private function loadAssetHavingOptionCollection(string $assetCode, array $optionCodes, string $channel = null, string $locale = null)
    {
        /** @var AssetRepositoryInterface $assetRepository */
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString($assetCode),
                $this->assetFamilyIdentifier,
                AssetCode::fromString($assetCode),
                ValueCollection::fromValues([
                    Value::create(
                        $this->attributeIdentifier,
                        (null !== $channel) ? ChannelReference::createfromNormalized($channel) : ChannelReference::noReference(),
                        (null !== $locale) ? LocaleReference::createFromNormalized($locale) : LocaleReference::noReference(),
                        OptionCollectionData::createFromNormalize($optionCodes)
                    ),
                ])
            )
        );
        $this->flushAssetsToIndexCache();
    }

    private function loadAssetHavingLinkedAsset(string $assetCode, string $linkedAsset, string $channel = null, string $locale = null)
    {
        /** @var AssetRepositoryInterface $assetRepository */
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString($assetCode),
                $this->assetFamilyIdentifier,
                AssetCode::fromString($assetCode),
                ValueCollection::fromValues([
                    Value::create(
                        $this->attributeIdentifier,
                        (null !== $channel) ? ChannelReference::createfromNormalized($channel) : ChannelReference::noReference(),
                        (null !== $locale) ? LocaleReference::createFromNormalized($locale) : LocaleReference::noReference(),
                        AssetData::createFromNormalize($linkedAsset)
                    ),
                ])
            )
        );
        $this->flushAssetsToIndexCache();
    }

    private function loadAssetHavingLinkedAssetCollection(string $assetCode, array $linkedAssetsByIdentifier, string $channel = null, string $locale = null)
    {
        $values = [];
        foreach ($linkedAssetsByIdentifier as $attributeIdentifier => $linkedAssets) {
            $values[] = Value::create(
                AttributeIdentifier::fromString($attributeIdentifier),
                (null !== $channel) ? ChannelReference::createfromNormalized($channel) : ChannelReference::noReference(),
                (null !== $locale) ? LocaleReference::createFromNormalized($locale) : LocaleReference::noReference(),
                AssetCollectionData::createFromNormalize($linkedAssets)
            );
        }

        /** @var AssetRepositoryInterface $assetRepository */
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString($assetCode),
                $this->assetFamilyIdentifier,
                AssetCode::fromString($assetCode),
                ValueCollection::fromValues($values)
            )
        );
        $this->flushAssetsToIndexCache();
    }

    private function loadAssetFamily(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $this->assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetFamily = AssetFamily::create(
            $this->assetFamilyIdentifier,
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($assetFamily);
    }

    private function searchAssets(string $channel, string $locale, array $dataValues): IdentifiersForQueryResult
    {
        $filters = [
            [
                'field'    => 'asset_family',
                'operator' => '=',
                'value'    => $this->assetFamilyIdentifier->normalize(),
                'context'  => [],
            ]
        ];

        foreach ($dataValues as $attributeIdentifier => $options) {
            $filters[] = [
                'field' => sprintf('values.%s', $attributeIdentifier),
                'operator'  => 'IN',
                'value'     => $options,
                'context'   => [],
            ];
        }

        $searchResult = $this->findIdentifiersForQuery->find(
            AssetQuery::createFromNormalized([
                'locale'  => $locale,
                'channel' => $channel,
                'size'    => 20,
                'page'    => 0,
                'filters' => $filters,
            ])
        );

        return $searchResult;
    }

    private function loadLinkedAssets(string $assetFamilyIdentifier, array $assetCodes): void
    {
        $assetFamilyRepository = $this->get(
            'akeneo_assetmanager.infrastructure.persistence.repository.asset_family'
        );
        $linkedAssetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
        $assetFamily = AssetFamily::create(
            $linkedAssetFamilyIdentifier,
            [
                'en_US' => ucfirst($assetFamilyIdentifier),
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($assetFamily);
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');

        foreach ($assetCodes as $assetCode) {
            $assetRepository->create(
                Asset::create(
                    AssetIdentifier::fromString($assetCode),
                    $linkedAssetFamilyIdentifier,
                    AssetCode::fromString($assetCode),
                    ValueCollection::fromValues([])
                )
            );
        }
        $this->flushAssetsToIndexCache();
    }
}
