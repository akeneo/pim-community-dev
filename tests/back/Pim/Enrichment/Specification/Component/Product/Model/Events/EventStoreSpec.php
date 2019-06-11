<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Model\Events;

use Akeneo\Pim\Enrichment\Component\Product\Model\Events\EventStore;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductCreated;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductEnabled;
use Akeneo\Pim\Enrichment\Component\Product\Model\Events\ProductEvent;
use PhpSpec\ObjectBehavior;

class EventStoreSpec extends ObjectBehavior
{
    function it_is_an_event_store()
    {
        $this->shouldHaveType(EventStore::class);
    }

    function it_can_only_store_product_events()
    {
        $this->shouldThrow(\TypeError::class)->during('add', [new \stdClass()]);
    }

    function it_stores_product_events()
    {
        $created = new ProductCreated();
        $enabled = new ProductEnabled();

        $this->add($created);
        $this->add($enabled);

        $this->popEvents('productIdentifier')->shouldReturn([$created, $enabled]);
    }

    function it_purges_events_when_popping_them()
    {
        $this->add(new ProductCreated());
        $this->popEvents('productIdentifier')->shouldHaveCount(1);
        $this->popEvents('productIdentifier')->shouldHaveCount(0);
    }

    function it_sets_identifier_to_events_when_popping_them(ProductEvent $created, ProductEvent $enabled)
    {
        $this->add($created);
        $this->add($enabled);

        $created->setProductIdentifier('productIdentifier')->shouldBeCalled();
        $enabled->setProductIdentifier('productIdentifier')->shouldBeCalled();

        $this->popEvents('productIdentifier');
    }
}
