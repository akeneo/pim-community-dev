<?php

namespace spec\Pim\Bundle\CatalogBundle\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry;

class AttributeFactorySpec extends ObjectBehavior
{
    const ATTRIBUTE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\Attribute';
    const PRODUCT_CLASS   = 'Pim\Bundle\CatalogBundle\Model\Product';

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

        $registry->get('pim_catalogue_text')->willReturn($attributeType);

        $this->createAttribute('pim_catalogue_text')->shouldReturnAnInstanceOf(self::ATTRIBUTE_CLASS);
    }
}
