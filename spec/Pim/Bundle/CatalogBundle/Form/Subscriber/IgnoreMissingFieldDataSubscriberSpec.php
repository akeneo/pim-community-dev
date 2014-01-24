<?php

namespace spec\Pim\Bundle\CatalogBundle\Form\Subscriber;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

class IgnoreMissingFieldDataSubscriberSpec extends ObjectBehavior
{
    function it_is_an_event_subscriber()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_form_pre_submit_event()
    {
        $this->getSubscribedEvents()->shouldReturn(['form.pre_bind' => 'preSubmit']);
    }

    function it_removes_fields_for_which_no_data_has_been_sent(
        FormEvent $event,
        FormInterface $form,
        FormInterface $firstname,
        FormInterface $lastname,
        FormInterface $age
    ) {
        $event->getForm()->willReturn($form);
        $form->isValid()->willReturn(true);
        $form->all()->willReturn([
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'age'       => $age,
        ]);
        $event->getData()->willReturn([
            'firstname' => 'Romain',
            'lastname'  => 'Monceau',
        ]);

        $form->remove('age')->shouldBeCalled();

        $this->preSubmit($event);
    }

    function it_does_nothing_if_the_form_is_not_valid(
        FormEvent $event,
        FormInterface $form
    ) {
        $event->getForm()->willReturn($form);
        $form->isValid()->willReturn(false);
        $form->remove()->shouldNotBeCalled();

        $this->preSubmit($event);
    }
}
