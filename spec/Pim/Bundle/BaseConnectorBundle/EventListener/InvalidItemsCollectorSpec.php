<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\EventListener;

use Akeneo\Bundle\BatchBundle\Event\InvalidItemEvent;
use PhpSpec\ObjectBehavior;

class InvalidItemsCollectorSpec extends ObjectBehavior
{
    function it_collects_invalid_items_from_event(InvalidItemEvent $event)
    {
        $item = [
            'sku' => 'sku-001',
            'name_en-us' => 'Black shoes',
            'name_fr-fr' => 'Chaussures noires'
        ];
        $hashKey = md5(serialize($item));

        $event->getItem()->willReturn($item);

        $this->collect($event);
        $this->getInvalidItems()->shouldReturn([$hashKey => $item]);
    }

    function it_collects_several_invalid_items_from_events(
        InvalidItemEvent $event1,
        InvalidItemEvent $event2,
        InvalidItemEvent $event3
    ) {
        $item1 = [
            'sku' => 'sku-001',
            'name_en-us' => 'Black shoes',
            'name_fr-fr' => 'Chaussures noires'
        ];
        $item2 = [
            'sku' => 'sku-002',
            'name_en-us' => 'Pink shoes',
            'name_fr-fr' => 'Chaussures roses'
        ];
        $item3 = [
            'sku' => 'sku-004',
            'name_en-us' => 'Yellow shoes',
            'name_fr-fr' => 'Chaussures jaunes'
        ];

        $hashKeyItem1 = md5(serialize($item1));
        $hashKeyItem2 = md5(serialize($item2));
        $hashKeyItem3 = md5(serialize($item3));

        $event1->getItem()->willReturn($item1);
        $event2->getItem()->willReturn($item2);
        $event3->getItem()->willReturn($item3);

        $this->collect($event1);
        $this->collect($event2);
        $this->collect($event3);
        $this->getInvalidItems()->shouldReturn([
            $hashKeyItem1 => $item1,
            $hashKeyItem2 => $item2,
            $hashKeyItem3 => $item3,
        ]);
    }

    function it_does_not_collect_duplicate_invalid_items(InvalidItemEvent $event1, InvalidItemEvent $event2)
    {
        $item = [
            'sku' => 'sku-001',
            'name_en-us' => 'Black shoes',
            'name_fr-fr' => 'Chaussures noires'
        ];

        $hashKeyItem = md5(serialize($item));

        $event1->getItem()->willReturn($item);
        $event2->getItem()->willReturn($item);

        $this->collect($event1);
        $this->collect($event2);
        $this->getInvalidItems()->shouldReturn([$hashKeyItem => $item]);
    }
}
