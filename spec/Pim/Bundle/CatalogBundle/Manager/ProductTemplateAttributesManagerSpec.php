<?php

namespace spec\Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductTemplateAttributesManagerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer,
        AttributeRepositoryInterface $attributeRepository,
        ProductBuilder $productBuilder
    ) {
        $this->beConstructedWith(
            $normalizer,
            $denormalizer,
            $productBuilder,
            $attributeRepository,
            'Pim\Bundle\CatalogBundle\Model\Product'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Manager\ProductTemplateAttributesManager');
    }

    function it_returns_non_eligible_attributes(
        $attributeRepository,
        GroupInterface $group,
        ProductTemplateInterface $template,
        AttributeInterface $length,
        AttributeInterface $name,
        AttributeInterface $color,
        AttributeInterface $identifier,
        Collection $collection
    ) {
        $group->getProductTemplate()->willReturn($template);
        $group->getAxisAttributes()->willReturn($collection);
        $collection->toArray()->willReturn([$length]);

        $template->getValuesData()->willReturn(['name' => 'foo', 'color' => 'bar']);

        $attributeRepository->findOneByIdentifier('name')->willReturn($name);
        $attributeRepository->findOneByIdentifier('color')->willReturn($color);
        $attributeRepository->findBy(['unique' => true])->willReturn([$name, $identifier]);

        $attributes = [$length, $name, $color, $identifier];
        $this->getNonEligibleAttributes($group)->shouldReturn($attributes);
    }

    function it_adds_attributes_to_a_product_template(
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
