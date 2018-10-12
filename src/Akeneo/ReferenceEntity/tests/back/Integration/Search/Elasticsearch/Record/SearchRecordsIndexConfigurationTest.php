<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Integration\Search\Elasticsearch\Record;

use Akeneo\ReferenceEntity\Integration\SearchIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * **The idea of the search model is the following:**
 *
 * A user who wants to search records given:
 * - A reference entity identifier
 * - A channel
 * - A locale
 *
 * The search request generated will search on those fields:
 * - Code
 * - Label of the given locale
 * - All values who are not localizable / not scopable
 * - All values who are localizable on the given locale
 * - All values who are scopable on the given channel
 *
 * **Therefore, the indexing model is as follow:**
 *
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchRecordsIndexConfigurationTest extends SearchIntegrationTestCase
{
    // Properties
    private const REFERENCE_ENTITY_IDENTIFIER = 'reference_entity_identifier';
    private const CODE = 'code';
    private const IDENTIFIER = 'identifier';

    // value keys
    private const DESIGNER = 'value_key-designer';
    private const DESCRIPTION_EN_US = 'value_key-description_en_US';
    private const DESCRIPTION_FR_FR = 'value_key-description_fr_FR';
    private const BIOGRAPHY_MOBILE_EN_US = 'value_key-biography_mobile_en_US';
    private const BIOGRAPHY_MOBILE_FR_FR = 'value_key-biography_mobile_fr_FR';

    private const UPDATED_AT = 'updated_at';

    public function setUp()
    {
        parent::setUp();

        $this->loaddataset();
    }

    /**
     * @test
     */
    public function default_search()
    {
        $matchingIdentifiers = $this->searchIndexHelper->executeQuery(
            [
                '_source' => '_id',
                'sort'    => [self::UPDATED_AT => 'desc']
            ]
        );
        Assert::assertSame(
            ['brand_kartell', 'brand_alessi', 'brand_bangolufsen', 'another_reference_entity_wrong_record'],
            $matchingIdentifiers
        );
    }

    /**
     * @test
     *      Search 'year' on ecommerce en_US
     *      will search on:
     *          - Code
     *          - Label en_US
     *          - All values who are not localizable / not scopable
     *          - All values who are localizable on en_US
     *          - All values who are scopable on ecommerce
     */
    public function simple_search()
    {
        $matchingIdentifiers = $this->searchIndexHelper->executeQuery(
            [
                '_source' => '_id',
                'sort'    => [self::IDENTIFIER => 'asc'],
                'query'   => [
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'filter' => [
                                    [
                                        'term' => [
                                            self::REFERENCE_ENTITY_IDENTIFIER => 'brand',
                                        ],
                                    ],
                                    [
                                        'query_string' => [
                                            'fields' => [
                                                self::CODE,
                                                'labels.en_US',
                                                self::DESIGNER,
                                                self::DESCRIPTION_EN_US,
                                            ],
                                            'query'  => '*year*',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        Assert::assertSame(['brand_alessi', 'brand_bangolufsen', 'brand_kartell'], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function insensitve_search()
    {
        $matchingIdentifiers = $this->searchIndexHelper->executeQuery(
            [
                '_source' => '_id',
                'sort'    => [self::IDENTIFIER => 'asc'],
                'query'   => [
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'filter' => [
                                    [
                                        'term' => [
                                            self::REFERENCE_ENTITY_IDENTIFIER => 'brand',
                                        ],
                                    ],
                                    [
                                        'query_string' => [
                                            'fields' => [
                                                self::CODE,
                                                'labels.en_US',
                                                self::DESIGNER,
                                                self::DESCRIPTION_EN_US,
                                            ],
                                            'query'  => '*Year*',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        Assert::assertSame(['brand_alessi', 'brand_bangolufsen', 'brand_kartell'], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function partial_match_search()
    {
        $matchingIdentifiers = $this->searchIndexHelper->executeQuery(
            [
                '_source' => '_id',
                'sort'    => [self::IDENTIFIER => 'asc'],
                'query'   => [
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'filter' => [
                                    [
                                        'term' => [
                                            self::REFERENCE_ENTITY_IDENTIFIER => 'brand',
                                        ],
                                    ],
                                    [
                                        'query_string' => [
                                            'fields' => [
                                                self::CODE,
                                                'labels.en_US',
                                                self::DESIGNER,
                                                self::DESCRIPTION_EN_US,
                                            ],
                                            'query'  => '*play*',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        Assert::assertSame(['brand_bangolufsen', 'brand_kartell'], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function exact_matching_search()
    {
        $matchingIdentifiers = $this->searchIndexHelper->executeQuery(
            [
                '_source' => '_id',
                'sort'    => [self::IDENTIFIER => 'asc'],
                'query'   => [
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'filter' => [
                                    [
                                        'term' => [
                                            self::REFERENCE_ENTITY_IDENTIFIER => 'brand',
                                        ],
                                    ],
                                    [
                                        'query_string' => [
                                            'fields' => [
                                                self::CODE,
                                                'labels.en_US',
                                                self::DESIGNER,
                                                self::DESCRIPTION_EN_US,
                                            ],
                                            'query'  => '*display*',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        Assert::assertSame(['brand_kartell'], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function two_words_search()
    {
        $matchingIdentifiers = $this->searchIndexHelper->executeQuery(
            [
                '_source' => '_id',
                'sort'    => [self::IDENTIFIER => 'asc'],
                'query'   => [
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'filter' => [
                                    [
                                        'term' => [
                                            self::REFERENCE_ENTITY_IDENTIFIER => 'brand',
                                        ],
                                    ],
                                    [
                                        'query_string' => [
                                            'fields' => [
                                                self::CODE,
                                                'labels.en_US',
                                                self::DESIGNER,
                                                self::DESCRIPTION_EN_US,
                                            ],
                                            'query'  => '*experience* AND *senses*',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        Assert::assertSame(['brand_bangolufsen'], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function another_two_words_search()
    {
        $matchingIdentifiers = $this->searchIndexHelper->executeQuery(
            [
                '_source' => '_id',
                'sort'    => [self::IDENTIFIER => 'asc'],
                'query'   => [
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'filter' => [
                                    [
                                        'term' => [
                                            self::REFERENCE_ENTITY_IDENTIFIER => 'brand',
                                        ],
                                    ],
                                    [
                                        'query_string' => [
                                            'fields' => [
                                                self::CODE,
                                                'labels.en_US',
                                                self::DESIGNER,
                                                self::DESCRIPTION_EN_US,
                                            ],
                                            'query'  => '*experience* AND *starck*',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        Assert::assertSame(['brand_kartell'], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function search_on_info_from_labels()
    {
        $matchingIdentifiers = $this->searchIndexHelper->executeQuery(
            [
                '_source' => '_id',
                'sort'    => [self::IDENTIFIER => 'asc'],
                'query'   => [
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'filter' => [
                                    [
                                        'term' => [
                                            self::REFERENCE_ENTITY_IDENTIFIER => 'brand',
                                        ],
                                    ],
                                    [
                                        'query_string' => [
                                            'fields' => [
                                                self::CODE,
                                                'labels.en_US',
                                                self::DESIGNER,
                                                self::DESCRIPTION_EN_US,
                                            ],
                                            'query'  => '*Bang* AND *Olufsen*',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        Assert::assertSame(['brand_bangolufsen'], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function search_on_info_from_labels_inverted_order()
    {
        $matchingIdentifiers = $this->searchIndexHelper->executeQuery(
            [
                '_source' => '_id',
                'sort'    => [self::IDENTIFIER => 'asc'],
                'query'   => [
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'filter' => [
                                    [
                                        'term' => [
                                            self::REFERENCE_ENTITY_IDENTIFIER => 'brand',
                                        ],
                                    ],
                                    [
                                        'query_string' => [
                                            'fields' => [
                                                self::CODE,
                                                'labels.en_US',
                                                self::DESIGNER,
                                                self::DESCRIPTION_EN_US,
                                            ],
                                            'query'  => '*Olufsen* AND *Bang*',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        Assert::assertSame(['brand_bangolufsen'], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function search_on_info_from_code()
    {
        $matchingIdentifiers = $this->searchIndexHelper->executeQuery(
            [
                '_source' => '_id',
                'sort'    => [self::IDENTIFIER => 'asc'],
                'query'   => [
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'filter' => [
                                    [
                                        'term' => [
                                            self::REFERENCE_ENTITY_IDENTIFIER => 'brand',
                                        ],
                                    ],
                                    [
                                        'query_string' => [
                                            'fields' => [
                                                self::CODE,
                                                'labels.en_US',
                                                self::DESIGNER,
                                                self::DESCRIPTION_EN_US,
                                            ],
                                            'query'  => '*bangolufsen*',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        Assert::assertSame(['brand_bangolufsen'], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function composed_words()
    {
        $matchingIdentifiers = $this->searchIndexHelper->executeQuery(
            [
                '_source' => '_id',
                'sort'    => [self::IDENTIFIER => 'asc'],
                'query'   => [
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'filter' => [
                                    [
                                        'term' => [
                                            self::REFERENCE_ENTITY_IDENTIFIER => 'brand',
                                        ],
                                    ],
                                    [
                                        'query_string' => [
                                            'fields' => [
                                                self::CODE,
                                                'labels.en_US',
                                                self::DESIGNER,
                                                self::DESCRIPTION_EN_US,
                                            ],
                                            'query'  => '*88* AND *year*',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        Assert::assertSame(['brand_bangolufsen'], $matchingIdentifiers);
    }

    private function loaddataset()
    {
        $this->searchIndexHelper->resetindex();

        $kartell = [
            self::REFERENCE_ENTITY_IDENTIFIER => 'brand',
            self::IDENTIFIER                  => 'brand_kartell',
            self::CODE                        => 'kartell',
            'labels' => [
                'en_US' => 'Kartell'
            ],
            self::DESCRIPTION_EN_US      => 'kartell - the culture of plastics’’… in just over 50 years, this famous italian company has revolutionised plastic, elevating it and propelling it into the refined world of luxury. today, kartell has more than a hundred showrooms all over the world and a good number of its creations have become cult pieces on display in the most prestigious museums. the famous kartell louis ghost armchair has the most sales for armchairs in the world, with 1.5 million sales! challenging the material, constantly researching new tactile, visual and aesthetic effects - kartell faces every challenge! with more than 60 years of experience in dealing with plastic, the brand has a unique know-how and an unquenchable thirst for innovation. kartellharnesses technological progress: notably, we owe them for the first totally transparent plastic chair, injection moulds, laser welding and more!',
            self::DESCRIPTION_FR_FR      => 'kartell - La culture du plastique’’… en 50 ans, cette entreprise fabuleuse à révolutioné le plastique',
            self::DESIGNER               => 'philippe starck',
            self::BIOGRAPHY_MOBILE_EN_US => 'kartell was born in italy',
            self::BIOGRAPHY_MOBILE_FR_FR => 'kartell est née à italy',
            self::UPDATED_AT             => date_create('2018-01-01')->format('Y-m-d')
        ];

        $alessi = [
            self::REFERENCE_ENTITY_IDENTIFIER => 'brand',
            self::IDENTIFIER                  => 'brand_alessi',
            self::CODE                        => 'alessi',
            'labels'                          => [
                'en_US' => 'Alessi',
            ],
            self::DESCRIPTION_EN_US           => 'alessi is truly a "dream factory"! this famous italian brand has been enhancing our daily lives for more than 80 years thanks to its beautiful and functional items which are designed by leading architects and designers. at alessi, design has been a family affair since 1921. initially focusing on coffee services and trays, alessi acquired international popularity during the 1950s through working with renowned architects and designers such as ettore sottsass.',
            self::DESCRIPTION_FR_FR           => 'alessi est vraiment une fabrique de rêve!',
            self::DESIGNER                    => 'marcel wanders',
            self::BIOGRAPHY_MOBILE_EN_US      => 'Alessi was founded in 1921 by Giovanni Aless',
            self::BIOGRAPHY_MOBILE_FR_FR      => 'Alessi est fondée en 1921 par Giovanni Aless',
            self::UPDATED_AT                  => date_create('2017-01-01')->format('Y-m-d'),
        ];

        $bangolufsendescriptionenus = <<<text
b&o play delivers stand-alone products with clear and simple operations - portable products that are intuitive to use, easy to integrate into your daily life, and deliver excellent high-quality experiences.

‘’we want to evoke senses, to elevate the experience of listening and watching. we have spoken to musicians and studio recorders who all love the fact that more people listen to music in more places, but hate the fact that the quality of the listening experience has been eroded. we want to provide the opportunity to experience media in a convenient and easy way but still in outstanding high quality.  firmly grounded in our 88-year history in bang & olufsen, we interpret the same core values for a new type of contemporary products."
text;
        $bangolufsen = [
            self::REFERENCE_ENTITY_IDENTIFIER => 'brand',
            self::IDENTIFIER                  => 'brand_bangolufsen',
            self::CODE                        => 'bangolufsen',
            'labels'                          => [
                'en_US' => 'Bang & Olufsen'
            ],
            self::DESCRIPTION_EN_US           => $bangolufsendescriptionenus,
            self::DESCRIPTION_FR_FR           => 'B&O play delivre des produits simple clair et unique',
            self::DESIGNER                    => 'cecilie manz',
            self::BIOGRAPHY_MOBILE_EN_US      => 'It is a danish company',
            self::BIOGRAPHY_MOBILE_FR_FR      => 'C\'est une société Danoise',
            self::UPDATED_AT                  => date_create('2016-01-01')->format('Y-m-d'),
        ];

        $wrongReferenceEntity = [
            self::IDENTIFIER                  => 'another_reference_entity_wrong_record',
            self::REFERENCE_ENTITY_IDENTIFIER => 'manufacturer',
            self::CODE                        => 'another_code',
            self::DESCRIPTION_EN_US           => '',
            self::DESIGNER                    => '',
            self::UPDATED_AT                  => date_create('2010-01-01')->format('Y-m-d'),
        ];
        $this->searchIndexHelper->index([$kartell, $alessi, $bangolufsen, $wrongReferenceEntity]);
    }
}
