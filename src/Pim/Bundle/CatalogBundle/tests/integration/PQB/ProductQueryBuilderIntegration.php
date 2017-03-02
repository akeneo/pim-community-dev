<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Tests that queries results executed by combining filters with different operators are consistent.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQueryBuilderIntegration extends TestCase
{

    /**
     * Combines several filters and operator to find the product
     */
    public function testComplexQuery1()
    {
        $foundProductQuery = $this->get('pim_catalog.query.product_query_builder_factory')->create();
        $foundProductQuery->addFilter('family', Operators::IN_LIST, ['familyA']);
        $foundProductQuery->addFilter('a_file', Operators::ENDS_WITH, '.txt');
        $foundProductQuery->addFilter('a_localizable_image', Operators::CONTAINS, 'akeneo', ['locale' => 'en_US']);
        $foundProductQuery->addFilter('a_regexp', Operators::CONTAINS, '+', ['locale' => 'en_US']);
        $foundProductQuery->addFilter('a_scopable_price', Operators::GREATER_THAN, ['amount' => 13, 'currency' => 'USD'], ['scope' => 'ecommerce']);
        $foundProductQuery->addFilter('a_localized_and_scopable_text_area', Operators::EQUALS, 'Mon textarea localisé et scopable ecommerce', ['locale' => 'fr_FR', 'scope' => 'ecommerce']);

        $productsFound = $foundProductQuery->execute();
        $this->assertProductFound($productsFound);

        $notFoundProductQuery = $this->get('pim_catalog.query.product_query_builder_factory')->create();
        // Operator changed
        $foundProductQuery->addFilter('family', Operators::IS_EMPTY, null);

        $notFoundProductQuery->addFilter('a_file', Operators::STARTS_WITH, '.txt');
        $notFoundProductQuery->addFilter('a_localizable_image', Operators::CONTAINS, 'akeneo', ['locale' => 'en_US']);
        $notFoundProductQuery->addFilter('a_regexp', Operators::CONTAINS, '+', ['locale' => 'en_US']);
        $notFoundProductQuery->addFilter('a_scopable_price', Operators::GREATER_THAN, ['amount' => 13, 'currency' => 'USD'], ['scope' => 'ecommerce']);
        $notFoundProductQuery->addFilter('a_localized_and_scopable_text_area', Operators::EQUALS, 'Mon textarea localisé et scopable ecommerce', ['locale' => 'fr_FR', 'scope' => 'ecommerce']);

        $notFoundProducts = $notFoundProductQuery->execute();
        $this->assertCount(0, $notFoundProducts);
    }

    /**
     * Combines several filters to find the product (using EMPTY like operators)
     */
    public function testComplexQueryWithEmptyOperator()
    {
        $foundProductQuery = $this->get('pim_catalog.query.product_query_builder_factory')->create();
        $foundProductQuery->addFilter('categories', Operators::UNCLASSIFIED, null);
        $foundProductQuery->addFilter('a_scopable_price', Operators::IS_EMPTY, ['amount' => '', 'currency' => ''], ['scope' => 'tablet']);
        $foundProductQuery->addFilter('a_localized_and_scopable_text_area', Operators::IS_NOT_EMPTY, null, ['locale' => 'en_US', 'scope' => 'ecommerce']);

        $productsFound = $foundProductQuery->execute();
        $this->assertProductFound($productsFound);

        $notFoundProductQuery = $this->get('pim_catalog.query.product_query_builder_factory')->create();
        // Operator changed
        $notFoundProductQuery->addFilter('categories', Operators::IN_CHILDREN_LIST, ['master']);
        $foundProductQuery->addFilter('a_scopable_price', Operators::IS_EMPTY, ['amount' => '', 'currency' => ''], ['scope' => 'tablet']);
        $foundProductQuery->addFilter('a_localized_and_scopable_text_area', Operators::IS_NOT_EMPTY, null, ['locale' => 'en_US', 'scope' => 'ecommerce']);

        $notFoundProducts = $notFoundProductQuery->execute();
        $this->assertCount(0, $notFoundProducts);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalCatalogPath()],
            true
        );
    }

    /**
     * Product anatomy
     * - a field
     * - a non-scopable/non-localizable attribute
     * - a localizable attribute
     * - a locale specific attribute
     * - a scopable attribute
     * - a localizable/scopable attribute
     *
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $product = $this->get('pim_catalog.builder.product')->createProduct('complex_product_1', 'familyA');
        $this->get('pim_catalog.updater.product')->update(
            $product,
            [
                'values' => [
                    'a_file' => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => $this->getFixturePath('akeneo.txt'),
                        ],
                    ],

                    'a_localizable_image'                => [
                        [
                            'locale' => 'en_US',
                            'scope'  => null,
                            'data'   => $this->getFixturePath('akeneo.jpg'),
                        ],
                        [
                            'locale' => 'fr_FR',
                            'scope'  => null,
                            'data'   => $this->getFixturePath('akeneo.jpg'),
                        ],
                    ],
                    'a_regexp'                           => [
                        [
                            'locale' => null,
                            'scope'  => null,
                            'data'   => '\w+ .*',
                        ],
                    ],
                    'a_scopable_price'                   => [
                        [
                            'locale' => null,
                            'scope'  => 'ecommerce',
                            'data'   => [
                                [
                                    'amount'   => 12,
                                    'currency' => 'EUR',
                                ],
                                [
                                    'amount'   => 14,
                                    'currency' => 'USD',
                                ],
                            ],
                        ],
                    ],
                    'a_localized_and_scopable_text_area' => [
                        [
                            'locale' => 'fr_FR',
                            'scope'  => 'ecommerce',
                            'data'   => 'Mon textarea localisé et scopable ecommerce',
                        ],
                        [
                            'locale' => 'en_US',
                            'scope'  => 'ecommerce',
                            'data'   => 'My localizable and scopable textearea ecommerce',
                        ],
                        [
                            'locale' => 'fr_FR',
                            'scope'  => 'tablet',
                            'data'   => 'Mon textarea localisé et scopable tablet',
                        ],
                        [
                            'locale' => 'en_US',
                            'scope'  => 'tablet',
                            'data'   => 'My localizable and scopable textearea tablet',
                        ],
                    ],
                ],
            ]
        );

        $this->get('pim_catalog.saver.product')->save($product);
    }

    /**
     * @param $products
     */
    protected function assertProductFound($products)
    {
        $this->assertCount(1, $products);

        foreach ($products as $product) {
            $this->assertEquals('complex_product_1', $product->getIdentifier());
        }
    }
}
