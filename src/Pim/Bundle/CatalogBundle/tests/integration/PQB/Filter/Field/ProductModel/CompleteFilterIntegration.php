<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter;

use Pim\Bundle\CatalogBundle\tests\assert\AssertEntityWithValues;
use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompleteFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->getFromTestContainer('akeneo_integration_tests.catalog.fixture.completeness_filter')
            ->loadProductModelTree();
    }

    public function testCompleteOperator()
    {
        $assert = new AssertEntityWithValues(
            [
                'simple_product',
                'root_product_model_one_level',
                'root_product_model_two_level',
            ],
            iterator_to_array($this->executeFilter([['completeness', Operators::AT_LEAST_COMPLETE, 'en_US', 'ecommerce']]))
        );

        $assert->same();
    }

    public function testIncompleteOperator()
    {
        $assert = new AssertEntityWithValues(
            [
                'simple_product',
                'sub_product_model',
            ],
            iterator_to_array($this->executeFilter([['completeness', Operators::AT_LEAST_INCOMPLETE, 'en_US', 'tablet']]))
        );

        $assert->same();
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "completeness" expects a valid scope.
     */
    public function testErrorLocalesIsMissing()
    {
        $this->executeFilter([['completeness', Operators::AT_LEAST_COMPLETE, null]]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Property "completeness" expects a valid scope.
     */
    public function testErrorScopeIsMissing()
    {
        $this->executeFilter([['completeness', Operators::AT_LEAST_COMPLETE, null]]);
    }
}
