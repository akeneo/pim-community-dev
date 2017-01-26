<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Pim\Bundle\CatalogBundle\EventSubscriber\LoadProductValuesSubscriber;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Factory\ProductValueCollectionFactory;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueCollectionInterface;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadProductValuesSubscriberSpec extends ObjectBehavior
{
    function let(ContainerInterface $container)
    {
        $this->beConstructedWith($container);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LoadProductValuesSubscriber::class);
    }

    function it_subscribes_to_the_postLoad_event()
    {
        $this->getSubscribedEvents()->shouldReturn(['postLoad']);
    }

    function it_loads_values_of_a_product(
        $container,
        LifecycleEventArgs $event,
        ProductInterface $product,
        ProductValueCollectionFactory $factory,
        ProductValueCollectionInterface $values
    ) {
        $event->getObject()->willReturn($product);

        $container->get('pim_catalog.factory.product_value_collection')->willReturn($factory);
        $product->getRawValues()->willReturn(['raw_values']);
        $factory->createFromStorageFormat(['raw_values'])->willReturn($values);
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
