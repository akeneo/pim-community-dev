<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Subscriber;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Form\FormEvent;

class SetAttributeGroupSortOrderSubscriberSpec extends ObjectBehavior
{
    function let(AttributeGroupRepositoryInterface $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\EnrichBundle\Form\Subscriber\SetAttributeGroupSortOrderSubscriber');
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_the_pre_set_data_form_event()
    {
        $this->getSubscribedEvents()->shouldReturn(
            [
                'form.pre_set_data' => 'preSetData'
            ]
        );
    }

    function it_sets_the_sort_order_when_creating_an_attribute_group($repository, FormEvent $event, AttributeGroupInterface $group)
    {
        $event->getData()->willReturn($group);
        $group->getId()->willReturn(null);

        $repository->getMaxSortOrder()->willReturn(3);
        $group->setSortOrder(4)->shouldBeCalled();

        $this->preSetData($event);
    }

    function it_does_nothing_if_a_new_group_is_not_being_created($repository, FormEvent $event, AttributeGroupInterface $group)
    {
        $event->getData()->willReturn($group);
        $group->getId()->willReturn(5);

        $repository->getMaxSortOrder()->shouldNotBeCalled();
        $group->setSortOrder(Argument::any())->shouldNotBeCalled();

        $this->preSetData($event);
    }
}
