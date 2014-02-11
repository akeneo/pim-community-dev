<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Subscriber;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormEvent;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Symfony\Component\Form\FormInterface;

class AddAttributeAsLabelSubscriberSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory)
    {
        $this->beConstructedWith('Acme\\Foo\\Bar', $factory);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_pre_set_data_form_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            'form.pre_set_data' => 'addAttributeAsLabelField',
        ]);
    }

    function it_adds_attribute_as_label_field_when_data_has_been_persisted(
        FormEvent $event,
        Family $family,
        FormInterface $form,
        FormInterface $field,
        $factory
    ) {
        $event->getForm()->willReturn($form);
        $event->getData()->willReturn($family);
        $family->getId()->willReturn(1337);
        $family->getAttributeAsLabel()->willReturn('name');
        $family->getAttributeAsLabelChoices()->willReturn([
            'name'        => 'Name',
            'description' => 'Description',
        ]);


        $factory->createNamed('attributeAsLabel', 'entity', 'name', [
            'required'        => true,
            'label'           => 'Attribute used as label',
            'class'           => 'Acme\\Foo\\Bar',
            'choices'         => ['name' => 'Name', 'description' => 'Description'],
            'auto_initialize' => false,
            'select2'         => true
        ])->willReturn($field);

        $form->add($field)->shouldBeCalled();

        $this->addAttributeAsLabelField($event);
    }

    function it_ignores_family_that_was_not_persisted(FormEvent $event, Family $family)
    {
        $event->getData()->willReturn($family);
        $family->getId()->willReturn(null);

        $event->getForm()->shouldNotBeCalled();

        $this->addAttributeAsLabelField($event);
    }

    function it_ignores_data_that_is_not_family(FormEvent $event)
    {
        $event->getData()->willReturn('foo');

        $event->getForm()->shouldNotBeCalled();

        $this->addAttributeAsLabelField($event);
    }
}
