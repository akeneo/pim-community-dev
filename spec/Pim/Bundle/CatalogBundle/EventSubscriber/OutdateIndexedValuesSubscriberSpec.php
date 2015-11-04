<?php

namespace spec\Pim\Bundle\CatalogBundle\EventSubscriber;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

class OutdateIndexedValuesSubscriberSpec extends ObjectBehavior
{
    function it_is_a_doctrine_subscriber()
    {
        $this->shouldHaveType('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_the_postLoad_event()
    {
        $this->getSubscribedEvents()->shouldReturn(['postLoad']);
    }

    function it_marks_indexed_product_values_outdated_after_loading_a_product(
        LifecycleEventArgs $args,
        ProductInterface $entity
    ) {
        $args->getObject()->willReturn($entity);

        $entity->markIndexedValuesOutdated()->shouldBeCalled();

        $this->postLoad($args);
    }

    function it_marks_indexed_product_values_outdated_after_loading_a_value(
        LifecycleEventArgs $args,
        ProductValueInterface $value,
        ProductInterface $entity
    ) {
        $args->getObject()->willReturn($value);
        $value->getEntity()->willReturn($entity);

        $entity->markIndexedValuesOutdated()->shouldBeCalled();

        $this->postLoad($args);
    }
}
