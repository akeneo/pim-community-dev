<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\EventSubscriber\LoadEntityWithValuesSubscriber;
use Pim\Component\Catalog\Factory\ValueCollectionFactoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadEntityWithValuesSubscriberSpec extends ObjectBehavior
{
    function let(
        ContainerInterface $container,
        ValueCollectionFactoryInterface $valueCollectionFactory
    ) {
        $this->beConstructedWith($container);

        $container->get('pim_catalog.factory.value_collection')->willReturn($valueCollectionFactory);
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
        ValueCollectionInterface $values
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

    function it_works_only_with_products($container, LifecycleEventArgs $event, \stdClass $object)
    {
        $event->getObject()->willReturn($object);
        $container->get(Argument::any())->shouldNotBeCalled();

        $this->postLoad($event);
    }
}
