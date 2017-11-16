<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

class PimCatalogCompletenessIntegration extends AbstractPimCatalogTestCase
{
    public function testQueryCompleteOrIncompleteProductModel()
    {
        $this->assertDocument(
            $this->executeProductModelQuery('at_least_complete', 'ecommerce', 'en_US'),
            ['document_1', 'document_2']
        );

        $this->assertDocument(
            $this->executeProductModelQuery('at_least_complete', 'ecommerce', 'fr_FR'),
            ['document_2']
        );

        $this->assertDocument(
            $this->executeProductModelQuery('at_least_incomplete', 'ecommerce', 'en_US'),
            ['document_1']
        );

        $this->assertDocument(
            $this->executeProductModelQuery('at_least_incomplete', 'ecommerce', 'fr_FR'),
            []
        );
    }

    public function testQueryCompleteProductModelOrCompleteProduct()
    {
        $query = [
            'query' => [
                'bool' => [
                    'should' => [
                        ['term' => ['completeness.ecommerce.en_US' => 100]],
                        ['term' => ['at_least_complete.ecommerce.en_US' => 1]],
                    ],
                ],
            ],
        ];


        $result = $this->getSearchQueryResults($query);

        $this->assertDocument(['document_1', 'document_2', 'document_3'], $result);
    }

    public function testQueryIncompleteProductModelOrIncompleteProduct()
    {
        $query = [
            'query' => [
                'bool' => [
                    'should' => [
                        ['range' => ['completeness.ecommerce.fr_FR' => ['lt' => 100]]],
                        ['term' => ['at_least_incomplete.ecommerce.fr_FR' => 1]],
                    ],
                ],
            ],
        ];


        $result = $this->getSearchQueryResults($query);

        $this->assertDocument(['document_3'], $result);
    }

    /**
     * {@inheritdoc}
     */
    protected function addDocuments()
    {
        $products = [
            [
                'identifier' => 'document_1',
                'at_least_complete' => [
                    'ecommerce' => [
                        'en_US' => 1,
                        'fr_FR' => 0
                    ],
                ],
                'at_least_incomplete' => [
                    'ecommerce' => [
                        'en_US' => 1,
                        'fr_FR' => 0
                    ],
                ],
            ],
            [
                'identifier' => 'document_2',
                'at_least_complete' => [
                    'ecommerce' => [
                        'en_US' => 1,
                        'fr_FR' => 1,
                    ],
                ],
                'at_least_incomplete' => [
                    'ecommerce' => [
                        'en_US' => 0,
                        'fr_FR' => 0,
                    ],
                ],
            ],
            [
                'identifier' => 'document_3',
                'completeness' => [
                    'ecommerce' => [
                        'en_US' => 100,
                        'fr_FR' => 12,
                    ],
                ],
            ],
            [
                'identifier' => 'document_4',
            ],
        ];
        
        $this->indexDocuments($products);
    }

    /**
     * @param string $type
     * @param string $channel
     * @param string $locale
     *
     * @return array
     */
    private function executeProductModelQuery(string $type, string $channel, string $locale): array
    {
        $fieldName = sprintf('%s.%s.%s', $type, $channel, $locale);

        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'term' => [
                            $fieldName => 1,
                        ],
                    ],
                ],
            ],
        ];

        return $this->getSearchQueryResults($query);
    }
}
