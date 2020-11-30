<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Validation;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class BlackListedAttributeCodeIntegration extends AbstractAttributeTestCase
{
    public function test_i_can_update_an_attribute_that_is_not_blacklisted()
    {
        $attribute = $this->createAttributeByCode('new_attribute');
        $violations = $this->validateAttribute($attribute);

        $this->assertCount(0, $violations);
    }

    public function test_i_cannot_create_a_blacklisted_attribute()
    {
        $attribute = $this->createAttributeByCode('new_attribute');
        $this->deleteAttribute($attribute);

        $secondAttribute = $this->createAttributeByCode('new_attribute');

        $violations = $this->validateAttribute($secondAttribute);

        $this->assertCount(1, $violations);
        $this->assertSame('The attribute code "new_blacklisted_attribute" was previously existing and is currently being cleaned up', $violations->get(0)->getMessage());
    }

    private function createAttributeByCode(string $attributeCode): AttributeInterface
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute(
            $attribute,
            [
                'code'             => $attributeCode,
                'type'             => 'pim_catalog_text',
                'group'            => 'attributeGroupA'
            ]
        );
        $this->saveAttribute($attribute);

        return $attribute;
    }
}
