<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Search\Elasticsearch\Record\Query;

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
                    'field' => 'search',
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
            'identifiers' => ['brand_bangolufsen', 'brand_alessi', 'brand_kartell'],
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
                    'field' => 'search',
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
            'identifiers' => ['brand_bangolufsen', 'brand_alessi', 'brand_kartell'],
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
                    'field' => 'search',
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

    private function loadDataset()
    {
        // Those properties are not indexed
        $kartellCode = 'kartell';
        $kartellDescriptionEnUs = 'Kartell - The Culture of Plastics’’… In just over 50 years, this famous Italian company has revolutionised plastic, elevating it and propelling it into the refined world of luxury. Today, Kartell has more than a hundred showrooms all over the world and a good number of its creations have become cult pieces on display in the most prestigious museums. The famous Kartell Louis Ghost armchair has the most sales for armchairs in the world, with 1.5 million sales! Challenging the material, constantly researching new tactile, visual and aesthetic effects - Kartell faces every challenge! With more than 60 years of experience in dealing with plastic, the brand has a unique know-how and an unquenchable thirst for innovation. Kartellharnesses technological progress: notably, we owe them for the first totally transparent plastic chair, injection moulds, laser welding and more!';
        $kartellDesigner = 'Philippe Starck';
        $kartell = [
            'reference_entity_code' => 'brand',
            'identifier'                  => 'brand_kartell',
            'code' => $kartellCode,
            'record_list_search'          => ['ecommerce' => ['en_US' => $kartellCode . ' ' . $kartellDescriptionEnUs . ' ' . $kartellDesigner]],
            'updated_at' => date_create('2018-01-01')->format('Y-m-d')
        ];

        // Those properties are not indexed
        $alessiCode = 'alessi';
        $alessiDescriptionEnUs = 'Alessi is truly a "dream factory"! This famous Italian brand has been enhancing our daily lives for more than 80 years thanks to its beautiful and functional items which are designed by leading architects and designers. At Alessi, design has been a family affair since 1921. Initially focusing on coffee services and trays, Alessi acquired international popularity during the 1950s through working with renowned architects and designers such as Ettore Sottsass.';
        $alessiDesigner = 'Marcel Wanders';
        $alessi = [
            'reference_entity_code' => 'brand',
            'identifier'                  => 'brand_alessi',
            'record_list_search'          => ['ecommerce' => ['en_US' => $alessiCode . ' ' . $alessiDescriptionEnUs . ' ' . $alessiDesigner]],
            'updated_at' => date_create('2017-01-01')->format('Y-m-d')
        ];

        // Those properties are not indexed
        $bangolufsenCode = 'bangolufsen';
        $bangolufsenDescriptionEnUs = <<<TEXT
B&O PLAY delivers stand-alone products with clear and simple operations - portable products that are intuitive to use, easy to integrate into your daily life, and deliver excellent high-quality experiences.

‘’We want to evoke senses, to elevate the experience of listening and watching. We have spoken to musicians and studio recorders who all love the fact that more people listen to music in more places, but hate the fact that the quality of the listening experience has been eroded. We want to provide the opportunity to experience media in a convenient and easy way but still in outstanding high quality.  Firmly grounded in our 88-year history in Bang & Olufsen, we interpret the same core values for a new type of contemporary products."
TEXT;
        $bangolufsenDesigner = 'Cecilie Manz';
        $bangolufsen = [
            'reference_entity_code' => 'brand',
            'identifier'            => 'brand_bangolufsen',
            'record_list_search'    => ['ecommerce' => ['en_US' => $bangolufsenCode . ' ' . $bangolufsenDescriptionEnUs . ' ' . $bangolufsenDesigner]],
            'updated_at' => date_create('2016-01-01')->format('Y-m-d')
        ];

        $wrongEnrichedEntity = [
            'identifier'            => 'another_reference_entity',
            'reference_entity_code' => 'manufacturer',
            'record_list_search'    => ['ecommerce' => ['fr_FR' => 'stark Designer supérieure']],
            'updated_at' => date_create('2010-01-01')->format('Y-m-d')
        ];
        $this->searchRecordIndexHelper->index([$kartell, $alessi, $bangolufsen, $wrongEnrichedEntity]);
    }
}
