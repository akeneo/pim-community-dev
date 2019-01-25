<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\EventSubscriber\AttributeCreationSubscriber;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class AttributeCreationSubscriberSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeCreationSubscriber::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_listens_to_storage_pre_save_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            StorageEvents::PRE_SAVE => 'incrementSortOrder',
        ]);
    }

    function it_does_nothing_if_subject_is_not_an_attribute(GenericEvent $event, AttributeOptionInterface $option)
    {
        $event->getSubject()->willReturn($option);
        $option->getId()->shouldNotBeCalled();

        $this->incrementSortOrder($event);
    }

    function it_does_nothing_if_subject_is_not_a_new_attribute(GenericEvent $event, AttributeInterface $attribute)
    {
        $event->getSubject()->willReturn($attribute);
        $attribute->getId()->willReturn(42);
        $attribute->getGroup()->shouldNotBeCalled();

        $this->incrementSortOrder($event);
    }

    function it_increments_sort_order_of_new_attribute(
        GenericEvent $event,
        AttributeInterface $attribute,
        AttributeGroupInterface $attributeGroup
    ) {
        $event->getSubject()->willReturn($attribute);
        $attribute->getId()->willReturn(null);
        $attribute->getGroup()->willReturn($attributeGroup);
        $attributeGroup->getMaxAttributeSortOrder()->willReturn(0);

        $attribute->setSortOrder(1)->shouldBeCalled();

        $this->incrementSortOrder($event);
    }
}
