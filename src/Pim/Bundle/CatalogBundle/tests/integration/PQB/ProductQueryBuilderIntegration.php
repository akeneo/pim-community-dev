<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;

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
        $foundProductQuery = $this->createPQBWithoutFamilyFilter();
        $foundProductQuery->addFilter('family', Operators::IN_LIST, ['familyA']);

        $productsFound = $foundProductQuery->execute();
        $this->assertCount(1, $productsFound);
        $this->assertEquals('complex_product_1', $productsFound->current()->getIdentifier());

        $notFoundProductQuery = $this->createPQBWithoutFamilyFilter();
        $notFoundProductQuery->addFilter('family', Operators::IS_EMPTY, null);

        $productsNotFound = $notFoundProductQuery->execute();
        $this->assertCount(0, $productsNotFound);
    }

    public function testComplexQuery2()
    {
        $foundProductQuery = $this->createPQBWithoutaLocalizedAndScopableTextAreaFilter();
        $foundProductQuery->addFilter(
            'a_localized_and_scopable_text_area',
            Operators::IS_EMPTY,
            'Mon textarea localisé et scopable ecommerce',
            ['locale' => 'en_US', 'scope' => 'ecommerce']
        );

        $productsFound = $foundProductQuery->execute();
        $this->assertCount(1, $productsFound);
        $this->assertEquals('complex_product_1', $productsFound->current()->getIdentifier());

        $notFoundProductQuery = $this->createPQBWithoutaLocalizedAndScopableTextAreaFilter();
        $notFoundProductQuery->addFilter(
            'a_localized_and_scopable_text_area',
            Operators::STARTS_WITH,
            'My',
            ['locale' => 'en_US', 'scope' => 'ecommerce']
        );

        $productsNotFound = $notFoundProductQuery->execute();
        $this->assertCount(0, $productsNotFound);
    }

    /**
     * Combines several filters to find the product (using EMPTY like operators)
     */
    public function testComplexQueryWithEmptyOperator()
    {
        $foundProductQuery = $this->createPQBWithoutACategoriesFilter();
        $foundProductQuery->addFilter('categories', Operators::UNCLASSIFIED, null);

        $productsFound = $foundProductQuery->execute();
        $this->assertCount(1, $productsFound);
        $this->assertEquals('complex_product_1', $productsFound->current()->getIdentifier());

        $notFoundProductQuery = $this->createPQBWithoutACategoriesFilter();
        $notFoundProductQuery->addFilter('categories', Operators::IN_CHILDREN_LIST, ['master']);

        $productsNotFound = $notFoundProductQuery->execute();
        $this->assertCount(0, $productsNotFound);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalCatalogPath()]);
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
                            'data'   => null,
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
     * @return ProductQueryBuilderInterface
     */
    protected function createPQBWithoutFamilyFilter()
    {
        $pqb = $this->get('pim_catalog.query.product_query_builder_factory')->create();
        $pqb->addFilter('a_file', Operators::ENDS_WITH, '.txt');
        $pqb->addFilter('a_localizable_image', Operators::CONTAINS, 'akeneo', ['locale' => 'en_US']);
        $pqb->addFilter('a_regexp', Operators::CONTAINS, '+', ['locale' => 'en_US']);
        $pqb->addFilter(
            'a_scopable_price',
            Operators::GREATER_THAN,
            ['amount' => 13, 'currency' => 'USD'],
            ['scope' => 'ecommerce']
        );
        $pqb->addFilter(
            'a_localized_and_scopable_text_area',
            Operators::EQUALS,
            'Mon textarea localisé et scopable ecommerce',
            ['locale' => 'fr_FR', 'scope' => 'ecommerce']
        );

        return $pqb;
    }

    /**
     * @return ProductQueryBuilderInterface
     */
    protected function createPQBWithoutaLocalizedAndScopableTextAreaFilter()
    {
        $pqb = $this->get('pim_catalog.query.product_query_builder_factory')->create();
        $pqb->addFilter('family', Operators::IN_LIST, ['familyA']);
        $pqb->addFilter('a_file', Operators::ENDS_WITH, '.txt');
        $pqb->addFilter('a_localizable_image', Operators::CONTAINS, 'akeneo', ['locale' => 'en_US']);
        $pqb->addFilter('a_regexp', Operators::CONTAINS, '+', ['locale' => 'en_US']);
        $pqb->addFilter(
            'a_scopable_price',
            Operators::GREATER_THAN,
            ['amount' => 13, 'currency' => 'USD'],
            ['scope' => 'ecommerce']
        );

        return $pqb;
    }

    /**
     * @return ProductQueryBuilderInterface
     */
    protected function createPQBWithoutACategoriesFilter()
    {
        $pqb = $this->get('pim_catalog.query.product_query_builder_factory')->create();
        $pqb->addFilter(
            'a_scopable_price',
            Operators::IS_EMPTY,
            ['amount' => '', 'currency' => ''],
            ['scope' => 'tablet']
        );
        $pqb->addFilter(
            'a_localized_and_scopable_text_area',
            Operators::IS_NOT_EMPTY,
            null,
            ['locale' => 'fr_FR', 'scope' => 'ecommerce']
        );

        return $pqb;
    }
}
