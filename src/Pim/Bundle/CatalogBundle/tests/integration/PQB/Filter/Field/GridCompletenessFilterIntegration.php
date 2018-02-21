<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Pim\Bundle\CatalogBundle\tests\assert\AssertEntityWithValues;
use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * Test that the right product and product model are found depending
 *  - the completeness for a variant product
 *  - the number of complete variant product
 *
 * Caution: We use product and product model query builder here
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GridCompletenessFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->getFromTestContainer('akeneo_integration_tests.catalog.fixture.completeness_filter')
            ->loadProductModelTree();

        sleep(2);
    }

    /**
     * Test the AT LEAST COMPLETE filter
     */
    public function testCompleteOperator()
    {
        $result = $this->executeFilter([[
            'completeness',
            Operators::AT_LEAST_COMPLETE,
            null,
            [
                'locale' => 'en_US',
                'scope' => 'ecommerce',
            ]
        ]]);

        $assert = new AssertEntityWithValues(
            [
                'simple_product',
                'root_product_model_one_level',
                'root_product_model_two_level',
            ],
            iterator_to_array($result),
            'The right complete variant products / product models did not be found (channel: ecommerce, locale: en_US).'
        );

        $assert->same();
    }

    /**
     * Test the AT LEAST INCOMPLETE filter
     */
    public function testIncompleteOperator()
    {
        $result = $this->executeFilter([[
            'completeness',
            Operators::AT_LEAST_INCOMPLETE,
            null,
            [
                'locale' => 'fr_FR',
                'scope' => 'tablet',
            ]
        ]]);

        $assert = new AssertEntityWithValues(
            [
                'simple_product',
                'root_product_model_two_level'
            ],
            iterator_to_array($result),
            'The right incomplete variant products / product models did not be found (channel: tablet, locale: fr_FR).'
        );

        $assert->same();
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
            'The right complete variant products / product models did not be found (channel: ecommerce, locale: en_US).'
        );

        $assert->same();
    }

    /**
     * The filter expect a non empty locale
     *
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "completeness" expects a valid locale.
     */
    public function testErrorLocaleIsNotMissing()
    {
        $this->executeFilter([['completeness', Operators::AT_LEAST_COMPLETE, null, ['scope' => 'ecommerce']]]);
    }
    /**
     * The filter expect a non empty channel
     *
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "completeness" expects a valid channel.
     */
    public function testErrorChannelIsNotMissing()
    {
        $this->executeFilter([['completeness', Operators::AT_LEAST_COMPLETE, null, ['locale' => 'en_US']]]);
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
        $pqb = $this->get('pim_enrich.query.product_and_product_model_query_builder_from_size_factory')->create(
            ['limit' => 100]
        );

        foreach ($filters as $filter) {
            $context = isset($filter[3]) ? $filter[3] : [];
            $pqb->addFilter($filter[0], $filter[1], $filter[2], $context);
        }

        return $pqb->execute();
    }
}
