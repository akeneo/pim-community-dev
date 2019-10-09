<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\AttributeOption;

use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\AttributeOption\SetAttributeOptionSortOrderSubscriber;
use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\Attribute\GetAttributeOptionsMaxSortOrder;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class SetAttributeOptionSortOrderSubscriberSpec extends ObjectBehavior
{
    function let(GetAttributeOptionsMaxSortOrder $getAttributeOptionsMaxSortOrder)
    {
        $this->beConstructedWith($getAttributeOptionsMaxSortOrder);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
        $this->shouldHaveType(SetAttributeOptionSortOrderSubscriber::class);
    }

    function it_subscribes_to_pre_save_and_pre_save_all_events()
    {
        $subscribedEvents = $this::getSubscribedEvents();
        $subscribedEvents->shouldHaveKey(StorageEvents::PRE_SAVE);
        $subscribedEvents->shouldHaveKey(StorageEvents::PRE_SAVE_ALL);
    }

    function it_only_handles_attributes_and_options(GetAttributeOptionsMaxSortOrder $getAttributeOptionsMaxSortOrder)
    {
        $getAttributeOptionsMaxSortOrder->forAttributeCodes(Argument::any())->shouldNotBeCalled();
        $this->onPreSave(new GenericEvent(new \stdClass(), ['unitary' => true]));
        $this->onPreSaveAll(new GenericEvent([new \stdClass()]));
    }

    function it_does_nothing_on_pre_save_for_non_unitary_events(
        GetAttributeOptionsMaxSortOrder $getAttributeOptionsMaxSortOrder
    ) {
        $getAttributeOptionsMaxSortOrder->forAttributeCodes(Argument::any())->shouldNotBeCalled();
        $this->onPreSave(new GenericEvent(new AttributeOption(), ['unitary' => false]));
    }

    function it_does_nothing_if_option_has_a_non_null_sort_order(
        GetAttributeOptionsMaxSortOrder $getAttributeOptionsMaxSortOrder,
        AttributeOptionInterface $option
    ) {
        $option->getSortOrder()->willReturn(42);

        $option->setSortOrder(Argument::any())->shouldNotBeCalled();
        $getAttributeOptionsMaxSortOrder->forAttributeCodes(Argument::any())->shouldNotBeCalled();

        $this->onPreSaveAll(new GenericEvent([$option->getWrappedObject()]));
    }

    function it_sets_sort_orders_for_options_with_a_null_sort_order(
        GetAttributeOptionsMaxSortOrder $getAttributeOptionsMaxSortOrder,
        AttributeOptionInterface $option1,
        AttributeOptionInterface $option2,
        AttributeOptionInterface $option3
    ) {
        $color = new Attribute();
        $color->setCode('color');
        $size = new Attribute();
        $size->setCode('size');

        $option1->getSortOrder()->willReturn(null);
        $option1->getAttribute()->willReturn($color);
        $option2->getSortOrder()->willReturn(null);
        $option2->getAttribute()->willReturn($color);
        $option3->getSortOrder()->willReturn(null);
        $option3->getAttribute()->willReturn($size);

        $getAttributeOptionsMaxSortOrder->forAttributeCodes(['color', 'size'])->willReturn(
            [
                'color' => 10,
                'size' => 22,
            ]
        );
        $option1->setSortOrder(11)->shouldBeCalled();
        $option2->setSortOrder(12)->shouldBeCalled();
        $option3->setSortOrder(23)->shouldBeCalled();

        $this->onPreSaveAll(
            new GenericEvent(
                [
                    $option1->getWrappedObject(),
                    $option2->getWrappedObject(),
                    $option3->getWrappedObject(),
                ]
            )
        );
    }

    function it_sets_sort_order_to_zero_if_the_attribute_has_no_option_yet(
        GetAttributeOptionsMaxSortOrder $getAttributeOptionsMaxSortOrder,
        AttributeOptionInterface $option1,
        AttributeOptionInterface $option2
    ) {
        $color = new Attribute();
        $color->setCode('color');

        $option1->getSortOrder()->willReturn(null);
        $option1->getAttribute()->willReturn($color);
        $option2->getSortOrder()->willReturn(null);
        $option2->getAttribute()->willReturn($color);

        $getAttributeOptionsMaxSortOrder->forAttributeCodes(['color'])->willReturn([]);
        $option1->setSortOrder(0)->shouldBeCalled();
        $option2->setSortOrder(1)->shouldBeCalled();

        $this->onPreSaveAll(
            new GenericEvent([$option1->getWrappedObject(), $option2->getWrappedObject()])
        );
    }

    function it_sets_sort_orders_of_options_when_saving_an_attribute(
        GetAttributeOptionsMaxSortOrder $getAttributeOptionsMaxSortOrder,
        AttributeOptionInterface $blue,
        AttributeOptionInterface $red
    ) {
        $color = new Attribute();
        $color->setCode('color');
        $color->addOption($blue->getWrappedObject());
        $color->addOption($red->getWrappedObject());

        $blue->getAttribute()->willReturn($color);
        $blue->getSortOrder()->willReturn(null);
        $red->getAttribute()->willReturn($color);
        $red->getSortOrder()->willReturn(null);

        $getAttributeOptionsMaxSortOrder->forAttributeCodes(['color'])->willReturn(['color' => 12]);

        $blue->setSortOrder(13)->shouldBeCalled();
        $red->setSortOrder(14)->shouldBeCalled();

        $this->onPreSave(new GenericEvent($color, ['unitary' => true]));
    }

    function it_sets_sort_orders_of_options_when_saving_multiple_attributes(
        GetAttributeOptionsMaxSortOrder $getAttributeOptionsMaxSortOrder,
        AttributeOptionInterface $blue,
        AttributeOptionInterface $red,
        AttributeOptionInterface $xl,
        AttributeOptionInterface $xxl
    ) {
        $color = new Attribute();
        $color->setCode('color');
        $color->addOption($blue->getWrappedObject());
        $color->addOption($red->getWrappedObject());

        $blue->getAttribute()->willReturn($color);
        $blue->getSortOrder()->willReturn(null);
        $red->getAttribute()->willReturn($color);
        $red->getSortOrder()->willReturn(null);

        $size = new Attribute();
        $size->setCode('size');
        $size->addOption($xl->getWrappedObject());
        $size->addOption($xxl->getWrappedObject());

        $xl->getAttribute()->willReturn($size);
        $xl->getSortOrder()->willReturn(null);
        $xxl->getAttribute()->willReturn($size);
        $xxl->getSortOrder()->willReturn(10);

        $name = new Attribute();
        $name->setCode('name');

        $getAttributeOptionsMaxSortOrder->forAttributeCodes(['color', 'size'])->willReturn(
            ['color' => 12, 'size' => 41]
        );

        $blue->setSortOrder(13)->shouldBeCalled();
        $red->setSortOrder(14)->shouldBeCalled();
        $xl->setSortOrder(42)->shouldBeCalled();
        $xxl->setSortOrder(Argument::any())->shouldNotBeCalled();

        $this->onPreSaveAll(new GenericEvent([$color, $size, $name]));
    }
}
