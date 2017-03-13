<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use PHPUnit\Framework\TestCase;

/**
 * For each integration tests implemented in the subclass, this abstract:
 * - Resets the index (eg, removes the index configuration from ES and the documents indexed)
 * - It also provides function to make ES queries to that index and make sure the expected index are part of the result.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractPimCatalogIntegration extends TestCase
{
    /** TODO: Also could be generated from configuration */
    const INDEX_NAME = 'product_index_test';

    /** TODO: Maybe get this from configuration ? */
    const PRODUCT_TYPE = 'pim_catalog_product';

    /** @var Client */
    private $ESClient;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->ESClient = ClientBuilder::create()->build();
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->resetIndex();
        $this->addProducts();
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
    abstract protected function addProducts();

    /**
     * TODO: From ElasticSearchBundle but could be generated from the global configuration
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
                                                'type'       => 'keyword',
                                                'normalizer' => 'text_normalizer',
                                            ],
                                        ],
                                        'type'     => 'text',
                                        'analyzer' => 'text_analyzer',
                                    ],
                                    'match'              => '*-text',
                                ],
                            ],
                            [
                                'varchar' => [
                                    'match_mapping_type' => 'string',
                                    'mapping'            => [
                                        'type'     => 'keyword',
                                        'normalizer' => 'varchar_normalizer',
                                    ],
                                    'match'              => '*-varchar',
                                ],
                            ],
                            [
                                'pim_catalog_image' => [
                                    'match_mapping_type' => 'string',
                                    'mapping'            => [
                                        'type'     => 'string',
                                        'analyzer' => 'text_analyzer',
                                    ],
                                    'match'              => '*-pim_catalog_image',
                                ],
                            ],
                            [
                                'pim_catalog_date' => [
                                    'match_mapping_type' => 'date',
                                    'mapping'            => [
                                        'fields' => [
                                            'raw' => [
                                                'type'     => 'string',
                                                'analyzer' => 'text_analyzer',
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
                                                'analyzer' => 'text_analyzer',
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
                                                'analyzer' => 'text_analyzer',
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
                                                'analyzer' => 'text_analyzer',
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
                        'normalizer'  => [
                            'text_normalizer'    => [
                                // Value is htmlstripped and newline pattern processed prior to indexing (in the PIM)
                                // ES Normalizers is an experimental feature and they do not support
                                // html_strip and newlinepattern filters yet.
                                // see https://www.elastic.co/guide/en/elasticsearch/reference/5.2/analysis-normalizers.html#analysis-normalizers                                'type'        => 'custom',
                                'filter'      => ['lowercase'],
                                'char_filter' => [],
                            ],
                            'varchar_normalizer' => [
                                'filter' => ['lowercase'],
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
                            'text_analyzer'    => [
                                'filter'      => ['lowercase'],
                                'char_filter' => ['html_strip', 'newline_pattern'],
                                'type'        => 'custom',
                                'tokenizer'   => 'standard',
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
    protected function indexProducts(array $products)
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
    protected function createSearchQuery(array $searchClause, array $sortClauses = [])
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
    protected function getSearchQueryResults(array $query)
    {
        $skus = [];
        $response = $this->ESClient->search($query);

        foreach ($response['hits']['hits'] as $hit) {
            $skus[] = $hit['_source']['sku-varchar'];
        }

        return $skus;
    }

    /**
     * Checks that the products found are effectively expected
     *
     * @param array $productsFound
     * @param array $expectedProducts
     */
    protected function assertProducts(array $productsFound, array $expectedProducts)
    {
        $this->assertCount(count($expectedProducts), $productsFound);
        foreach ($expectedProducts as $productExpected) {
            $this->assertContains($productExpected, $productsFound);
        }
    }
}
