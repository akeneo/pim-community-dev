<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\EventListener;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Pim\Bundle\EnrichBundle\EnrichEvents;
use Pim\Bundle\EnrichBundle\Event\CreateProductValueFormEvent;
use Pim\Bundle\CatalogBundle\Model\AbstractProductValue;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use PimEnterprise\Bundle\SecurityBundle\Voter\AttributeGroupVoter;

class DisableProductValueFieldListenerSpec extends ObjectBehavior
{
    function let(SecurityContextInterface $context)
    {
        $this->beConstructedWith($context);
    }

    function it_is_an_event_subscriber()
    {
        $this->shouldImplement('Symfony\Component\EventDispatcher\EventSubscriberInterface');
    }

    function it_subscribes_to_create_product_value_form()
    {
        $this->getSubscribedEvents()->shouldReturn([
            EnrichEvents::CREATE_PRODUCT_VALUE_FORM => 'onCreateProductValueForm'
        ]);
    }

    function it_disables_the_product_value_form_when_no_edit_right(
        CreateProductValueFormEvent $event,
        AbstractProductValue $value,
        AbstractAttribute $sku,
        AttributeGroup $group,
        $context
    ) {
        $event->getProductValue()->willReturn($value);
        $event->getFormOptions()->willReturn([]);
        $event->getContext()->willReturn(['root_form_name' => 'pim_catalog_edit']);
        $value->getAttribute()->willReturn($sku);
        $sku->getGroup()->willReturn($group);

        $context->isGranted(AttributeGroupVoter::EDIT_ATTRIBUTES, $group)->willReturn(false);
        $event->updateFormOptions(['disabled' => true, 'read_only' => true])->shouldBeCalled();

        $this->onCreateProductValueForm($event);
    }

    function it_doesnt_disable_the_product_value_form_when_no_edit_right_but_creating_the_product(
        CreateProductValueFormEvent $event,
        AbstractProductValue $value,
        AbstractAttribute $sku,
        AttributeGroup $group,
        $context
    ) {
        $event->getProductValue()->willReturn($value);
        $event->getFormOptions()->willReturn([]);
        $event->getContext()->willReturn(['root_form_name' => 'pim_catalog_create']);
        $value->getAttribute()->willReturn($sku);
        $sku->getGroup()->willReturn($group);

        $event->updateFormOptions(['disabled' => true, 'read_only' => true])->shouldNotBeCalled();

        $this->onCreateProductValueForm($event);
    }
}
