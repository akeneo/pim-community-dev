<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Subscriber;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

class FilterLocaleSpecificValueSubscriberSpec extends ObjectBehavior
{
    function let()
    {
        $currentLocale = 'en_US';
        $this->beConstructedWith($currentLocale);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_pre_set_data_form_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            'form.pre_set_data' => 'preSetData',
        ]);
    }

    function it_removes_value_when_the_attribute_is_locale_specific_and_current_locale_is_not_in_available_list(
        FormEvent $event,
        FormInterface $form,
        FormInterface $field,
        FormInterface $rootForm,
        ValueInterface $taxValue,
        AttributeInterface $taxAttribute
    ) {
        $event->getForm()->willReturn($form);
        $event->getData()->willReturn(['tax' => $taxValue]);
        $taxValue->getAttribute()->willReturn($taxAttribute);
        $fr = new Locale();
        $fr->setCode('fr_FR');
        $taxAttribute->isLocaleSpecific()->willReturn(true);
        $taxAttribute->getLocaleSpecificCodes()->willReturn(['fr_FR']);
        $form->remove('tax')->shouldBeCalled();

        $this->preSetData($event);
    }

    function it_doesnt_remove_value_when_the_attribute_is_locale_specific_and_current_locale_is_in_available_list(
        FormEvent $event,
        FormInterface $form,
        FormInterface $field,
        FormInterface $rootForm,
        ValueInterface $taxValue,
        AttributeInterface $taxAttribute
    ) {
        $event->getForm()->willReturn($form);
        $event->getData()->willReturn(['tax' => $taxValue]);
        $taxValue->getAttribute()->willReturn($taxAttribute);
        $fr = new Locale();
        $fr->setCode('fr_FR');
        $en = new Locale();
        $en->setCode('en_US');
        $taxAttribute->isLocaleSpecific()->willReturn(true);
        $taxAttribute->getLocaleSpecificCodes()->willReturn(['fr_FR', 'en_US']);

        $form->remove('tax')->shouldNotBeCalled();

        $this->preSetData($event);
    }

    function it_doesnt_remove_value_when_the_attribute_is_not_locale_specific(
        FormEvent $event,
        FormInterface $form,
        FormInterface $field,
        FormInterface $rootForm,
        ValueInterface $nameValue,
        AttributeInterface $nameAttribute
    ) {
        $event->getForm()->willReturn($form);
        $event->getData()->willReturn(['name' => $nameValue]);
        $nameValue->getAttribute()->willReturn($nameAttribute);
        $nameAttribute->isLocaleSpecific()->willReturn(false);
        $form->remove('name')->shouldNotBeCalled();

        $this->preSetData($event);
    }
}
