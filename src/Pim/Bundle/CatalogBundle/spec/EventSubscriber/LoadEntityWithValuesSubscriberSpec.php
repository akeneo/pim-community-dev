<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\EventSubscriber\LoadEntityWithValuesSubscriber;
use Pim\Component\Catalog\Factory\ValueCollectionFactory;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadEntityWithValuesSubscriberSpec extends ObjectBehavior
{
    function let(
        ContainerInterface $container,
        ValueCollectionFactory $valueCollectionFactory
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

    function it_loads_values_of_a_variant_product(
        $valueCollectionFactory,
        LifecycleEventArgs $event,
        ProductModelInterface $rootProductModel,
        ProductModelInterface $subProductModel,
        VariantProductInterface $product,
        ValueCollectionInterface $values
    ) {
        $event->getObject()->willReturn($product);
        $product->getIdentifier()->willReturn('foo');
        $product->getRawValues()->willReturn(['an attribute' => 'a value', 'another attribute' => 'another value']);

        $rootProductModel->getParent()->willReturn(null);
        $rootProductModel->getRawValues()->willReturn(['description' => 'a desc']);
        $subProductModel->getParent()->willReturn($rootProductModel);
        $subProductModel->getRawValues()->willReturn(['color' => 'red', 'image' => 'red.png']);
        $product->getParent()->willReturn($subProductModel);

        $valueCollectionFactory
            ->createFromStorageFormat(
                [
                    'description'       => 'a desc',
                    'color'             => 'red',
                    'image'             => 'red.png',
                    'an attribute'      => 'a value',
                    'another attribute' => 'another value'
                ]
            )
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
