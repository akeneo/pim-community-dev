<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch;

use Elasticsearch\ClientBuilder;
use PHPUnit\Framework\TestCase;

/**
 * This integration tests checks that given an index configuration and some products indexed
 * the text research are consistent.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCatalogTextIndexConfigurationIntegration extends TestCase
{
    /** TODO: Also could be generated from configuration */
    const INDEX_NAME = 'product_index_test';

    /** TODO: Maybe get this from configuration ? */
    const PRODUCT_TYPE = 'pim_catalog_product';

    /** Client */
    private $ESClient;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->ESClient = ClientBuilder::create()->build();
    }

    public function setUp()
    {
        parent::setUp();

        $this->resetIndex();
        $this->addProducts();
    }

    public function testStartWithOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'name-varchar',
                            'query'         => 'an*',
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_2', 'product_5']);
    }

    public function testStartWithOperatorWithWhiteSpace()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'name-varchar',
                            'query'         => 'My\\ product*',
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1']);
    }

    public function testContainsOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'name-varchar',
                            'query'         => '*Love*',
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3', 'product_6', 'product_7', 'product_8']);
    }

    public function testContainsOperatorWithWhiteSpace()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'name-varchar',
                            'query'         => '*Love\\ this*',
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3', 'product_6']);
    }

    public function testDoesNotContainOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'must_not' => [
                        'query_string' => [
                            'default_field' => 'name-varchar',
                            'query'         => '*Love*',
                        ],
                    ],
                    'filter'   => [
                        'exists' => ['field' => 'name-varchar'],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_2', 'product_5']);
    }

    public function testEqualsOperator()
    {
        $query = $this->createSearchQuery(
            [
                'query' => [
                    'bool' => [
                        'filter' => [
                            'query_string' => [
                                'default_field' => 'name-varchar',
                                'query'         => 'I-love.dots',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_8']);
    }

    public function testEmptyOperator()
    {
        $query = $this->createSearchQuery(
            [
                'query' => [
                    'bool' => [
                        'must_not' => [
                            'exists' => ['field' => 'name-varchar'],
                        ],
                    ],
                ],
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_4']);
    }

    public function testSortAscending()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'match_all' => new \stdClass(),
            ],
            'sort'  => [
                [
                    'name-varchar.raw' => [
                        'order'   => 'asc',
                        'missing' => '_first',
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts(
            $productsFound,
            ['product_4', 'product_5', 'product_2', 'product_8', 'product_7', 'product_6', 'product_1', 'product_3']
        );
    }

    public function testSortDescending()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'match_all' => new \stdClass(),
            ],
            'sort'  => [
                [
                    'name-varchar.raw' => [
                        'order'   => 'desc',
                        'missing' => '_last',
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts(
            $productsFound,
            ['product_3', 'product_1', 'product_6', 'product_7', 'product_8', 'product_2', 'product_5', 'product_4']
        );
    }

    /**
     * Resets the index used for the integration tests query
     */
    private function resetIndex()
    {
        if ($this->ESClient->indices()->exists(['index' => self::INDEX_NAME])) {
            $this->ESClient->indices()->delete(['index' => self::INDEX_NAME]);
        }

        $this->ESClient->indices()->create($this->getProductIndexConfiguration());
    }

    /**
     * This method indexes dummy products in elastic search.
     */
    private function addProducts()
    {
        $products = [
            [
                'sku-pim_catalog_identifier' => 'product_1',
                'name-varchar'      => 'My product',
            ],
            [
                'sku-pim_catalog_identifier' => 'product_2',
                'name-varchar'      => 'Another product',
            ],
            [
                'sku-pim_catalog_identifier' => 'product_3',
                'name-varchar'      => 'Yeah, love this name',
            ],
            [
                'sku-pim_catalog_identifier' => 'product_4',
            ],
            [
                'sku-pim_catalog_identifier' => 'product_5',
                'name-varchar'      => 'And an uppercase NAME',
            ],
            [
                'sku-pim_catalog_identifier' => 'product_6',
                'name-varchar'      => 'Love this product',
            ],
            [
                'sku-pim_catalog_identifier' => 'product_7',
                'name-varchar'      => 'I.love.dots',
            ],
            [
                'sku-pim_catalog_identifier' => 'product_8',
                'name-varchar'      => 'I-love.dots',
            ],

        ];

        $this->indexProducts($products);
    }

    /**
     * TODO: From ElasticsearchBundle but could be generated from the global configuration
     *
     * Returns the full configuration for a product index
     *
     * @return array
     */
    private function getProductIndexConfiguration()
    {
        return [
            'index' => self::INDEX_NAME,
            'body'  => [
                'mappings' => [
                    'pim_catalog_product' => [
                        'dynamic_templates' => [
                            [
                                'text' => [
                                    'match_mapping_type' => 'string',
                                    'mapping'            => [
                                        'fields'   => [
                                            'raw' => [
                                                'type'            => 'string',
                                            ],
                                        ],
                                        'type'     => 'string',
                                        'analyzer' => 'pim_text_area_analyzer',
                                    ],
                                    'match'              => '*-text',
                                ],
                            ],
                            [
                                'varchar' => [
                                    'match_mapping_type' => 'string',
                                    'mapping'            => [
                                        'fields'   => [
                                            'raw' => [
                                                'type'       => 'keyword',
                                                'normalizer' => 'lowercase_normalizer',
                                            ],
                                        ],
                                        'type'     => 'text',
                                        'analyzer' => 'pim_text_analyzer',
                                    ],
                                    'match'              => '*-varchar',
                                ],
                            ],
                            [
                                'pim_catalog_identifier' => [
                                    'match_mapping_type' => 'string',
                                    'mapping'            => [
                                        'fields'   => [
                                            'raw' => [
                                                'type' => 'keyword',
                                            ],
                                        ],
                                        'type'     => 'string',
                                        'analyzer' => 'pim_text_analyzer',
                                    ],
                                    'match'              => '*-pim_catalog_identifier',
                                ],
                            ],
                            [
                                'pim_catalog_image' => [
                                    'match_mapping_type' => 'string',
                                    'mapping'            => [
                                        'type'     => 'string',
                                        'analyzer' => 'pim_text_analyzer',
                                    ],
                                    'match'              => '*-pim_catalog_image',
                                ],
                            ],
                            // Missing pim_catalog_file
                            [
                                'pim_catalog_date' => [
                                    'match_mapping_type' => 'date',
                                    'mapping'            => [
                                        'fields' => [
                                            'raw' => [
                                                'type'     => 'string',
                                                'analyzer' => 'pim_text_analyzer',
                                            ],
                                        ],
                                        'type'   => 'date',
                                        'format' => 'dateOptionalTime',
                                    ],
                                    'match'              => '*-pim_catalog_date',
                                ],
                            ],
                            [
                                'pim_catalog_number' => [
                                    'match_mapping_type' => 'long',
                                    'mapping'            => [
                                        'fields' => [
                                            'raw' => [
                                                'type'     => 'string',
                                                'analyzer' => 'pim_text_analyzer',
                                            ],
                                        ],
                                        'type'   => 'double',
                                    ],
                                    'match'              => '*-pim_catalog_number',
                                ],
                            ],
                            [
                                'pim_catalog_metric' => [
                                    'match_mapping_type' => 'double',
                                    'mapping'            => [
                                        'fields' => [
                                            'raw' => [
                                                'type'     => 'string',
                                                'analyzer' => 'pim_text_analyzer',
                                            ],
                                        ],
                                        'type'   => 'double',
                                    ],
                                    'match'              => '*-pim_catalog_metric',
                                ],
                            ],
                            [
                                'pim_catalog_boolean' => [
                                    'match_mapping_type' => 'string',
                                    'mapping'            => [
                                        'fields' => [
                                            'raw' => [
                                                'type'     => 'string',
                                                'analyzer' => 'pim_text_analyzer',
                                            ],
                                        ],
                                        'type'   => 'boolean',
                                    ],
                                    'match'              => '*-pim_catalog_boolean',
                                ],
                            ],
                        ],
                    ],
                ],
                'settings' => [
                    'analysis' => [
                        'normalizer' => [
                            'lowercase_normalizer' => [
                                "filter" => ["lowercase"],
                            ],
                        ],
                        'char_filter' => [
                            'newline_pattern' => [
                                'pattern'     => '\\n',
                                'type'        => 'pattern_replace',
                                'replacement' => '',
                            ],
                        ],
                        'analyzer'    => [
                            'pim_text_analyzer'      => [
                                'filter'    => [
                                    'lowercase',
                                ],
                                'type'      => 'custom',
                                'tokenizer' => 'keyword',
                            ],
                            'pim_text_area_analyzer' => [
                                'filter'      => [
                                    'standard',
                                ],
                                'char_filter' => 'html_strip',
                                'type'        => 'custom',
                                'tokenizer'   => 'standard',
                            ],
                            'pim_text_area_raw'      => [
                                'filter'      => [
                                    'lowercase',
                                ],
                                'char_filter' => [
                                    'html_strip',
                                    'newline_pattern',
                                ],
                                'type'        => 'custom',
                                'tokenizer'   => 'keyword',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Indexes the given list of products
     *
     * @param array $products
     */
    private function indexProducts(array $products)
    {
        $params = [];
        $params['index'] = self::INDEX_NAME;
        $params['type'] = self::PRODUCT_TYPE;

        foreach ($products as $product) {
            $productBody = [];

            foreach ($product as $field => $value) {
                $matches = [];
                if (preg_match('/^(.*)-option$/', $field, $matches)) {
//                $attributeCode = $matches[1];
//                $optionParams = $this->currentOptions[$attributeCode][$value];
//                unset($optionParams['code']);
//                $productBody[$field] = $optionParams;
                } elseif (preg_match('/^(.*)-options$/', $field, $matches)) {
//                $attributeCode = $matches[1];
//                $options = explode(',', $value);
//                $optionsParams = [];
//                foreach ($options as $option) {
//                    $optionParams = $this->currentOptions[$attributeCode][trim($option)];
//                    unset($optionParams['code']);
//                    $optionsParams[] = $optionParams;
//                }
//                $productBody[$field] = $optionsParams;
                } elseif (preg_match('/^(.*)-metric$/', $field, $matches)) {
                    $productBody[$field] = floatval($value);
                } elseif (preg_match('/^(.*)-number$/', $field, $matches)) {
                    $productBody[$field] = floatval($value);
                } else {
                    $productBody[$field] = $value;
                }
            }

            $params['body'] = $productBody;

            $this->ESClient->index($params);
        }

        $this->ESClient->indices()->refresh();
    }

    /**
     * Prepare a search query with the given clause
     *
     * @param array $searchClause
     * @param array $sortClauses
     *
     * @return array
     */
    private function createSearchQuery(array $searchClause, array $sortClauses = [])
    {
        $searchQuery = [
            'index' => self::INDEX_NAME,
            'type'  => self::PRODUCT_TYPE,
            'body'  => [],
        ];

        if (!empty($searchClause)) {
            $searchQuery['body'] = $searchClause;
        }

        if (!empty($sortClause)) {
            $searchQuery['body']['sort'] = $sortClauses;
        }

        return $searchQuery;
    }

    /**
     * Executes the given query and returns the list of skus found.
     *
     * @param array $query
     *
     * @return array
     */
    private function getSearchQueryResults(array $query)
    {
        $skus = [];
        $response = $this->ESClient->search($query);

        foreach ($response['hits']['hits'] as $hit) {
            $skus[] = $hit['_source']['sku-pim_catalog_identifier'];
        }

        return $skus;
    }

    /**
     * Checks that the products found are effectively expected
     *
     * @param array $productsFound
     * @param array $expectedProducts
     */
    private function assertProducts(array $productsFound, array $expectedProducts)
    {
        $this->assertCount(count($expectedProducts), $productsFound);
        foreach ($expectedProducts as $productExpected) {
            $this->assertContains($productExpected, $productsFound);
        }
    }
}
