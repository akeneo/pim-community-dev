<?php

namespace spec\Akeneo\Tool\Bundle\ConnectorBundle\EventListener;

use Akeneo\Tool\Component\Batch\Event\EventInterface;
use Akeneo\Tool\Component\Batch\Event\InvalidItemEvent;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use PhpSpec\ObjectBehavior;

class InvalidItemsCollectorSpec extends ObjectBehavior
{
    function it_is_an_event_subscriber()
    {
        $this->shouldHaveType('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_returns_subscribed_events()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                EventInterface::INVALID_ITEM => 'collect'
            ]
        );
    }

    function it_collects_invalid_items_from_event(InvalidItemEvent $event, FileInvalidItem $invalidItem)
    {
        $item = [
            'sku'        => 'sku-001',
            'name_en-us' => 'Black shoes',
            'name_fr-fr' => 'Chaussures noires',
        ];


        $event->getItem()->willReturn($invalidItem);
        $invalidItem->getInvalidData()->willReturn($item);

        $hashKey = md5(serialize($item));

        $this->collect($event);
        $this->getInvalidItems()->shouldReturn([$hashKey => $invalidItem]);
    }

    function it_collects_several_invalid_items_from_events(
        InvalidItemEvent $event1,
        InvalidItemEvent $event2,
        InvalidItemEvent $event3,
        FileInvalidItem $invalidItem1,
        FileInvalidItem $invalidItem2,
        FileInvalidItem $invalidItem3
    ) {
        $item1 = [
            'sku'        => 'sku-001',
            'name_en-us' => 'Black shoes',
            'name_fr-fr' => 'Chaussures noires',
        ];
        $item2 = [
            'sku'        => 'sku-002',
            'name_en-us' => 'Pink shoes',
            'name_fr-fr' => 'Chaussures roses',
        ];
        $item3 = [
            'sku'        => 'sku-004',
            'name_en-us' => 'Yellow shoes',
            'name_fr-fr' => 'Chaussures jaunes',
        ];

        $event1->getItem()->willReturn($invalidItem1);
        $event2->getItem()->willReturn($invalidItem2);
        $event3->getItem()->willReturn($invalidItem3);

        $invalidItem1->getInvalidData()->willReturn($item1);
        $invalidItem2->getInvalidData()->willReturn($item2);
        $invalidItem3->getInvalidData()->willReturn($item3);

        $hashKeyItem1 = md5(serialize($item1));
        $hashKeyItem2 = md5(serialize($item2));
        $hashKeyItem3 = md5(serialize($item3));

        $this->collect($event1);
        $this->collect($event2);
        $this->collect($event3);
        $this->getInvalidItems()->shouldReturn([
            $hashKeyItem1 => $invalidItem1,
            $hashKeyItem2 => $invalidItem2,
            $hashKeyItem3 => $invalidItem3,
        ]);
    }

    function it_does_not_collect_duplicate_invalid_items(
        InvalidItemEvent $event,
        FileInvalidItem $invalidItem
    ) {
        $item = [
            'sku'        => 'sku-001',
            'name_en-us' => 'Black shoes',
            'name_fr-fr' => 'Chaussures noires',
        ];

        $event->getItem()->willReturn($invalidItem);
        $invalidItem->getInvalidData()->willReturn($item);

        $hashKeyItem = md5(serialize($item));

        $this->collect($event);
        $this->getInvalidItems()->shouldReturn([$hashKeyItem => $invalidItem]);
    }
}
