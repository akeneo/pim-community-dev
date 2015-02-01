<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Factory;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeRegistry;
use Pim\Bundle\CatalogBundle\AttributeType\TextType;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class ProductValueFormFactorySpec extends ObjectBehavior
{
    function let(
        FormFactoryInterface $formFactory,
        AttributeTypeRegistry $attributeTypeRegistry,
        EventDispatcherInterface $dispatcher
    ) {
        $this->beConstructedWith($formFactory, $attributeTypeRegistry, $dispatcher);
    }

    function it_creates_product_value_form(
        FormInterface $form,
        ProductValueInterface $value,
        AttributeInterface $sku,
        TextType $textType,
        $attributeTypeRegistry,
        $dispatcher,
        $formFactory
    ) {
        $value->getAttribute()->willReturn($sku);
        $sku->getAttributeType()->willReturn('pim_catalog_text');
        $attributeTypeRegistry->get('pim_catalog_text')->willReturn($textType);

        $textType->prepareValueFormName($value)->shouldBeCalled();
        $textType->prepareValueFormAlias($value)->shouldBeCalled();
        $textType->prepareValueFormData($value)->shouldBeCalled();
        $textType->prepareValueFormConstraints($value)->shouldBeCalled()->willReturn([]);
        $textType->prepareValueFormOptions($value)->shouldBeCalled()->willReturn([]);

        $dispatcher->dispatch(Argument::any(), Argument::any(), Argument::any())->shouldBeCalled();

        $formFactory->createNamed(Argument::any(), Argument::any(), Argument::any(), Argument::any())->shouldBeCalled();

        $this->createProductValueForm($value, ['root_form_name' => 'pim_product_edit']);
    }
}
