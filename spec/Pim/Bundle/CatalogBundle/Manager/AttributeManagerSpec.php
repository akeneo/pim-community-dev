<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry;
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
        AttributeTypeRegistry $registry,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith(
            self::ATTRIBUTE_CLASS,
            self::PRODUCT_CLASS,
            $objectManager,
            $registry,
            $eventDispatcher
        );
    }

    function it_is_a_saver()
    {
        $this->shouldImplement('Pim\Component\Resource\Model\SaverInterface');
    }

    function it_is_a_bulk_saver()
    {
        $this->shouldImplement('Pim\Component\Resource\Model\BulkSaverInterface');
    }

    function it_is_a_remover()
    {
        $this->shouldImplement('Pim\Component\Resource\Model\RemoverInterface');
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

    function it_dispatches_events_when_removing_an_attribute(
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

        $eventDispatcher->dispatch(
            AttributeEvents::POST_REMOVE,
            Argument::type('Symfony\Component\EventDispatcher\GenericEvent')
        )->shouldBeCalled();

        $this->remove($attribute);
    }

    function it_throws_exception_when_save_anything_else_than_a_attribute()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a Pim\Bundle\CatalogBundle\Model\AttributeInterface, "%s" provided',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringSave($anythingElse);
    }

    function it_throws_exception_when_remove_anything_else_than_a_attribute()
    {
        $anythingElse = new \stdClass();
        $this
            ->shouldThrow(
                new \InvalidArgumentException(
                    sprintf(
                        'Expects a Pim\Bundle\CatalogBundle\Model\AttributeInterface, "%s" provided',
                        get_class($anythingElse)
                    )
                )
            )
            ->duringRemove($anythingElse);
    }
}
