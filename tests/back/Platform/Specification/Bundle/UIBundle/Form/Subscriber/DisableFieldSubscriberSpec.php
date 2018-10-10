<?php

namespace Specification\Akeneo\Platform\Bundle\UIBundle\Form\Subscriber;

use Akeneo\Platform\Bundle\UIBundle\Form\Subscriber\DisableFieldSubscriber;
use PhpSpec\ObjectBehavior;
use Akeneo\Channel\Component\Model\Channel;
use Prophecy\Argument;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormEvent;

class DisableFieldSubscriberSpec extends ObjectBehavior
{
    function let(FormEvent $event, Form $form, FormConfigInterface $config, Channel $channel)
    {
        $this->beConstructedWith('name');

        $event->getData()->willReturn($channel);
        $event->getForm()->willReturn($form);
        $form->get(Argument::any())->willReturn($form);
        $form->getConfig()->willReturn($config);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(DisableFieldSubscriber::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_post_set_data()
    {
        $this->getSubscribedEvents()->shouldReturn(['form.post_set_data' => 'postSetData']);
    }

    function it_disables_the_field_if_entity_is_already_saved($event, $channel, $form)
    {
        $channel->getId()->willReturn(1);
        $form->add('name', null, [
            'disabled'  => true,
            'attr' => ['read_only' => true]
        ])->shouldBeCalled();
        $this->postSetData($event);
    }

    function it_does_nothing_if_entity_is_not_saved_yet($channel, $event, $form)
    {
        $event->getData()->willReturn($channel);
        $form->add(Argument::cetera())->shouldNotBeCalled();
    }

    function it_does_nothing_if_entity_does_not_exist($channel, $event, $form)
    {
        $channel->getId()->willReturn(1);
        $event->getData()->willReturn(null);
        $form->add(Argument::cetera())->shouldNotBeCalled();
    }
}
