<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Search\Elasticsearch\Record\SearchRecordLinks;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\OptionAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\Record;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\LocaleReference;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\OptionData;
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
class FilterRecords extends SearchIntegrationTestCase
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
            'ecommerce', 'en_US', 'main_color_designers_fingerprint', 'red'
        );
        $searchResultMobileFrFR = $this->searchRecords(
            'mobile', 'fr_FR', 'main_color_designers_fingerprint', 'red'
        );
        $emptySearchResult = $this->searchRecords(
            'ecommerce', 'en_US', 'main_color_designers_fingerprint', 'blue'
        );

        $this->assertContains('stark', $searchResultEcommerceEnUS->normalize()['identifiers']);
        $this->assertContains('stark', $searchResultMobileFrFR->normalize()['identifiers']);
        $this->assertEmpty($emptySearchResult->normalize()['identifiers']);
    }

    /**
     * @test
     */
    public function it_finds_all_records_having_a_localizable_simple_option_on_a_specific_channel_and_locale()
    {
    }

    // TODO: Write the tests for the following usecases
    // public function it_searches_all_records_having_multiple_options()
    // public function it_searches_all_records_linked_to_another_record()
    // public function it_searches_all_records_linked_to_multiple_records()

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function loadAttributeWithOptions(string $attributeIdentifier, array $options): void
    {
        $this->attributeIdentifier = AttributeIdentifier::fromString($attributeIdentifier);
        $optionAttribute = OptionAttribute::create(
            $this->attributeIdentifier,
            $this->referenceEntityIdentifier,
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
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

    private function loadRecordHavingOption(string $recordCode, string $optionCode)
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
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        OptionData::createFromNormalize($optionCode)
                    ),
                ])
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

    private function searchRecords(string $channel, string $locale, string $attributeIdentifier, string $options): IdentifiersForQueryResult
    {
        $searchResult = ($this->findIdentifiersForQuery)(
            RecordQuery::createFromNormalized([
                'locale'  => $locale,
                'channel' => $channel,
                'size'    => 20,
                'page'    => 0,
                'filters' => [
                    [
                        'field'    => 'reference_entity',
                        'operator' => '=',
                        'value'    => $this->referenceEntityIdentifier->normalize(),
                        'context'  => [],
                    ],
                    [
                        'field' => sprintf('values.%s', $attributeIdentifier),
                        'operator'  => 'IN',
                        'value'     => [$options],
                        'context'   => [],
                    ],
                ],
            ])
        );

        return $searchResult;
    }
}

