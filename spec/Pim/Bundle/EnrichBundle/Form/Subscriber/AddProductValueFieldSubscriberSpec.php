<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Subscriber;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\EnrichBundle\Form\Factory\ProductValueFormFactory;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

class AddProductValueFieldSubscriberSpec extends ObjectBehavior
{
    function let(ProductValueFormFactory $factory)
    {
        $this->beConstructedWith($factory);
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

    function it_adds_product_value_form(
        FormEvent $event,
        FormInterface $form,
        FormInterface $field,
        FormInterface $rootForm,
        $factory,
        ProductValueInterface $value
    ) {
        $event->getForm()->willReturn($form);
        $event->getData()->willReturn($value);

        $form->getRoot()->willReturn($rootForm);
        $rootForm->getName()->willReturn('pim_catalog_edit');
        $factory->createProductValueForm($value, ['root_form_name' => 'pim_catalog_edit'])->willReturn($field);
        $form->add($field)->shouldBeCalled();

        $this->preSetData($event);
    }

    function it_doesnt_add_product_value_form_when_value_is_null(
        FormEvent $event,
        FormInterface $form,
        $factory
    ) {
        $event->getForm()->willReturn($form);
        $event->getData()->willReturn(null);

        $factory->createProductValueForm()->shouldNotBeCalled();
    }
}
