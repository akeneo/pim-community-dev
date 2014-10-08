<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeFactory;
use Pim\Bundle\CatalogBundle\Event\AttributeEvents;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AttributeManagerSpec extends ObjectBehavior
{
    const ATTRIBUTE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\Attribute';
    const PRODUCT_CLASS   = 'Pim\Bundle\CatalogBundle\Model\Product';

    function let(
        ObjectManager $objectManager,
        AttributeTypeFactory $factory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith(
            self::ATTRIBUTE_CLASS,
            self::PRODUCT_CLASS,
            $objectManager,
            $factory,
            $eventDispatcher
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

    function it_provides_the_list_of_attribute_types($factory)
    {
        $factory->getAttributeTypes(self::PRODUCT_CLASS)->willReturn(['foo', 'bar']);

        $this->getAttributeTypes()->shouldReturn(['bar' => 'bar', 'foo' => 'foo']);
    }

    function it_dispatches_an_event_when_removing_an_attribute(
        $eventDispatcher,
        $objectManager,
        AbstractAttribute $attribute
    ) {
        $eventDispatcher->dispatch(
            AttributeEvents::PRE_REMOVE,
            Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
        )->shouldBeCalled();

        $objectManager->remove($attribute)->shouldBeCalled();
        $objectManager->flush()->shouldBeCalled();

        $this->remove($attribute);
    }
}
