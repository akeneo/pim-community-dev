<?php

namespace spec\Pim\Bundle\EnrichBundle\Form\Factory;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Pim\Bundle\EnrichBundle\Form\Factory\ProductValueFormFactory;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeFactory;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\AttributeType\TextType;


class ProductValueFormFactorySpec extends ObjectBehavior
{
    function let(FormFactoryInterface $formFactory, AttributeTypeFactory $attributeTypeFactory, EventDispatcherInterface $dispatcher)
    {
        $this->beConstructedWith($formFactory, $attributeTypeFactory, $dispatcher);
    }

    function it_builds_product_value_form(
        FormInterface $form,
        ProductValueInterface $value,
        AbstractAttribute $sku,
        TextType $textType,
        $attributeTypeFactory,
        $dispatcher,
        $formFactory
    ) {
        $value->getAttribute()->willReturn($sku);
        $sku->getAttributeType()->willReturn('pim_catalog_text');
        $attributeTypeFactory->get('pim_catalog_text')->willReturn($textType);

        $textType->prepareValueFormName($value)->shouldBeCalled();
        $textType->prepareValueFormAlias($value)->shouldBeCalled();
        $textType->prepareValueFormData($value)->shouldBeCalled();
        $textType->prepareValueFormConstraints($value)->shouldBeCalled()->willReturn([]);
        $textType->prepareValueFormOptions($value)->shouldBeCalled()
->willReturn([]);


        $dispatcher->dispatch(Argument::any(), Argument::any(), Argument::any())->shouldBeCalled();

        $formFactory->createNamed(Argument::any(), Argument::any(), Argument::any(), Argument::any())->shouldBeCalled();

        $this->buildProductValueForm($value, ['root_form_name' => 'pim_product_edit']);
    }
}
