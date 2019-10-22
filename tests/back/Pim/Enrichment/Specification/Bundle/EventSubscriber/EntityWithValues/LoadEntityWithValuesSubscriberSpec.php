<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithValues;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\EventSubscriber\EntityWithValues\LoadEntityWithValuesSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;

class LoadEntityWithValuesSubscriberSpec extends ObjectBehavior
{
    function let(
        WriteValueCollectionFactory $valueCollectionFactory
    ) {
        $this->beConstructedWith($valueCollectionFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LoadEntityWithValuesSubscriber::class);
    }

    function it_subscribes_to_the_postLoad_event()
    {
        $this->getSubscribedEvents()->shouldReturn(['postLoad']);
    }

    function it_loads_values_of_a_product(
        $valueCollectionFactory,
        LifecycleEventArgs $event,
        ProductInterface $product,
        WriteValueCollection $values
    ) {
        $event->getObject()->willReturn($product);
        $product->getIdentifier()->willReturn('foo');

        $product->getRawValues()->willReturn(['an attribute' => 'a value', 'another attribute' => 'another value']);

        $valueCollectionFactory
            ->createFromStorageFormat(['an attribute' => 'a value', 'another attribute' => 'another value'])
            ->willReturn($values);

        $product->setValues($values)->shouldBeCalled();

        $this->postLoad($event);
    }

    function it_works_only_with_entity_with_values(LifecycleEventArgs $event, \stdClass $object)
    {
        $event->getObject()->willReturn($object);

        $this->postLoad($event);
    }
}
