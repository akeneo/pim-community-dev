<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\AttributeGroup\Query;

use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Test\Integration\TestCase;

class SqlFindAttributeGroupOrdersEqualOrSuperiorToIntegration extends TestCase
{
    public function testQueryToGetAssociatedProductCodes()
    {
        $query = $this->get('akeneo.pim.structure.query.find_attribute_group_orders_equal_or_superior_to');

        $attributeGroup = new AttributeGroup();
        $attributeGroup->setCode('whatever');

        $attributeGroup->setSortOrder('3');
        $this->assertSame(['3', '100'], $query->execute($attributeGroup));

        $attributeGroup->setSortOrder('4');
        $this->assertSame(['100'], $query->execute($attributeGroup));

        $attributeGroup->setSortOrder('1');
        $this->assertSame(['1', '2', '3', '100'], $query->execute($attributeGroup));

        $attributeGroup->setSortOrder('500');
        $this->assertSame([], $query->execute($attributeGroup));
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
