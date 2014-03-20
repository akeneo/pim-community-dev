<?php

namespace spec\Pim\Bundle\CatalogBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue;
use Pim\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;

class OutdateIndexedValuesListenerSpec extends ObjectBehavior
{
    function it_is_a_doctrine_subscriber()
    {
        $this->shouldHaveType('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_the_postLoad_event()
    {
        $this->getSubscribedEvents()->shouldReturn(['postLoad']);
    }

    function it_marks_flexible_value_entity_outdated(
        LifecycleEventArgs $args,
        AbstractEntityFlexibleValue $value,
        AbstractEntityFlexible $entity
    ) {
        $args->getObject()->willReturn($value);
        $value->getEntity()->willReturn($entity);

        $entity->markIndexedValuesOutdated()->shouldBeCalled();

        $this->postLoad($args);
    }

    function it_marks_flexible_entity_outdated(
        LifecycleEventArgs $args,
        AbstractEntityFlexible $entity
    ) {
        $args->getObject()->willReturn($entity);

        $entity->markIndexedValuesOutdated()->shouldBeCalled();

        $this->postLoad($args);
    }
}
