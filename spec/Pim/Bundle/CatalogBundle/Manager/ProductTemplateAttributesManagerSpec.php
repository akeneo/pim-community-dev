<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductTemplateAttributesManagerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer, DenormalizerInterface $denormalizer, ProductBuilder $productBuilder)
    {
        $this->beConstructedWith($normalizer, $denormalizer, $productBuilder, 'Pim\Bundle\CatalogBundle\Model\Product');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Manager\ProductTemplateAttributesManager');
    }

    function it_can_add_attributes_to_a_product_template(
        $denormalizer,
        $normalizer,
        $productBuilder,
        ProductTemplateInterface $template,
        ProductValueInterface $colorValue,
        AttributeInterface $name,
        AttributeInterface $color
    ) {
        $color->getCode()->willReturn('color');
        $color->isLocalizable()->willReturn(false);
        $color->isScopable()->willReturn(false);
        $colorValue->getAttribute()->willReturn($color);
        $colorValue->setEntity(Argument::type('Pim\Bundle\CatalogBundle\Model\Product'))->willReturn($colorValue);

        $template->getValuesData()->willReturn(['color' => 'bar']);
        $denormalizer
            ->denormalize(['color' => 'bar'], 'ProductValue[]', 'json')
            ->shouldBeCalled()->willReturn([$colorValue]);

        $productBuilder
            ->addAttributeToProduct(Argument::type('Pim\Bundle\CatalogBundle\Model\Product'), $name)
            ->shouldBeCalled();

        $productBuilder
            ->addMissingProductValues(Argument::type('Pim\Bundle\CatalogBundle\Model\Product'))
            ->shouldBeCalled();

        $normalizer
            ->normalize(Argument::type('Doctrine\Common\Collections\ArrayCollection'), 'json', ['entity' => 'product'])
            ->shouldBeCalled()
            ->willReturn(['name' => 'foo', 'color' => 'bar']);

        $template->setValuesData(['name' => 'foo', 'color' => 'bar'])->shouldBeCalled();

        $this->addAttributes($template, [$name]);
    }

    function it_can_remove_attributes_from_a_product_template(
        ProductTemplateInterface $template,
        AttributeInterface $name
    ) {
        $name->getCode()->willReturn('name');
        $template->getValuesData()->willReturn(['name' => 'foo', 'color' => 'bar']);
        $template->setValuesData(['color' => 'bar'])->shouldBeCalled();

        $this->removeAttribute($template, $name);
    }
}
