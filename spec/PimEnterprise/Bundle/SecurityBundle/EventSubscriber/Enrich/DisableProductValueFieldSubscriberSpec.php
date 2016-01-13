<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Enrich;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use Pim\Bundle\EnrichBundle\Event\CreateProductValueFormEvent;
use Pim\Bundle\EnrichBundle\Event\ProductEvents;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DisableProductValueFieldSubscriberSpec extends ObjectBehavior
{
    function let(AuthorizationCheckerInterface $context)
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
            ProductEvents::CREATE_VALUE_FORM => 'onCreateProductValueForm'
        ]);
    }

    function it_disables_the_product_value_form_when_no_edit_right(
        CreateProductValueFormEvent $event,
        ProductValueInterface $value,
        AttributeInterface $sku,
        AttributeGroupInterface $group,
        $context
    ) {
        $event->getProductValue()->willReturn($value);
        $event->getFormOptions()->willReturn([]);
        $event->getContext()->willReturn(['root_form_name' => 'pim_catalog_edit']);
        $value->getAttribute()->willReturn($sku);
        $sku->getGroup()->willReturn($group);

        $context->isGranted(Attributes::EDIT_ATTRIBUTES, $group)->willReturn(false);
        $event->updateFormOptions(['disabled' => true, 'read_only' => true])->shouldBeCalled();

        $this->onCreateProductValueForm($event);
    }

    function it_doesnt_disable_the_product_value_form_when_no_edit_right_but_creating_the_product(
        CreateProductValueFormEvent $event,
        ProductValueInterface $value,
        AttributeInterface $sku,
        AttributeGroupInterface $group,
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
