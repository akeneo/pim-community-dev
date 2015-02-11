<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry;
use Pim\Bundle\CatalogBundle\Event\AttributeEvents;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AttributeManagerSpec extends ObjectBehavior
{
    const ATTRIBUTE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\Attribute';
    const PRODUCT_CLASS   = 'Pim\Bundle\CatalogBundle\Model\Product';

    function let(
        ObjectManager $objectManager,
        AttributeTypeRegistry $registry
    ) {
        $this->beConstructedWith(
            self::ATTRIBUTE_CLASS,
            self::PRODUCT_CLASS,
            $objectManager,
            $registry
        );
    }

    function it_instantiates_an_attribute()
    {
        $this->createAttribute()->shouldReturnAnInstanceOf(self::ATTRIBUTE_CLASS);
    }

    function it_provides_the_attribute_class_used()
    {
        $this->getAttributeClass()->shouldReturn(self::ATTRIBUTE_CLASS);
    }

    function it_provides_the_list_of_attribute_types($registry)
    {
        $registry->getAliases()->willReturn(['foo', 'bar']);

        $this->getAttributeTypes()->shouldReturn(['bar' => 'bar', 'foo' => 'foo']);
    }
}
