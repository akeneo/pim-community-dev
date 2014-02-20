<?php

namespace spec\Pim\Bundle\FlexibleEntityBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

class DefaultValueListenerSpec extends ObjectBehavior
{
    function it_is_a_doctrine_event_subscriber()
    {
        $this->shouldBeAnInstanceOf('Doctrine\Common\EventSubscriber');
    }

    function it_subscribes_to_prePersist_and_preUpdate_events()
    {
        $this->getSubscribedEvents()->shouldReturn(['prePersist', 'preUpdate']);
    }

    function it_ignores_other_entities_than_flexible_value(
        LifecycleEventArgs $args
    ) {
        $args->getEntity()->willReturn('foo');

        $this->prePersist($args);
        $this->preUpdate($args);
    }

    function it_sets_flexible_value_data_to_its_attribute_default_if_empty_before_persisting(
        LifecycleEventArgs $args,
        FlexibleValueInterface $value,
        AbstractAttribute $attribute
    ) {
        $args->getEntity()->willReturn($value);
        $value->hasData()->willReturn(false);
        $value->getAttribute()->willReturn($attribute);
        $attribute->getDefaultValue()->willReturn('default');

        $value->setData('default')->shouldBeCalled();

        $this->prePersist($args);
    }

    function it_sets_flexible_value_data_to_its_attribute_default_if_empty_before_updating(
        LifecycleEventArgs $args,
        FlexibleValueInterface $value,
        AbstractAttribute $attribute
    ) {
        $args->getEntity()->willReturn($value);
        $value->hasData()->willReturn(false);
        $value->getAttribute()->willReturn($attribute);
        $attribute->getDefaultValue()->willReturn('default');

        $value->setData('default')->shouldBeCalled();

        $this->preUpdate($args);
    }

    function it_does_not_set_flexible_value_data_to_its_attribute_default_if_not_empty_before_persisting(
        LifecycleEventArgs $args,
        FlexibleValueInterface $value
    ) {
        $args->getEntity()->willReturn($value);
        $value->hasData()->willReturn(true);

        $value->setData(Argument::any())->shouldNotBeCalled();

        $this->prePersist($args);
    }

    function it_does_not_set_flexible_value_data_to_its_attribute_default_if_not_empty_before_updating(
        LifecycleEventArgs $args,
        FlexibleValueInterface $value
    ) {
        $args->getEntity()->willReturn($value);
        $value->hasData()->willReturn(true);

        $value->setData(Argument::any())->shouldNotBeCalled();

        $this->preUpdate($args);
    }
}
