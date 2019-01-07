<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOrder;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\TextAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LabelCollection;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifierCollection;
use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\Record\Value\ChannelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindIdentifiersForQueryInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Akeneo\ReferenceEntity\Integration\SearchIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class FindIdentifiersForQueryTest extends SearchIntegrationTestCase
{
    /** @var FindIdentifiersForQueryInterface */
    private $findIdentifiersForQuery;

    public function setUp()
    {
        parent::setUp();

        $this->findIdentifiersForQuery = $this->get('akeneo_referenceentity.infrastructure.search.elasticsearch.record.query.find_identifiers_for_query');
        $this->resetDB();
        $this->createReferenceEntityWithAttributes();
        $this->loadDataset();
    }

    /**
     * @test
     */
    public function default_search()
    {
        $query = RecordQuery::createFromNormalized([
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'size' => 20,
            'page' => 0,
            'filters' => [
                [
                    'field' => 'full_text',
                    'operator' => '=',
                    'value' => '',
                    'context' => []
                ],
                [
                    'field' => 'reference_entity',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ]
            ]
        ]);

        $matchingidentifiers = ($this->findIdentifiersForQuery)($query);
        Assert::assertsame([
            'identifiers' => ['brand_kartell', 'brand_alessi', 'brand_bangolufsen'],
            'total' => 3
        ], $matchingidentifiers->normalize());
    }

    /**
     * @test
     */
    public function simple_search()
    {
        $query = RecordQuery::createFromNormalized([
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'size' => 20,
            'page' => 0,
            'filters' => [
                [
                    'field' => 'full_text',
                    'operator' => '=',
                    'value' => 'year',
                    'context' => []
                ],
                [
                    'field' => 'reference_entity',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ]
            ]
        ]);

        $matchingidentifiers = ($this->findIdentifiersForQuery)($query);
        Assert::assertsame([
            'identifiers' => ['brand_kartell', 'brand_alessi', 'brand_bangolufsen'],
            'total' => 3
        ], $matchingidentifiers->normalize());
    }

    /**
     * @test
     */
    public function two_words_search()
    {
        $query = RecordQuery::createFromNormalized([
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'size' => 20,
            'page' => 0,
            'filters' => [
                [
                    'field' => 'full_text',
                    'operator' => '=',
                    'value' => 'experience senses',
                    'context' => []
                ],
                [
                    'field' => 'reference_entity',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ]
            ]
        ]);

        $matchingidentifiers = ($this->findIdentifiersForQuery)($query);
        Assert::assertsame([
            'identifiers' => ['brand_bangolufsen'],
            'total' => 1
        ], $matchingidentifiers->normalize());
    }

    /**
     * @test
     */
    public function two_words_search_with_special_characters()
    {
        $query = RecordQuery::createFromNormalized([
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'size' => 20,
            'page' => 0,
            'filters' => [
                [
                    'field' => 'full_text',
                    'operator' => '=',
                    'value' => '"special senses"',
                    'context' => []
                ],
                [
                    'field' => 'reference_entity',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ]
            ]
        ]);

        $matchingidentifiers = ($this->findIdentifiersForQuery)($query);
        Assert::assertsame([
            'identifiers' => ['brand_bangolufsen'],
            'total' => 1
        ], $matchingidentifiers->normalize());
    }

    /**
     * @test
     */
    public function code_label_filter()
    {
        $query = RecordQuery::createFromNormalized([
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'size' => 20,
            'page' => 0,
            'filters' => [
                [
                    'field' => 'code_label',
                    'operator' => '=',
                    'value' => 'alessi',
                    'context' => []
                ],
                [
                    'field' => 'reference_entity',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ],
            ]
        ]);

        $matchingidentifiers = ($this->findIdentifiersForQuery)($query);
        Assert::assertsame([
            'identifiers' => ['brand_alessi'],
            'total' => 1
        ], $matchingidentifiers->normalize());

        $query = RecordQuery::createFromNormalized([
            'locale' => 'fr_FR',
            'channel' => 'ecommerce',
            'size' => 20,
            'page' => 0,
            'filters' => [
                [
                    'field' => 'code_label',
                    'operator' => '=',
                    'value' => 'Marcel Francais',
                    'context' => []
                ],
                [
                    'field' => 'reference_entity',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ],
            ]
        ]);

        $matchingidentifiers = ($this->findIdentifiersForQuery)($query);
        Assert::assertsame([
            'identifiers' => ['brand_alessi'],
            'total' => 1
        ], $matchingidentifiers->normalize());

        $query = RecordQuery::createFromNormalized([
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'size' => 20,
            'page' => 0,
            'filters' => [
                [
                    'field' => 'code_label',
                    'operator' => '=',
                    'value' => 'Marcel Francais',
                    'context' => []
                ],
                [
                    'field' => 'reference_entity',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ],
            ]
        ]);

        $matchingidentifiers = ($this->findIdentifiersForQuery)($query);
        Assert::assertsame([
            'identifiers' => [],
            'total' => 0
        ], $matchingidentifiers->normalize());
    }

    /**
     * @test
     */
    public function code_not_in_filter()
    {
        $query = RecordQuery::createFromNormalized([
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'size' => 20,
            'page' => 0,
            'filters' => [
                [
                    'field' => 'code',
                    'operator' => 'NOT IN',
                    'value' => ['kartell', 'alessi'],
                    'context' => []
                ],
                [
                    'field' => 'reference_entity',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ]
            ]
        ]);

        $matchingidentifiers = ($this->findIdentifiersForQuery)($query);
        Assert::assertsame([
            'identifiers' => ['brand_bangolufsen'],
            'total' => 1
        ], $matchingidentifiers->normalize());
    }

    /**
     * @test
     */
    public function code_in_filter()
    {
        $query = RecordQuery::createFromNormalized([
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'size' => 20,
            'page' => 0,
            'filters' => [
                [
                    'field' => 'code',
                    'operator' => 'IN',
                    'value' => ['kartell', 'alessi'],
                    'context' => []
                ],
                [
                    'field' => 'reference_entity',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ]
            ]
        ]);

        $matchingidentifiers = ($this->findIdentifiersForQuery)($query);
        Assert::assertSame([
            'identifiers' => ['brand_kartell', 'brand_alessi'],
            'total' => 2
        ], $matchingidentifiers->normalize());
    }

    /**
     * @test
     */
    public function all_records_filter()
    {
        $query = RecordQuery::createFromNormalized([
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'size' => 20,
            'page' => 0,
            'filters' => [
                [
                    'field' => 'reference_entity',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ]
            ]
        ]);

        $matchingidentifiers = ($this->findIdentifiersForQuery)($query);
        Assert::assertSame([
            'identifiers' => ['brand_kartell', 'brand_alessi', 'brand_bangolufsen'],
            'total' => 3
        ], $matchingidentifiers->normalize());
    }

    /**
     * @test
     */
    public function complete_records_filter()
    {
        $query = RecordQuery::createFromNormalized([
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'size' => 20,
            'page' => 0,
            'filters' => [
                [
                    'field' => 'complete',
                    'operator' => '=',
                    'value' => true,
                    'context' => []
                ],
                [
                    'field' => 'reference_entity',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ]
            ]
        ]);

        $matchingidentifiers = ($this->findIdentifiersForQuery)($query);
        Assert::assertSame([
            'identifiers' => ['brand_kartell'],
            'total' => 1
        ], $matchingidentifiers->normalize());
    }

    // add case for update date filter

    /**
     * @test
     */
    public function uncomplete_records_filter()
    {
        $query = RecordQuery::createFromNormalized([
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'size' => 20,
            'page' => 0,
            'filters' => [
                [
                    'field' => 'complete',
                    'operator' => '=',
                    'value' => false,
                    'context' => []
                ],
                [
                    'field' => 'reference_entity',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ]
            ]
        ]);

        $matchingidentifiers = ($this->findIdentifiersForQuery)($query);
        Assert::assertSame([
            'identifiers' => ['brand_alessi', 'brand_bangolufsen'],
            'total' => 2
        ], $matchingidentifiers->normalize());
    }

    /**
     * @test
     */
    public function paginated_by_search_after_search()
    {
        $query = RecordQuery::createPaginatedQueryUsingSearchAfter(
            ReferenceEntityIdentifier::fromString('brand'),
            ChannelReference::noReference(),
            LocaleIdentifierCollection::empty(),
            10,
            RecordCode::fromString('alessi')
        );

        $matchingIdentifiers = ($this->findIdentifiersForQuery)($query);
        Assert::assertSame([
            'identifiers' => ['brand_bangolufsen', 'brand_kartell'],
            'total' => 3
        ], $matchingIdentifiers->normalize());
    }

    /**
     * @test
     */
    public function paginated_by_search_after_from_the_start_search()
    {
        $query = RecordQuery::createPaginatedQueryUsingSearchAfter(
            ReferenceEntityIdentifier::fromString('brand'),
            ChannelReference::noReference(),
            LocaleIdentifierCollection::empty(),
            10,
            null
        );

        $matchingIdentifiers = ($this->findIdentifiersForQuery)($query);
        Assert::assertSame([
            'identifiers' => ['brand_alessi', 'brand_bangolufsen', 'brand_kartell'],
            'total' => 3
        ], $matchingIdentifiers->normalize());
    }

    private function loadDataset()
    {
        // Those properties are not indexed
        $kartellCode = 'kartell';
        $kartellDescriptionEnUs = 'Kartell - The Culture of Plastics’’… In just over 50 years, this famous Italian company has revolutionised plastic, elevating it and propelling it into the refined world of luxury. Today, Kartell has more than a hundred showrooms all over the world and a good number of its creations have become cult pieces on display in the most prestigious museums. The famous Kartell Louis Ghost armchair has the most sales for armchairs in the world, with 1.5 million sales! Challenging the material, constantly researching new tactile, visual and aesthetic effects - Kartell faces every challenge! With more than 60 years of experience in dealing with plastic, the brand has a unique know-how and an unquenchable thirst for innovation. Kartellharnesses technological progress: notably, we owe them for the first totally transparent plastic chair, injection moulds, laser welding and more!';
        $kartellDesigner = 'Philippe Starck';
        $kartell = [
            'reference_entity_code' => 'brand',
            'identifier'            => 'brand_kartell',
            'code' => $kartellCode,
            'record_code_label_search' => ['en_US' => $kartellCode . ' ' . $kartellDesigner],
            'record_full_text_search'  => ['ecommerce' => ['en_US' => $kartellCode . ' ' . $kartellDescriptionEnUs . ' ' . $kartellDesigner]],
            'updated_at' => date_create('2018-01-01')->format('Y-m-d'),
            'complete_value_keys' => [
                'founder_brand_fingerprint_ecommerce_en_US' => true,
                'description_brand_fingerprint_ecommerce_en_US' => true,
            ],
        ];

        // Those properties are not indexed
        $alessiCode = 'alessi';
        $alessiDescriptionEnUs = 'Alessi is truly a "dream factory"! This famous Italian brand has been enhancing our daily lives for more than 80 years thanks to its beautiful and functional items which are designed by leading architects and designers. At Alessi, design has been a family affair since 1921. Initially focusing on coffee services and trays, Alessi acquired international popularity during the 1950s through working with renowned architects and designers such as Ettore Sottsass.';
        $alessiDesigner = 'Marcel Wanders';
        $alessi = [
            'reference_entity_code' => 'brand',
            'identifier'            => 'brand_alessi',
            'code' => $alessiCode,
            'record_code_label_search' => [
                'en_US' => $alessiCode . ' ' . $alessiDesigner,
                'fr_FR' => $alessiCode . ' Marcel Francais',
            ],
            'record_full_text_search'          => ['ecommerce' => ['en_US' => $alessiCode . ' ' . $alessiDescriptionEnUs . ' ' . $alessiDesigner]],
            'updated_at' => date_create('2017-01-01')->format('Y-m-d'),
            'complete_value_keys' => [],
        ];

        // Those properties are not indexed
        $bangolufsenCode = 'bangolufsen';
        $bangolufsenDescriptionEnUs = <<<TEXT
B&O PLAY delivers stand-alone products with clear and simple operations - portable products that are intuitive to use, easy to integrate into your daily life, and deliver excellent high-quality experiences.

‘’We want to evoke senses, to elevate the experience of listening and watching. We have spoken to musicians and studio recorders who all love the fact that more people listen to music in more places, but hate the fact that the quality of the listening experience has been eroded. We want to provide the opportunity to experience media in a convenient and easy way but still in outstanding high quality.  Firmly grounded in our 88-year history in Bang & Olufsen, we interpret the same core values for a new type of contemporary products."
Are they the "special senses" ?
TEXT;
        $bangolufsenDesigner = 'Cecilie Manz';
        $bangolufsen = [
            'reference_entity_code' => 'brand',
            'identifier'            => 'brand_bangolufsen',
            'code' => $bangolufsenCode,
            'record_code_label_search' => ['en_US' => $bangolufsenCode . ' ' . $bangolufsenDesigner],
            'record_full_text_search'    => ['ecommerce' => ['en_US' => $bangolufsenCode . ' ' . $bangolufsenDescriptionEnUs . ' ' . $bangolufsenDesigner]],
            'updated_at' => date_create('2016-01-01')->format('Y-m-d'),
            'complete_value_keys' => [
                'description_brand_fingerprint_ecommerce_en_US' => true,
                'founded_brand_fingerprint_ecommerce_en_US' => true,
            ],
        ];

        $wrongReferenceEntity = [
            'identifier'            => 'another_reference_entity',
            'reference_entity_code' => 'manufacturer',
            'code' => 'manu_code',
            'record_code_label_search' => ['fr_FR' => 'wrong_reference'],
            'record_full_text_search'    => ['ecommerce' => ['fr_FR' => 'stark Designer supérieure']],
            'updated_at' => date_create('2010-01-01')->format('Y-m-d'),
            'complete_value_keys' => [],
        ];
        $this->searchRecordIndexHelper->index([$kartell, $alessi, $bangolufsen, $wrongReferenceEntity]);
    }

    private function resetDB(): void
    {
        $this->get('akeneoreference_entity.tests.helper.database_helper')->resetDatabase();
    }

    private function createReferenceEntityWithAttributes(): void
    {
        $repository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.reference_entity');

        $referenceEntityDesigner = ReferenceEntity::create(
            ReferenceEntityIdentifier::fromString('brand'),
            [],
            Image::createEmpty()
        );
        $repository->create($referenceEntityDesigner);

        $description = TextAttribute::createText(
            AttributeIdentifier::create('brand', 'description', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'Description']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $founder = TextAttribute::createText(
            AttributeIdentifier::create('brand', 'founder', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('founder'),
            LabelCollection::fromArray(['en_US' => 'Founder']),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $founded = TextAttribute::createText(
            AttributeIdentifier::create('brand', 'founded', 'fingerprint'),
            ReferenceEntityIdentifier::fromString('brand'),
            AttributeCode::fromString('founded'),
            LabelCollection::fromArray(['en_US' => 'Founded']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $attributesRepository = $this->get('akeneo_referenceentity.infrastructure.persistence.repository.attribute');
        $attributesRepository->create($description);
        $attributesRepository->create($founder);
        $attributesRepository->create($founded);
    }
}
