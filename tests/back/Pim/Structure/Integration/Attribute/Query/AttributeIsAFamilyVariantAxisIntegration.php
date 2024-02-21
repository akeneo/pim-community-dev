<?php

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Query;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class AttributeIsAFamilyVariantAxisIntegration extends TestCase
{
    function testItReturnsTrueIfAnAttributeIsUsedAsVariantAxis()
    {
        $result = $this->get('akeneo.pim.structure.query.attribute_is_an_family_variant_axis')
            ->execute('color');

        $this->assertTrue($result);
    }

    function testItReturnsFalseIfAnAttributeIsNotUsedAsVariantAxis()
    {
        $result = $this->get('akeneo.pim.structure.query.attribute_is_an_family_variant_axis')
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
