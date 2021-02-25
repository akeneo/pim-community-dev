<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Asset\FindIdentifiersForQueryInterface;
use Akeneo\AssetManager\Integration\SearchIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class FindIdentifiersForQueryTest extends SearchIntegrationTestCase
{
    /** @var FindIdentifiersForQueryInterface */
    private $findIdentifiersForQuery;

    public function setUp(): void
    {
        parent::setUp();

        $this->findIdentifiersForQuery = $this->get('akeneo_assetmanager.infrastructure.search.elasticsearch.asset.query.find_identifiers_for_query');

        $this->resetDB();
        $this->createAssetFamilyWithAttributes();
        $this->loadDataset();
    }

    /**
     * @test
     */
    public function default_search()
    {
        $query = AssetQuery::createFromNormalized([
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
                    'field' => 'asset_family',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ]
            ]
        ]);

        $matchingidentifiers = $this->findIdentifiersForQuery->find($query);
        Assert::assertsame([
            'identifiers' => ['brand_kartell', 'brand_alessi', 'brand_bangolufsen'],
            'matches_count' => 3
        ], $matchingidentifiers->normalize());
    }

    /**
     * @test
     */
    public function simple_search()
    {
        $query = AssetQuery::createFromNormalized([
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
                    'field' => 'asset_family',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ]
            ]
        ]);

        $matchingidentifiers = $this->findIdentifiersForQuery->find($query);
        Assert::assertsame([
            'identifiers' => ['brand_kartell', 'brand_alessi', 'brand_bangolufsen'],
            'matches_count' => 3
        ], $matchingidentifiers->normalize());
    }

    /**
     * @test
     */
    public function two_words_search()
    {
        $query = AssetQuery::createFromNormalized([
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
                    'field' => 'asset_family',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ]
            ]
        ]);

        $matchingidentifiers = $this->findIdentifiersForQuery->find($query);
        Assert::assertsame([
            'identifiers' => ['brand_bangolufsen'],
            'matches_count' => 1
        ], $matchingidentifiers->normalize());
    }

    /**
     * @test
     */
    public function two_words_search_with_special_characters()
    {
        $query = AssetQuery::createFromNormalized([
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
                    'field' => 'asset_family',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ]
            ]
        ]);

        $matchingidentifiers = $this->findIdentifiersForQuery->find($query);
        Assert::assertsame([
            'identifiers' => ['brand_bangolufsen'],
            'matches_count' => 1
        ], $matchingidentifiers->normalize());
    }

    /**
     * @test
     */
    public function code_label_filter()
    {
        $query = AssetQuery::createFromNormalized([
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
                    'field' => 'asset_family',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ],
            ]
        ]);

        $matchingidentifiers = $this->findIdentifiersForQuery->find($query);
        Assert::assertsame([
            'identifiers' => ['brand_alessi'],
            'matches_count' => 1
        ], $matchingidentifiers->normalize());

        $query = AssetQuery::createFromNormalized([
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
                    'field' => 'asset_family',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ],
            ]
        ]);

        $matchingidentifiers = $this->findIdentifiersForQuery->find($query);
        Assert::assertsame([
            'identifiers' => ['brand_alessi'],
            'matches_count' => 1
        ], $matchingidentifiers->normalize());

        $query = AssetQuery::createFromNormalized([
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
                    'field' => 'asset_family',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ],
            ]
        ]);

        $matchingidentifiers = $this->findIdentifiersForQuery->find($query);
        Assert::assertsame([
            'identifiers' => [],
            'matches_count' => 0
        ], $matchingidentifiers->normalize());
    }

    /**
     * @test
     */
    public function code_not_in_filter()
    {
        $query = AssetQuery::createFromNormalized([
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
                    'field' => 'asset_family',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ]
            ]
        ]);

        $matchingidentifiers = $this->findIdentifiersForQuery->find($query);
        Assert::assertsame([
            'identifiers' => ['brand_bangolufsen'],
            'matches_count' => 1
        ], $matchingidentifiers->normalize());
    }

    /**
     * @test
     */
    public function code_in_filter()
    {
        $query = AssetQuery::createFromNormalized([
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
                    'field' => 'asset_family',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ]
            ]
        ]);

        $matchingidentifiers = $this->findIdentifiersForQuery->find($query);
        Assert::assertSame([
            'identifiers' => ['brand_kartell', 'brand_alessi'],
            'matches_count' => 2
        ], $matchingidentifiers->normalize());
    }

    /**
     * @test
     */
    public function all_assets_filter()
    {
        $query = AssetQuery::createFromNormalized([
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'size' => 20,
            'page' => 0,
            'filters' => [
                [
                    'field' => 'asset_family',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ]
            ]
        ]);

        $matchingidentifiers = $this->findIdentifiersForQuery->find($query);
        Assert::assertSame([
            'identifiers' => ['brand_kartell', 'brand_alessi', 'brand_bangolufsen'],
            'matches_count' => 3
        ], $matchingidentifiers->normalize());
    }

    /**
     * @test
     */
    public function complete_assets_filter()
    {
        $query = AssetQuery::createFromNormalized([
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
                    'field' => 'asset_family',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ]
            ]
        ]);

        $matchingidentifiers = $this->findIdentifiersForQuery->find($query);
        Assert::assertSame([
            'identifiers' => ['brand_kartell'],
            'matches_count' => 1
        ], $matchingidentifiers->normalize());
    }

    /**
     * @test
     */
    public function updated_date_filter()
    {
        $before = [
            'identifier'            => 'before',
            'asset_family_code' => 'date_asset_family',
            'code' => 'before',
            'asset_code_label_search' => ['fr_FR' => 'before_ref'],
            'asset_full_text_search'    => ['ecommerce' => ['fr_FR' => 'avant']],
            'updated_at' => date_create('2010-01-01')->getTimestamp(),
            'complete_value_keys' => [],
        ];

        $after = [
            'identifier'            => 'after',
            'asset_family_code' => 'date_asset_family',
            'code' => 'after',
            'asset_code_label_search' => ['fr_FR' => 'after_fre'],
            'asset_full_text_search'    => ['ecommerce' => ['fr_FR' => 'apres']],
            'updated_at' => date_create('2012-01-01')->getTimestamp(),
            'complete_value_keys' => [],
        ];

        $this->searchAssetIndexHelper->index([$before, $after]);

        $query = AssetQuery::createFromNormalized([
            'locale' => 'en_US',
            'channel' => 'ecommerce',
            'size' => 20,
            'page' => 0,
            'filters' => [
                [
                    'field' => 'updated',
                    'operator' => '>',
                    'value' => '2011-01-01T10:00:00+00:00'
                ],
                [
                    'field' => 'asset_family',
                    'operator' => '=',
                    'value' => 'date_asset_family',
                    'context' => []
                ]
            ]
        ]);

        $matchingidentifiers = $this->findIdentifiersForQuery->find($query);

        Assert::assertSame([
            'identifiers' => ['after'],
            'matches_count' => 1
        ], $matchingidentifiers->normalize());
    }

    /**
     * @test
     */
    public function uncomplete_assets_filter()
    {
        $query = AssetQuery::createFromNormalized([
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
                    'field' => 'asset_family',
                    'operator' => '=',
                    'value' => 'brand',
                    'context' => []
                ]
            ]
        ]);

        $matchingidentifiers = $this->findIdentifiersForQuery->find($query);
        Assert::assertSame([
            'identifiers' => ['brand_alessi', 'brand_bangolufsen'],
            'matches_count' => 2
        ], $matchingidentifiers->normalize());
    }

    /**
     * @test
     */
    public function paginated_by_search_after_search()
    {
        $query = AssetQuery::createPaginatedQueryUsingSearchAfter(
            AssetFamilyIdentifier::fromString('brand'),
            ChannelReference::noReference(),
            LocaleIdentifierCollection::empty(),
            10,
            AssetCode::fromString('alessi'),
            []
        );

        $matchingIdentifiers = $this->findIdentifiersForQuery->find($query);
        Assert::assertSame([
            'identifiers' => ['brand_bangolufsen', 'brand_kartell'],
            'matches_count' => 3
        ], $matchingIdentifiers->normalize());
        self::assertSame(['kartell'], $matchingIdentifiers->lastSortValue);
    }

    /**
     * @test
     */
    public function paginated_by_search_after_from_the_start_search()
    {
        $query = AssetQuery::createPaginatedQueryUsingSearchAfter(
            AssetFamilyIdentifier::fromString('brand'),
            ChannelReference::noReference(),
            LocaleIdentifierCollection::empty(),
            10,
            null,
            []
        );

        $matchingIdentifiers = $this->findIdentifiersForQuery->find($query);
        Assert::assertSame([
            'identifiers' => ['brand_alessi', 'brand_bangolufsen', 'brand_kartell'],
            'matches_count' => 3
        ], $matchingIdentifiers->normalize());
    }

    private function loadDataset()
    {
        // Those properties are not indexed
        $kartellCode = 'kartell';
        $kartellDescriptionEnUs = 'Kartell - The Culture of Plastics’’… In just over 50 years, this famous Italian company has revolutionised plastic, elevating it and propelling it into the refined world of luxury. Today, Kartell has more than a hundred showrooms all over the world and a good number of its creations have become cult pieces on display in the most prestigious museums. The famous Kartell Louis Ghost armchair has the most sales for armchairs in the world, with 1.5 million sales! Challenging the material, constantly researching new tactile, visual and aesthetic effects - Kartell faces every challenge! With more than 60 years of experience in dealing with plastic, the brand has a unique know-how and an unquenchable thirst for innovation. Kartellharnesses technological progress: notably, we owe them for the first totally transparent plastic chair, injection moulds, laser welding and more!';
        $kartellDesigner = 'Philippe Starck';
        $kartell = [
            'asset_family_code' => 'brand',
            'identifier'            => 'brand_kartell',
            'code' => $kartellCode,
            'asset_code_label_search' => ['en_US' => $kartellCode . ' ' . $kartellDesigner],
            'asset_full_text_search'  => ['ecommerce' => ['en_US' => $kartellCode . ' ' . $kartellDescriptionEnUs . ' ' . $kartellDesigner]],
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
            'asset_family_code' => 'brand',
            'identifier'            => 'brand_alessi',
            'code' => $alessiCode,
            'asset_code_label_search' => [
                'en_US' => $alessiCode . ' ' . $alessiDesigner,
                'fr_FR' => $alessiCode . ' Marcel Francais',
            ],
            'asset_full_text_search'          => ['ecommerce' => ['en_US' => $alessiCode . ' ' . $alessiDescriptionEnUs . ' ' . $alessiDesigner]],
            'updated_at' => date_create('2017-01-01')->format('Y-m-d'),
            'complete_value_keys' => [],
        ];

        // Those properties are not indexed
        $bangolufsenCode = 'bangolufsen';
        $bangolufsenDescriptionEnUs = <<<TEXT
B&O PLAY delivers stand-alone products with clear and simple operations - portable products that are intuitive to use, easy to integrate into your daily life, and deliver excellent high-quality experiences.

‘’We want to evoke senses, to elevate the experience of listening and watching. We have spoken to musicians and studio asseters who all love the fact that more people listen to music in more places, but hate the fact that the quality of the listening experience has been eroded. We want to provide the opportunity to experience media in a convenient and easy way but still in outstanding high quality.  Firmly grounded in our 88-year history in Bang & Olufsen, we interpret the same core values for a new type of contemporary products."
Are they the "special senses" ?
TEXT;
        $bangolufsenDesigner = 'Cecilie Manz';
        $bangolufsen = [
            'asset_family_code' => 'brand',
            'identifier'            => 'brand_bangolufsen',
            'code' => $bangolufsenCode,
            'asset_code_label_search' => ['en_US' => $bangolufsenCode . ' ' . $bangolufsenDesigner],
            'asset_full_text_search'    => ['ecommerce' => ['en_US' => $bangolufsenCode . ' ' . $bangolufsenDescriptionEnUs . ' ' . $bangolufsenDesigner]],
            'updated_at' => date_create('2016-01-01')->format('Y-m-d'),
            'complete_value_keys' => [
                'description_brand_fingerprint_ecommerce_en_US' => true,
                'founded_brand_fingerprint_ecommerce_en_US' => true,
            ],
        ];

        $wrongAssetFamily = [
            'identifier'            => 'another_asset_family',
            'asset_family_code' => 'manufacturer',
            'code' => 'manu_code',
            'asset_code_label_search' => ['fr_FR' => 'wrong_reference'],
            'asset_full_text_search'    => ['ecommerce' => ['fr_FR' => 'stark Designer supérieure']],
            'updated_at' => date_create('2010-01-01')->format('Y-m-d'),
            'complete_value_keys' => [],
        ];
        $this->searchAssetIndexHelper->index([$kartell, $alessi, $bangolufsen, $wrongAssetFamily]);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function createAssetFamilyWithAttributes(): void
    {
        $repository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');

        $assetFamilyDesigner = AssetFamily::create(
            AssetFamilyIdentifier::fromString('brand'),
            [],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $repository->create($assetFamilyDesigner);

        $description = TextAttribute::createText(
            AttributeIdentifier::create('brand', 'description', 'fingerprint'),
            AssetFamilyIdentifier::fromString('brand'),
            AttributeCode::fromString('description'),
            LabelCollection::fromArray(['en_US' => 'Description']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $founder = TextAttribute::createText(
            AttributeIdentifier::create('brand', 'founder', 'fingerprint'),
            AssetFamilyIdentifier::fromString('brand'),
            AttributeCode::fromString('founder'),
            LabelCollection::fromArray(['en_US' => 'Founder']),
            AttributeOrder::fromInteger(3),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $founded = TextAttribute::createText(
            AttributeIdentifier::create('brand', 'founded', 'fingerprint'),
            AssetFamilyIdentifier::fromString('brand'),
            AttributeCode::fromString('founded'),
            LabelCollection::fromArray(['en_US' => 'Founded']),
            AttributeOrder::fromInteger(4),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(155),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );

        $attributesRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $attributesRepository->create($description);
        $attributesRepository->create($founder);
        $attributesRepository->create($founded);
    }
}
