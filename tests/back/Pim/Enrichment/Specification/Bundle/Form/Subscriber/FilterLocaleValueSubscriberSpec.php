<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Form\Subscriber;

use Akeneo\Pim\Enrichment\Bundle\Form\Subscriber\FilterLocaleValueSubscriber;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class FilterLocaleValueSubscriberSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('en_US', 'fr_FR');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(FilterLocaleValueSubscriber::class);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_subscribes_to_form_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            FormEvents::PRE_SET_DATA => 'preSetData',
        ]);
    }

    function it_removes_locale_when_necessary(FormEvent $event, FormInterface $form, ValueInterface $value)
    {
        $value->isLocalizable()->willReturn(true);
        $value->getLocaleCode()->willReturn('de_DE');
        $event->getData()->willReturn(['a_value' => $value]);
        $event->getForm()->willReturn($form);

        $form->remove('a_value')->shouldBeCalled();

        $this->preSetData($event);
    }

    function it_adds_value_to_the_form(FormEvent $event, FormInterface $form, ValueInterface $value)
    {
        $value->isLocalizable()->willReturn(true);
        $value->getLocaleCode()->willReturn('fr_FR');
        $event->getData()->willReturn(['a_value' => $value]);
        $event->getForm()->willReturn($form);

        $form->add('a_value', 'pim_product_value', Argument::any())->shouldBeCalled();

        $this->preSetData($event);
    }
}
