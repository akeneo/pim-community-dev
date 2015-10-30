<?php

namespace spec\Pim\Bundle\CatalogBundle\Builder;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Component\Localization\LocaleResolver;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductTemplateBuilderSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        ProductBuilder $productBuilder,
        LocaleResolver $localeResolver
    ) {
        $this->beConstructedWith(
            $normalizer,
            $denormalizer,
            $productBuilder,
            $localeResolver,
            'Pim\Bundle\CatalogBundle\Entity\ProductTemplate',
            'Pim\Bundle\CatalogBundle\Model\Product'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Builder\ProductTemplateBuilder');
    }

    function it_creates_product_templates()
    {
        $this->createProductTemplate()->shouldReturnAnInstanceOf('Pim\Bundle\CatalogBundle\Entity\ProductTemplate');
    }

    function it_adds_attributes_to_a_product_template(
        $denormalizer,
        $normalizer,
        $productBuilder,
        $localeResolver,
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

        $options = ['locale' => 'en_US'];
        $localeResolver->getCurrentLocale()->willReturn('en_US');
        $template->getValuesData()->willReturn(['color' => 'bar']);
        $denormalizer
            ->denormalize(['color' => 'bar'], 'ProductValue[]', 'json', $options)
            ->shouldBeCalled()->willReturn([$colorValue]);

        $productBuilder
            ->addAttributeToProduct(Argument::type('Pim\Bundle\CatalogBundle\Model\Product'), $name)
            ->shouldBeCalled();
        $productBuilder
            ->addMissingProductValues(Argument::type('Pim\Bundle\CatalogBundle\Model\Product'))
            ->shouldBeCalled();

        $normalizer
            ->normalize(Argument::type('Doctrine\Common\Collections\ArrayCollection'), 'json', [
                'entity' => 'product',
                'locale' => 'en_US',
            ])
            ->shouldBeCalled()
            ->willReturn(['name' => 'foo', 'color' => 'bar']);

        $template->setValuesData(['name' => 'foo', 'color' => 'bar'])->shouldBeCalled();

        $this->addAttributes($template, [$name]);
    }

    function it_removes_attributes_from_a_product_template(
        ProductTemplateInterface $template,
        AttributeInterface $name
    ) {
        $name->getCode()->willReturn('name');
        $template->getValuesData()->willReturn(['name' => 'foo', 'color' => 'bar']);
        $template->setValuesData(['color' => 'bar'])->shouldBeCalled();

        $this->removeAttribute($template, $name);
    }
}
