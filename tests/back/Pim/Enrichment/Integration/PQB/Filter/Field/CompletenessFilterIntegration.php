<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * Test completeness filter with the operators '=', '!=', '<', '>'.
 *
 * Caution: We use product and product model query builder here
 *
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.catalog.fixture.completeness_filter')
            ->loadProductModelTree();
    }

    /**
     * Test the EQUALS filter
     */
    public function testOperatorEquals()
    {
        $result = $this->executeFilter([[
            'completeness',
            Operators::EQUALS,
            100,
            [
                'scope' => 'tablet',
                'locales' => ['fr_FR', 'en_US', 'de_DE']
            ]
        ]]);
        $this->assert($result, ['variant_product_1', 'variant_product_2', 'variant_product_3', 'variant_product_4']);

        $result = $this->executeFilter([[
            'completeness',
            Operators::EQUALS,
            100,
            [
                'scope' => 'tablet',
                'locale' => 'en_US'
            ]
        ]]);
        $this->assert($result, ['variant_product_1', 'variant_product_3']);

        $result = $this->executeFilter([[
            'completeness',
            Operators::EQUALS,
            100,
            [
                'scope' => 'tablet',
                'locale' => 'fr_FR'
            ]
        ]]);
        $this->assert($result, ['variant_product_2', 'variant_product_3', 'variant_product_4']);

        $result = $this->executeFilter([[
            'completeness',
            Operators::EQUALS,
            100,
            [
                'scope' => 'tablet',
                'locale' => 'de_DE'
            ]
        ]]);
        $this->assert($result, []);

        $result = $this->executeFilter([[
            'completeness',
            Operators::EQUALS,
            75,
            [
                'scope' => 'tablet',
                'locale' => 'fr_FR'
            ]
        ]]);
        $this->assert($result, ['simple_product', 'variant_product_1']);
    }

    /**
     * Test the GREATER filter
     */
    public function testOperatorGreaterThan()
    {
        $result = $this->executeFilter([[
            'completeness',
            Operators::GREATER_THAN,
            100,
            [
                'scope' => 'tablet',
                'locales' => ['fr_FR', 'en_US', 'de_DE']
            ]
        ]]);
        $this->assert($result, []);

        $result = $this->executeFilter([[
            'completeness',
            Operators::GREATER_THAN,
            100,
            [
                'scope' => 'ecommerce',
                'locale' => 'en_US'
            ]
        ]]);
        $this->assert($result, []);

        $result = $this->executeFilter([[
            'completeness',
            Operators::GREATER_THAN,
            75,
            [
                'scope' => 'tablet',
                'locale' => 'en_US'
            ]
        ]]);
        $this->assert($result, ['variant_product_1', 'variant_product_3']);
    }

    /**
     * Test the NOT EQUAL filter
     */
    public function testOperatorNotEqual()
    {
        $result = $this->executeFilter([[
            'completeness',
            Operators::NOT_EQUAL,
            100,
            [
                'scope' => 'tablet',
                'locales' => ['fr_FR', 'en_US', 'de_DE']
            ]
        ]]);
        $this->assert($result, ['variant_product_1', 'variant_product_2', 'variant_product_3', 'variant_product_4', 'simple_product']);

        $result = $this->executeFilter([[
            'completeness',
            Operators::NOT_EQUAL,
            50,
            [
                'scope' => 'tablet',
                'locale' => 'en_US'
            ]
        ]]);
        $this->assert($result, ['variant_product_1', 'variant_product_2', 'variant_product_3', 'variant_product_4', 'simple_product']);

        $result = $this->executeFilter([[
            'completeness',
            Operators::NOT_EQUAL,
            75,
            [
                'scope' => 'tablet',
                'locale' => 'fr_FR'
            ]
        ]]);
        $this->assert($result, ['variant_product_2', 'variant_product_3', 'variant_product_4']);

        $result = $this->executeFilter([[
            'completeness',
            Operators::NOT_EQUAL,
            33,
            [
                'scope' => 'ecommerce',
                'locale' => 'en_US'
            ]
        ]]);
        $this->assert($result, ['variant_product_1', 'variant_product_2', 'variant_product_3', 'variant_product_4', 'simple_product']);
    }

    /**
     * Test the LOWER filter
     */
    public function testOperatorLowerThan()
    {
        $result = $this->executeFilter([[
            'completeness',
            Operators::LOWER_THAN,
            100,
            [
                'scope' => 'tablet',
                'locales' => ['fr_FR', 'en_US', 'de_DE']
            ]
        ]]);
        $this->assert($result, ['variant_product_1', 'variant_product_2', 'variant_product_3', 'variant_product_4', 'simple_product']);

        $result = $this->executeFilter([[
            'completeness',
            Operators::LOWER_THAN,
            100,
            [
                'scope' => 'tablet',
                'locale' => 'en_US'
            ]
        ]]);
        $this->assert($result, ['variant_product_2', 'variant_product_4', 'simple_product']);

        $result = $this->executeFilter([[
            'completeness',
            Operators::LOWER_THAN,
            100,
            [
                'scope' => 'tablet',
                'locale' => 'fr_FR'
            ]
        ]]);
        $this->assert($result, ['variant_product_1', 'simple_product']);

        $result = $this->executeFilter([[
            'completeness',
            Operators::LOWER_THAN,
            100,
            [
                'scope' => 'ecommerce',
                'locale' => 'en_US'
            ]
        ]]);
        $this->assert($result, []);

        $result = $this->executeFilter([[
            'completeness',
            Operators::LOWER_THAN,
            75,
            [
                'scope' => 'tablet',
                'locales' => ['fr_FR', 'en_US', 'de_DE']
            ]
        ]]);
        $this->assert($result, []);
    }

    /**
     * All those queries need to be done with product and product model query builder.
     *
     * @param array $filters
     *
     * @return CursorInterface
     */
    protected function executeFilter(array $filters)
    {
        $pqb = $this->get('pim_catalog.query.product_and_product_model_query_builder_factory')->create();

        foreach ($filters as $filter) {
            $context = isset($filter[3]) ? $filter[3] : [];
            $pqb->addFilter($filter[0], $filter[1], $filter[2], $context);
        }

        return $pqb->execute();
    }
}
