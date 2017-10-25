<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter;

use Pim\Bundle\CatalogBundle\tests\helper\EntityBuilder;
use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductAndProductModelQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ParentFilterIntegration extends AbstractProductAndProductModelQueryBuilderTestCase
{
    public function testQueryParentInList()
    {
        $entityBuilder = new EntityBuilder(static::$kernel->getContainer());

        $bikerJacket = $this->get('pim_catalog.repository.product_model')->findOneByIdentifier('model-biker-jacket');
        $entityBuilder->createVariantProduct(
            'product-biker-jacket',
            'clothing',
            'clothing_material_size',
            $bikerJacket,
            []
        );
        $entityBuilder->createProductModel(
            'a_model_without_children',
            'clothing_color_size',
            null,
            []
        );

        $result = $this->executeFilter([['parent', Operators::IN_LIST, ['model-biker-jacket']]]);
        $this->assert($result, [
            'model-biker-jacket-leather',
            'model-biker-jacket-polyester',
            'product-biker-jacket',
        ]);

        $result = $this->executeFilter([['parent', Operators::IN_LIST, ['model-biker-jacket', 'model-running-shoes']]]);
        $this->assert($result, [
            'model-biker-jacket-leather',
            'model-biker-jacket-polyester',
            'model-running-shoes-xxs',
            'model-running-shoes-m',
            'model-running-shoes-xxxl',
            'product-biker-jacket',
        ]);

        $result = $this->executeFilter([['parent', Operators::IN_LIST, ['a_model_without_children']]]);
        $this->assert($result, []);
    }

    public function testQueryParentEmptyAndAttributesInList()
    {
        $result = $this->executeFilter([
            ['parent', Operators::IS_EMPTY, ''],
            ['color', Operators::IN_LIST, ['navy_blue']]
        ]);

        $this->assert($result, [
            'watch',
        ]);

        $result = $this->executeFilter([
            ['parent', Operators::IS_EMPTY, ''],
            ['family', Operators::IN_LIST, ['accessories']]
        ]);

        $this->assert($result, [
            '1111111171',
            '1111111172',
            '1111111240',
            '1111111292',
            '1111111304',
            'model-braided-hat',
            'watch',
        ]);
    }
}
