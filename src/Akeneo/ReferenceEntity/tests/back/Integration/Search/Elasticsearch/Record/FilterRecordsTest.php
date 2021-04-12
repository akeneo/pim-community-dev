<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\OptionCollectionData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\OptionData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordCollectionData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\RecordData;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\Value;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ValueCollection;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersForQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\IdentifiersForQueryResult;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Repository\RecordRepositoryInterface;
use Akeneo\ReferenceEntity\Integration\SearchIntegrationTestCase;

/**
 * Testing the search usecases to filter on attributes:
 * - option
 * - option_collection
 * - record
 * - record_collection
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class FilterRecordsTest extends SearchIntegrationTestCase
{
    /** @var FindIdentifiersForQueryInterface */
    private $findIdentifiersForQuery;

    /** @var ReferenceEntityIdentifier */
    private $referenceEntityIdentifier;

    /** @var AttributeIdentifier */
    private $attributeIdentifier;

    public function setUp(): void
    {
        parent::setUp();
        $this->resetDB();
        $this->findIdentifiersForQuery = $this->get('akeneo_referenceentity.infrastructure.search.elasticsearch.record.query.find_identifiers_for_query');
    }

    /**
     * @test
     */
    public function it_finds_all_records_having_a_simple_option()
    {
        $this->loadReferenceEntity();
        $this->loadAttributeWithOptions('main_color_designers_fingerprint', ['red', 'blue']);
        $this->loadRecordHavingOption('stark', 'red');
        $this->get('akeneo_referenceentity.client.record')->refreshIndex();

        $searchResultEcommerceEnUS = $this->searchRecords(
            'ecommerce',
            'en_US',
            ['main_color_designers_fingerprint' => ['red']]
        );
        $searchResultMobileFrFR = $this->searchRecords(
            'mobile',
            'fr_FR',
            ['main_color_designers_fingerprint' => ['red']]
        );
        $emptySearchResult = $this->searchRecords(
            'ecommerce',
            'en_US',
            ['main_color_designers_fingerprint' => ['blue']]
        );

        $this->assertContains('stark', $searchResultEcommerceEnUS->normalize()['identifiers']);
        $this->assertContains('stark', $searchResultMobileFrFR->normalize()['identifiers']);
        $this->assertEmpty($emptySearchResult->normalize()['identifiers']);
    }

    /**
     * @test
     */
    public function it_finds_all_records_having_a_simple_option_on_a_specific_channel_and_locale()
    {
        $this->loadReferenceEntity();
        $this->loadAttributeWithOptions('main_color_designers_fingerprint', ['red', 'blue'], true, true);
        $this->loadRecordHavingOption('stark', 'red', 'ecommerce', 'en_US');
        $this->get('akeneo_referenceentity.client.record')->refreshIndex();

        $searchResultEcommerceEnUS = $this->searchRecords(
            'ecommerce',
            'en_US',
            ['main_color_designers_fingerprint' => ['red']]
        );
        $identifiers = $searchResultEcommerceEnUS->normalize()['identifiers'];
        $this->assertContains('stark', $identifiers);

        $this->expectException('\LogicException');
        $this->searchRecords(
            'mobile',
            'fr_FR',
            ['main_color_designers_fingerprint' => ['red']]
        );
    }

    /**
     * @test
     */
    public function it_searches_all_records_having_multiple_options()
    {
        $this->loadReferenceEntity();
        $this->loadAttributeWithOptionCollection('main_color_designers_fingerprint', ['red', 'blue', 'green']);
        $this->loadRecordHavingOptionCollection('stark', ['red', 'blue']);
        $this->get('akeneo_referenceentity.client.record')->refreshIndex();

        $searchResultEcommerceEnUS = $this->searchRecords(
            'ecommerce',
            'en_US',
            ['main_color_designers_fingerprint' => ['red', 'blue']]
        );
        $searchResultMobileFrFR = $this->searchRecords(
            'mobile',
            'fr_FR',
            ['main_color_designers_fingerprint' => ['blue', 'green']]
        );
        $emptySearchResult = $this->searchRecords(
            'ecommerce',
            'en_US',
            ['main_color_designers_fingerprint' => ['green']]
        );

        $this->assertContains('stark', $searchResultEcommerceEnUS->normalize()['identifiers']);
        $this->assertContains('stark', $searchResultMobileFrFR->normalize()['identifiers']);
        $this->assertEmpty($emptySearchResult->normalize()['identifiers']);
    }

    /**
     * @test
     */
    public function it_searches_all_records_having_multiple_options_on_a_specific_channel_and_locale()
    {
        $this->loadReferenceEntity();
        $this->loadAttributeWithOptionCollection('main_color_designers_fingerprint', ['red', 'blue', 'green'], true, true);
        $this->loadRecordHavingOptionCollection('stark', ['red', 'blue'], 'ecommerce', 'en_US');
        $this->get('akeneo_referenceentity.client.record')->refreshIndex();

        $searchResultEcommerceEnUS = $this->searchRecords(
            'ecommerce',
            'en_US',
            ['main_color_designers_fingerprint' => ['red', 'blue']]
        );
        $identifiers = $searchResultEcommerceEnUS->normalize()['identifiers'];
        $this->assertContains('stark', $identifiers);

        $this->expectException('\LogicException');
        $this->searchRecords(
            'mobile',
            'fr_FR',
            ['main_color_designers_fingerprint' => ['red']]
        );
    }

    /**
     * @test
     */
    public function it_searches_all_records_linked_to_another_record()
    {
        $this->loadReferenceEntity();
        $this->loadLinkedRecords('city', ['paris']);
        $this->loadLinkedRecordAttribute('main_city_designers_fingerprint', 'city');
        $this->loadRecordHavingLinkedRecord('stark', 'paris');
        $this->get('akeneo_referenceentity.client.record')->refreshIndex();

        $searchResultEcommerceEnUS = $this->searchRecords(
            'ecommerce',
            'en_US',
            ['main_city_designers_fingerprint' => ['paris']]
        );
        $searchResultMobileFrFR = $this->searchRecords(
            'mobile',
            'fr_FR',
            ['main_city_designers_fingerprint' => ['paris']]
        );
        $emptySearchResult = $this->searchRecords(
            'ecommerce',
            'en_US',
            ['main_city_designers_fingerprint' => ['london']]
        );

        $this->assertContains('stark', $searchResultEcommerceEnUS->normalize()['identifiers']);
        $this->assertContains('stark', $searchResultMobileFrFR->normalize()['identifiers']);
        $this->assertEmpty($emptySearchResult->normalize()['identifiers']);
    }

    /**
     * @test
     */
    public function it_searches_all_records_linked_to_another_record_on_a_specific_channel_and_locale()
    {
        $this->loadReferenceEntity();
        $this->loadLinkedRecords('city', ['paris']);
        $this->loadLinkedRecordAttribute('main_city_designers_fingerprint', 'city', true, true);
        $this->loadRecordHavingLinkedRecord('stark', 'paris', 'ecommerce', 'en_US');
        $this->get('akeneo_referenceentity.client.record')->refreshIndex();

        $searchResultEcommerceEnUS = $this->searchRecords(
            'ecommerce',
            'en_US',
            ['main_city_designers_fingerprint' => ['paris']]
        );
        $identifiers = $searchResultEcommerceEnUS->normalize()['identifiers'];
        $this->assertContains('stark', $identifiers);

        $this->expectException('\LogicException');
        $this->searchRecords(
            'mobile',
            'fr_FR',
            ['main_city_designers_fingerprint' => ['paris']]
        );
    }

    /**
     * @test
     */
    public function it_searches_all_records_linked_to_multiple_records()
    {
        $this->loadReferenceEntity();
        $this->loadLinkedRecords('city', ['paris', 'barcelona']);
        $this->loadLinkedRecordCollectionAttribute('main_cities_designers_fingerprint', 3, 'city');
        $this->loadRecordHavingLinkedRecordCollection(
            'stark',
            [
                'main_cities_designers_fingerprint' => ['paris', 'barcelona'],
            ]
        );
        $this->get('akeneo_referenceentity.client.record')->refreshIndex();

        $searchResultEcommerceEnUS = $this->searchRecords(
            'ecommerce',
            'en_US',
            ['main_cities_designers_fingerprint' => ['paris', 'barcelona']]
        );
        $searchResultMobileFrFR = $this->searchRecords(
            'mobile',
            'fr_FR',
            ['main_cities_designers_fingerprint' => ['barcelona', 'london']]
        );
        $emptySearchResult = $this->searchRecords(
            'ecommerce',
            'en_US',
            ['main_cities_designers_fingerprint' => ['london']]
        );

        $this->assertContains('stark', $searchResultEcommerceEnUS->normalize()['identifiers']);
        $this->assertContains('stark', $searchResultMobileFrFR->normalize()['identifiers']);
        $this->assertEmpty($emptySearchResult->normalize()['identifiers']);
    }

    /**
     * @test
     */
    public function it_searches_all_records_with_two_filters_on_values()
    {
        $this->loadReferenceEntity();
        $this->loadLinkedRecords('city', ['paris', 'barcelona']);
        $this->loadLinkedRecords('country', ['france']);
        $this->loadLinkedRecordCollectionAttribute('main_cities_designers_fingerprint', 3, 'city');
        $this->loadLinkedRecordCollectionAttribute('main_countries_designers_fingerprint', 4, 'country');
        $this->loadRecordHavingLinkedRecordCollection(
            'stark',
            [
                'main_cities_designers_fingerprint' => ['paris', 'barcelona'],
                'main_countries_designers_fingerprint' => ['france']
            ]
        );
        $this->get('akeneo_referenceentity.client.record')->refreshIndex();

        $searchResultEcommerceEnUS = $this->searchRecords(
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
    public function it_searches_all_records_linked_to_multiple_records_on_a_specific_channel_and_locale()
    {
        $this->loadReferenceEntity();
        $this->loadLinkedRecords('city', ['paris', 'barcelona']);
        $this->loadLinkedRecordCollectionAttribute('main_cities_designers_fingerprint', 3, 'city', true, true);
        $this->loadRecordHavingLinkedRecordCollection(
            'stark',
            [
                'main_cities_designers_fingerprint' => ['paris', 'barcelona'],
            ],
            'ecommerce',
            'en_US'
        );
        $this->get('akeneo_referenceentity.client.record')->refreshIndex();

        $searchResultEcommerceEnUS = $this->searchRecords(
            'ecommerce',
            'en_US',
            [
                'main_cities_designers_fingerprint' => ['paris', 'barcelona']
            ]
        );
        $identifiers = $searchResultEcommerceEnUS->normalize()['identifiers'];
        $this->assertContains('stark', $identifiers);

        $this->expectException('\LogicException');
        $this->searchRecords(
            'mobile',
            'fr_FR',
            [
                'main_cities_designers_fingerprint' => ['paris']
            ]
        );
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadAttributeWithOptions(string $attributeIdentifier, array $options, bool $isScopable = false, bool $isLocalizable = false): void
    {
        $this->attributeIdentifier = AttributeIdentifier::fromString($attributeIdentifier);
        $optionAttribute = OptionAttribute::create(
            $this->attributeIdentifier,
            $this->referenceEntityIdentifier,
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
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
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attributeRepository->create($optionAttribute);
    }

    private function loadAttributeWithOptionCollection(string $attributeIdentifier, array $options, bool $isScopable = false, bool $isLocalizable = false): void
    {
        $this->attributeIdentifier = AttributeIdentifier::fromString($attributeIdentifier);
        $optionCollectionAttribute = OptionCollectionAttribute::create(
            $this->attributeIdentifier,
            $this->referenceEntityIdentifier,
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
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
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attributeRepository->create($optionCollectionAttribute);
    }

    private function loadLinkedRecordAttribute(string $attributeIdentifier, string $recordType, bool $isScopable = false, bool $isLocalizable = false): void
    {
        $this->attributeIdentifier = AttributeIdentifier::fromString($attributeIdentifier);
        $recordAttribute = RecordAttribute::create(
            $this->attributeIdentifier,
            $this->referenceEntityIdentifier,
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean($isScopable),
            AttributeValuePerLocale::fromBoolean($isLocalizable),
            ReferenceEntityIdentifier::fromString($recordType)
        );

        /** @var AttributeRepositoryInterface $attributeRepository */
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attributeRepository->create($recordAttribute);
    }

    private function loadLinkedRecordCollectionAttribute(string $attributeIdentifier, int $order, string $recordType, bool $isScopable = false, bool $isLocalizable = false): void
    {
        $this->attributeIdentifier = AttributeIdentifier::fromString($attributeIdentifier);
        $recordAttribute = RecordCollectionAttribute::create(
            $this->attributeIdentifier,
            $this->referenceEntityIdentifier,
            AttributeCode::fromString($attributeIdentifier),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger($order),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean($isScopable),
            AttributeValuePerLocale::fromBoolean($isLocalizable),
            ReferenceEntityIdentifier::fromString($recordType)
        );

        /** @var AttributeRepositoryInterface $attributeRepository */
        $attributeRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attributeRepository->create($recordAttribute);
    }

    private function loadRecordHavingOption(string $recordCode, string $optionCode, string $channel = null, string $locale = null)
    {
        /** @var RecordRepositoryInterface $recordRepository */
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $recordRepository->create(
            Record::create(
                RecordIdentifier::fromString($recordCode),
                $this->referenceEntityIdentifier,
                RecordCode::fromString($recordCode),
                ValueCollection::fromValues([
                    Value::create(
                        $this->attributeIdentifier,
                        (null !== $channel) ? ChannelReference::createFromNormalized($channel) : ChannelReference::noReference(),
                        (null !== $locale) ? LocaleReference::createFromNormalized($locale) : LocaleReference::noReference(),
                        OptionData::createFromNormalize($optionCode)
                    ),
                ])
            )
        );
    }

    private function loadRecordHavingOptionCollection(string $recordCode, array $optionCodes, string $channel = null, string $locale = null)
    {
        /** @var RecordRepositoryInterface $recordRepository */
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $recordRepository->create(
            Record::create(
                RecordIdentifier::fromString($recordCode),
                $this->referenceEntityIdentifier,
                RecordCode::fromString($recordCode),
                ValueCollection::fromValues([
                    Value::create(
                        $this->attributeIdentifier,
                        (null !== $channel) ? ChannelReference::createFromNormalized($channel) : ChannelReference::noReference(),
                        (null !== $locale) ? LocaleReference::createFromNormalized($locale) : LocaleReference::noReference(),
                        OptionCollectionData::createFromNormalize($optionCodes)
                    ),
                ])
            )
        );
    }

    private function loadRecordHavingLinkedRecord(string $recordCode, string $linkedRecord, string $channel = null, string $locale = null)
    {
        /** @var RecordRepositoryInterface $recordRepository */
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $recordRepository->create(
            Record::create(
                RecordIdentifier::fromString($recordCode),
                $this->referenceEntityIdentifier,
                RecordCode::fromString($recordCode),
                ValueCollection::fromValues([
                    Value::create(
                        $this->attributeIdentifier,
                        (null !== $channel) ? ChannelReference::createFromNormalized($channel) : ChannelReference::noReference(),
                        (null !== $locale) ? LocaleReference::createFromNormalized($locale) : LocaleReference::noReference(),
                        RecordData::createFromNormalize($linkedRecord)
                    ),
                ])
            )
        );
    }

    private function loadRecordHavingLinkedRecordCollection(string $recordCode, array $linkedRecordsByIdentifier, string $channel = null, string $locale = null)
    {
        $values = [];
        foreach ($linkedRecordsByIdentifier as $attributeIdentifier => $linkedRecords) {
            $values[] = Value::create(
                AttributeIdentifier::fromString($attributeIdentifier),
                (null !== $channel) ? ChannelReference::createFromNormalized($channel) : ChannelReference::noReference(),
                (null !== $locale) ? LocaleReference::createFromNormalized($locale) : LocaleReference::noReference(),
                RecordCollectionData::createFromNormalize($linkedRecords)
            );
        }

        /** @var RecordRepositoryInterface $recordRepository */
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');
        $recordRepository->create(
            Record::create(
                RecordIdentifier::fromString($recordCode),
                $this->referenceEntityIdentifier,
                RecordCode::fromString($recordCode),
                ValueCollection::fromValues($values)
            )
        );
    }

    private function loadReferenceEntity(): void
    {
        $referenceEntityRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');
        $this->referenceEntityIdentifier = ReferenceEntityIdentifier::fromString('designer');
        $referenceEntity = ReferenceEntity::create(
            $this->referenceEntityIdentifier,
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);
    }

    private function searchRecords(string $channel, string $locale, array $dataValues): IdentifiersForQueryResult
    {
        $filters = [
            [
                'field'    => 'reference_entity',
                'operator' => '=',
                'value'    => $this->referenceEntityIdentifier->normalize(),
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
            RecordQuery::createFromNormalized([
                'locale'  => $locale,
                'channel' => $channel,
                'size'    => 20,
                'page'    => 0,
                'filters' => $filters,
            ])
        );

        return $searchResult;
    }

    private function loadLinkedRecords(string $referenceEntityIdentifier, array $recordCodes): void
    {
        $referenceEntityRepository = $this->get(
            'akeneo_referenceentity.infrastructure.persistence.repository.reference_entity'
        );
        $linkedReferenceEntityIdentifier = ReferenceEntityIdentifier::fromString($referenceEntityIdentifier);
        $referenceEntity = ReferenceEntity::create(
            $linkedReferenceEntityIdentifier,
            [
                'en_US' => ucfirst($referenceEntityIdentifier),
            ],
            Image::createEmpty()
        );
        $referenceEntityRepository->create($referenceEntity);
        $recordRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.record');

        foreach ($recordCodes as $recordCode) {
            $recordRepository->create(
                Record::create(
                    RecordIdentifier::fromString($recordCode),
                    $linkedReferenceEntityIdentifier,
                    RecordCode::fromString($recordCode),
                    ValueCollection::fromValues([])
                )
            );
        }
    }
}
