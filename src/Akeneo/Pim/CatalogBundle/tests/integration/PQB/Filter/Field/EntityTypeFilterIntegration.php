<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductAndProductModelQueryBuilderTestCase;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityTypeFilterIntegration extends AbstractProductAndProductModelQueryBuilderTestCase
{
    public function test_filters_only_product_entities()
    {
        $result = $this->executeFilter([['entity_type', Operators::EQUALS, ProductInterface::class]]);
        $this->assertEquals(242, $result->count());
    }

    public function test_filters_only_product_model_entities()
    {
        $result = $this->executeFilter([['entity_type', Operators::EQUALS, ProductModelInterface::class]]);
        $this->assertEquals(80, $result->count());
    }
}
