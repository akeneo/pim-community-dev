<?php
declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Search\Elasticsearch\Asset;

use Akeneo\AssetManager\Integration\SearchIntegrationTestCase;
use PHPUnit\Framework\Assert;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SearchLinkedAssetIndexConfigurationTest extends SearchIntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadDataset();
    }

    /**
     * @test
     */
    public function it_finds_assets_linked_to_another_asset_identifier()
    {
        $query = [
            '_source' => '_id',
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'links.asset.brand' => 'brand_kartell',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $matchingIdentifiers = $this->searchAssetIndexHelper->executeQuery($query);
        Assert::assertSame(['designer_stark'], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function it_does_not_find_assets_if_it_is_not_linked()
    {
        $query = [
            '_source' => '_id',
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'links.asset.brand' => 'unknown_asset',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $matchingIdentifiers = $this->searchAssetIndexHelper->executeQuery($query);
        Assert::assertSame([], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function it_finds_all_assets_linked_to_a_specific_asset_family()
    {
        $query = [
            '_source' => '_id',
            'sort'    => ['updated_at' => 'desc'],
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'exists' => [
                                        'field' => 'links.asset.brand'
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $matchingIdentifiers = $this->searchAssetIndexHelper->executeQuery($query);
        Assert::assertSame(['designer_stark'], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function it_does_not_find_assets_linked_to_a_specific_asset_family()
    {
        $query = [
            '_source' => '_id',
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'exists' => [
                                        'field' => 'links.asset.unknown'
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $matchingIdentifiers = $this->searchAssetIndexHelper->executeQuery($query);
        Assert::assertSame([], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function it_finds_assets_linked_to_a_specific_attribute_option()
    {
        $query = [
            '_source' => '_id',
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => [
                                        'links.option.color_brand_fingerprint' => ['blue', 'yellow']
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $matchingIdentifiers = $this->searchAssetIndexHelper->executeQuery($query);
        Assert::assertSame(['designer_stark'], $matchingIdentifiers);
    }

    /**
     * @test
     */
    public function it_does_not_find_assets_linked_to_an_attribute_option()
    {
        $query = [
            '_source' => '_id',
            'query'   => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => [
                                        'links.option.color_brand_fingerprint' => ['yellow']
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $matchingIdentifiers = $this->searchAssetIndexHelper->executeQuery($query);
        Assert::assertSame([], $matchingIdentifiers);
    }

    private function loadDataset()
    {
        $from = [
            'asset_family_code'   => 'designer',
            'identifier'              => 'designer_stark',
            'code'                    => 'stark',
            'updated_at'              => date_create('2018-01-01')->getTimestamp(),
            'links'       => [
                'asset' => [
                    'brand' => ['brand_kartell'], // Link to a specific asset family and a specific asset
                ],
                'option' => [
                    'color_brand_fingerprint' => ['red', 'blue'] // link to a specific attribute and a specific attribute option
                ]
            ],
        ];
        $to = [
            'asset_family_code'   => 'brand',
            'identifier'              => 'brand_kartell',
            'code'                    => 'kartell',
            'updated_at'              => date_create('2018-01-01')->getTimestamp(),
            'links'       => [],
        ];

        $this->searchAssetIndexHelper->index([$from, $to]);
    }
}
