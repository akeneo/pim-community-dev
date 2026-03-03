<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Validation;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

class NonAvailableAttributeCodeIntegration extends AbstractAttributeTestCase
{
    public function test_create_an_attribute_with_an_available_code_is_possible()
    {
        $attribute = $this->createAttributeByCode('new_attribute');
        $violations = $this->validateAttribute($attribute);

        $this->assertCount(0, $violations);
    }

    public function test_create_an_attribute_with_a_non_available_code_is_not_possible()
    {
        $attribute = $this->createAttributeByCode('categories');
        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This code is not available', $violations->get(0)->getMessage());
    }

    public function test_create_an_attribute_with_a_non_available_code_in_different_case_is_not_possible()
    {
        $attribute = $this->createAttributeByCode('CATEgories');
        $violations = $this->validateAttribute($attribute);

        $this->assertCount(1, $violations);
        $this->assertSame('This code is not available', $violations->get(0)->getMessage());
    }

    private function createAttributeByCode(string $attributeCode): AttributeInterface
    {
        $attribute = $this->createAttribute();

        $this->updateAttribute($attribute, [
            'code' => $attributeCode,
            'type' => 'pim_catalog_text',
            'group' => 'attributeGroupA'
        ]);
        $this->saveAttribute($attribute);

        return $attribute;
    }
}
