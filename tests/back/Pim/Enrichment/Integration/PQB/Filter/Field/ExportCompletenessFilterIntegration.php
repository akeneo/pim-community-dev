<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use AkeneoTest\Pim\Enrichment\Integration\Assert\AssertEntityWithValues;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * Test that the right product and product model are found depending
 *  - the completeness for a variant product
 *  - the number of complete variant product
 *
 * Caution: We use product and product model query builder here
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportCompletenessFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.catalog.fixture.completeness_filter')
            ->loadProductModelTree();
    }

    /**
     * Test the AT LEAST COMPLETE filter on all locale
     */
    public function testCompleteOnAllLocaleOperator()
    {
        $result = $this->executeFilter([[
            'completeness',
            Operators::AT_LEAST_COMPLETE,
            null,
            [
                'locale' => null,
                'scope' => 'tablet',
                'locales' => ['fr_FR', 'en_US', 'de_DE']
            ]
        ]]);

        $assert = new AssertEntityWithValues(
            [
                'sub_product_model',
                'root_product_model_one_level',
                'root_product_model_two_level',
            ],
            iterator_to_array($result),
            'The right complete variant products / product models did not be found (channel: ecommerce, locale: en_US, fr_FR, de_DE).'
        );

        $assert->same();
    }

    /**
     * Test the AT LEAST INCOMPLETE filter on all locale
     */
    public function testIncompleteOnAllLocaleOperator()
    {
        $result = $this->executeFilter([[
            'completeness',
            Operators::AT_LEAST_INCOMPLETE,
            null,
            [
                'locale' => null,
                'scope' => 'tablet',
                'locales' => ['fr_FR', 'en_US', 'de_DE']
            ]
        ]]);

        $assert = new AssertEntityWithValues(
            [
                'sub_product_model',
                'root_product_model_one_level',
                'root_product_model_two_level',
            ],
            iterator_to_array($result),
            'The right complete variant products / product models did not be found (channel: ecommerce, locale: en_US, fr_FR, de_DE).'
        );

        $assert->same();
    }

    /**
     * Test the ALL COMPLETE filter on all locale
     */
    public function testAllCompleteOnAllLocaleOperator()
    {
        $result = $this->executeFilter([[
            'completeness',
            Operators::ALL_COMPLETE,
            null,
            [
                'locale' => null,
                'scope' => 'tablet',
                'locales' => ['fr_FR', 'en_US', 'de_DE']
            ]
        ]]);

        $assert = new AssertEntityWithValues(
            [],
            iterator_to_array($result),
            'The right complete variant products / product models did not be found (channel: ecommerce, locale: en_US, fr_FR, de_DE).'
        );

        $assert->same();
    }

    /**
     * Test the ALL INCOMPLETE filter on all locale
     */
    public function testAllIncompleteOnAllLocaleOperator()
    {
        $result = $this->executeFilter([[
            'completeness',
            Operators::ALL_INCOMPLETE,
            null,
            [
                'locale' => null,
                'scope' => 'tablet',
                'locales' => ['fr_FR', 'en_US', 'de_DE']
            ]
        ]]);

        $assert = new AssertEntityWithValues(
            [],
            iterator_to_array($result),
            'The right complete variant products / product models did not be found (channel: ecommerce, locale: en_US, fr_FR, de_DE).'
        );

        $assert->same();
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
        $pqb = $this->get('pim_catalog.query.product_model_query_builder_factory')->create();

        foreach ($filters as $filter) {
            $context = isset($filter[3]) ? $filter[3] : [];
            $pqb->addFilter($filter[0], $filter[1], $filter[2], $context);
        }

        return $pqb->execute();
    }
}
