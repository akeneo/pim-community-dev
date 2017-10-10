<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

class PimCatalogCompletenessIntegration extends AbstractPimCatalogTestCase
{
    public function testCompletenessFilter()
    {
        $this->assertDocument(
            $this->executeQuery('at_least_complete', 'ecommerce', 'en_US'),
            ['document_1', 'document_2']
        );

        $this->assertDocument(
            $this->executeQuery('at_least_complete', 'ecommerce', 'fr_FR'),
            ['document_2']
        );

        $this->assertDocument(
            $this->executeQuery('at_least_incomplete', 'ecommerce', 'en_US'),
            ['document_1']
        );

        $this->assertDocument(
            $this->executeQuery('at_least_incomplete', 'ecommerce', 'fr_FR'),
            []
        );
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
        ];
        
        $this->indexProductDocuments($products);
    }

    /**
     * @param string $type
     * @param string $channel
     * @param string $locale
     *
     * @return array
     */
    private function executeQuery(string $type, string $channel, string $locale): array
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
