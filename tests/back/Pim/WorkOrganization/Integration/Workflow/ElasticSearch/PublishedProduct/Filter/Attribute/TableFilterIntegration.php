<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\ElasticSearch\PublishedProduct\Filter\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

class TableFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /** @test */
    public function it_filters_published_products_with_not_empty_table_values()
    {
        $result = $this->executeFilter([['nutrition', Operators::IS_NOT_EMPTY, null]]);
        $this->assert($result, ['foo', 'baz']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttribute([
            'code' => 'nutrition',
            'type' => AttributeTypes::TABLE,
            'group' => 'other',
            'table_configuration' => [
                [
                    'code' => 'ingredient',
                    'data_type' => 'select',
                    'options' => [
                        ['code' => 'sugar'],
                        ['code' => 'salt'],
                    ],
                ],
                [
                    'code' => 'quantity',
                    'data_type' => 'number',
                ],
            ],
        ]);

        $this->esProductClient = $this->get('akeneo_elasticsearch.client.published_product');
        $publishedProductManager = $this->get('pimee_workflow.manager.published_product');

        $foo = $this->createProduct('foo', [
            'values' => [
                'nutrition' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => [['ingredient' => 'sugar', 'quantity' => 10]]
                    ]
                ]
            ]
        ]);
        $publishedProductManager->publish($foo);

        $bar = $this->createProduct('bar', ['enabled' => true]);
        $publishedProductManager->publish($bar);

        $baz = $this->createProduct('baz', [
            'values' => [
                'nutrition' => [
                    [
                        'locale' => null,
                        'scope' => null,
                        'data' => [['ingredient' => 'salt', 'quantity' => 5]]
                    ]
                ]
            ]
        ]);
        $publishedProductManager->publish($baz);

        $this->esProductClient->refreshIndex();
    }

    protected function executeFilter(array $filters): CursorInterface
    {
        $pqb = $this->get('pimee_workflow.doctrine.query.published_product_query_builder_factory')->create();

        foreach ($filters as $filter) {
            $pqb->addFilter($filter[0], $filter[1], $filter[2]);
        }

        return $pqb->execute();
    }
}
