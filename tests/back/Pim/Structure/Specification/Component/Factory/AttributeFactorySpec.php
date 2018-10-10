<?php

namespace Specification\Akeneo\Pim\Structure\Component\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\AttributeType\AbstractAttributeType;
use Akeneo\Pim\Structure\Component\AttributeTypeRegistry;

class AttributeFactorySpec extends ObjectBehavior
{
    const ATTRIBUTE_CLASS = Attribute::class;
    const PRODUCT_CLASS = Product::class;

    function let(AttributeTypeRegistry $registry)
    {
        $this->beConstructedWith(
            $registry,
            self::ATTRIBUTE_CLASS,
            self::PRODUCT_CLASS
        );
    }

    function it_creates_an_attribute()
    {
        $this->createAttribute()->shouldReturnAnInstanceOf(self::ATTRIBUTE_CLASS);
    }

    function it_creates_an_attribute_with_type($registry, AbstractAttributeType $attributeType)
    {
        $attributeType->getBackendType()->willReturn('backend_type');
        $attributeType->getName()->willReturn('name_type');
        $attributeType->isUnique()->willReturn(false);

        $registry->get('pim_catalogue_text')->willReturn($attributeType);

        $this->createAttribute('pim_catalogue_text')->shouldReturnAnInstanceOf(self::ATTRIBUTE_CLASS);
    }
}
