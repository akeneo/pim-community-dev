<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Attribute\Query;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class AttributeIsAFamilyVariantAxisIntegration extends TestCase
{
    /**
     * @test
     */
    function it_returns_true_if_an_attribute_is_used_as_variant_axis()
    {
        $result = $this->getFromTestContainer('pim_catalog.doctrine.query.attribute_is_an_family_variant_axis')
            ->execute('color');

        $this->assertTrue($result);
    }

    /**
     * @test
     */
    function it_returns_false_if_an_attribute_is_not_used_as_variant_axis()
    {
        $result = $this->getFromTestContainer('pim_catalog.doctrine.query.attribute_is_an_family_variant_axis')
            ->execute('sku');

        $this->assertFalse($result);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
