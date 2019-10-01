<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use AkeneoTest\Pim\Enrichment\Integration\Fixture\EntityBuilder;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductAndProductModelQueryBuilderTestCase;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ParentFilterIntegration extends AbstractProductAndProductModelQueryBuilderTestCase
{
    public function testQueryParentInList()
    {
        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');

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

    public function testQueryParentInListCaseInsensitive()
    {
        $entityBuilder = $this->get('akeneo_integration_tests.catalog.fixture.build_entity');
        $productModel = $entityBuilder->createProductModel(
            'MODEL_test',
            'clothing_color_size',
            null,
            []
        );
        $entityBuilder->createVariantProduct(
            'variant_product',
            'clothing',
            'clothing_material_size',
            $productModel,
            []
        );
        $result = $this->executeFilter([['parent', Operators::IN_LIST, ['mOdEl_TeSt']]]);
        $this->assert($result, [
            'variant_product',
        ]);
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
