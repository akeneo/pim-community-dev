<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Subscriber;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;
use Prophecy\Argument;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class AddAttributeAsLabelSubscriberSpec extends ObjectBehavior
{
    function let(FormFactoryInterface $factory, SecurityFacade $securityFacade)
    {
        $this->beConstructedWith('Acme\\Foo\\Bar', $factory, $securityFacade);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_pre_set_data_form_event()
    {
        $this->getSubscribedEvents()->shouldReturn([
            'form.pre_set_data' => 'addAttributeAsLabelField',
        ]);
    }

    function it_adds_attribute_as_label_field_when_data_has_been_persisted(
        FormEvent $event,
        FamilyInterface $family,
        FormInterface $form,
        FormInterface $field,
        $factory,
        $securityFacade
    ) {
        $event->getForm()->willReturn($form);
        $event->getData()->willReturn($family);
        $family->getId()->willReturn(1337);
        $family->getAttributeAsLabel()->willReturn('name');
        $family->getAttributeAsLabelChoices()->willReturn([
            'name'        => 'Name',
            'description' => 'Description',
        ]);

        $securityFacade->isGranted(Argument::any())->willReturn(true);

        $factory->createNamed('attributeAsLabel', 'entity', 'name', [
            'required'        => true,
            'label'           => 'Attribute used as label',
            'class'           => 'Acme\\Foo\\Bar',
            'choices'         => ['name' => 'Name', 'description' => 'Description'],
            'auto_initialize' => false,
            'select2'         => true,
            'disabled'        => false
        ])->willReturn($field);

        $form->add($field)->shouldBeCalled();

        $this->addAttributeAsLabelField($event);
    }

    function it_ignores_family_that_was_not_persisted(FormEvent $event, FamilyInterface $family)
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
