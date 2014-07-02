<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeFactory;
use Pim\Bundle\CatalogBundle\CatalogEvents;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

class AttributeManagerSpec extends ObjectBehavior
{
    const ATTRIBUTE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\Attribute';
    const PRODUCT_CLASS   = 'Pim\Bundle\CatalogBundle\Model\Product';
    const OPTION_CLASS    = 'Pim\Bundle\CatalogBundle\Entity\AttributeOption';
    const OPT_VALUE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue';

    function let(
        ObjectManager $objectManager,
        AttributeTypeFactory $factory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith(
            self::ATTRIBUTE_CLASS,
            self::OPTION_CLASS,
            self::OPT_VALUE_CLASS,
            self::PRODUCT_CLASS,
            $objectManager,
            $factory,
            $eventDispatcher
        );
    }

    function it_should_create_an_attribute() {
        $this->createAttribute()->shouldReturnAnInstanceOf(self::ATTRIBUTE_CLASS);
    }

    function it_should_create_an_attribute_option() {
        $this->createAttributeOption()->shouldReturnAnInstanceOf(self::OPTION_CLASS);
    }

    function it_should_create_an_attribute_option_value()
    {
        $this->createAttributeOptionValue()->shouldReturnAnInstanceOf(self::OPT_VALUE_CLASS);
    }

    function it_should_return_the_attribute_class_used()
    {
        $this->getAttributeClass()->shouldReturn(self::ATTRIBUTE_CLASS);
    }

    function it_should_return_the_attribute_option_class_used()
    {
        $this->getAttributeOptionClass()->shouldReturn(self::OPTION_CLASS);
    }

    function it_should_return_list_of_attribute_types($factory)
    {
        $factory->getAttributeTypes(self::PRODUCT_CLASS)->willReturn(['foo', 'bar']);

        $this->getAttributeTypes()->shouldReturn(['bar' => 'bar', 'foo' => 'foo']);
    }

    function it_should_dispatch_an_event_when_remove_an_attribute(
        $eventDispatcher,
        $objectManager,
        AbstractAttribute $attribute
    ) {
        $eventDispatcher->dispatch(
            CatalogEvents::PRE_REMOVE_ATTRIBUTE,
            Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
        )->shouldBeCalled();

        $objectManager->remove($attribute)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->remove($attribute);
    }
}
