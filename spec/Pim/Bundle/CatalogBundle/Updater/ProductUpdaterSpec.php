<?php

namespace spec\Pim\Bundle\CatalogBundle\Updater;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Updater\Copier\CopierInterface;
use Pim\Bundle\CatalogBundle\Updater\Copier\CopierRegistryInterface;
use Pim\Bundle\CatalogBundle\Updater\Setter\SetterInterface;
use Pim\Bundle\CatalogBundle\Updater\Setter\SetterRegistryInterface;
use Prophecy\Argument;

class ProductUpdaterSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        SetterRegistryInterface $setterRegistry,
        CopierRegistryInterface $copierRegistry
    ) {
        $this->beConstructedWith($attributeRepository, $setterRegistry, $copierRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\CatalogBundle\Updater\ProductUpdater');
    }

    function it_sets_a_value(
        $setterRegistry,
        $attributeRepository,
        ProductInterface $product1,
        ProductInterface $product2,
        AttributeInterface $attribute,
        SetterInterface $setter
    ) {
        $products = [$product1, $product2];

        $attributeRepository->findOneBy(['code' => 'field'])->willReturn($attribute);
        $setterRegistry->get($attribute)->willReturn($setter);
        $setter->setValue($products, $attribute, 'data', 'fr_FR', 'ecommerce')->shouldBeCalled();

        $this->setValue($products, 'field', 'data', 'fr_FR', 'ecommerce');
    }

    function it_throws_an_exception_when_it_sets_an_unknown_field($attributeRepository, ProductInterface $product)
    {
        $attributeRepository->findOneBy(Argument::any())->willReturn(null);
        $this->shouldThrow(new \LogicException('Unknown attribute "unknown_field".'))->during(
            'setValue', [[$product], 'unknown_field', 'data', 'fr_FR', 'ecommerce']
        );
    }

    function it_copies_a_value(
        $copierRegistry,
        $attributeRepository,
        ProductInterface $product1,
        ProductInterface $product2,
        AttributeInterface $fromAttribute,
        AttributeInterface $toAttribute,
        CopierInterface $copier
    ) {
        $products = [$product1, $product2];

        $attributeRepository->findOneBy(['code' => 'from_field'])->willReturn($fromAttribute);
        $attributeRepository->findOneBy(['code' => 'to_field'])->willReturn($toAttribute);
        $copierRegistry->get($fromAttribute, $toAttribute)->willReturn($copier);
        $copier
            ->copyValue($products, $fromAttribute, $toAttribute, 'from_locale', 'to_locale', 'from_scope', 'to_scope')
            ->shouldBeCalled();

        $this->copyValue($products, 'from_field', 'to_field', 'from_locale', 'to_locale', 'from_scope', 'to_scope');
    }

    function it_throws_an_exception_when_it_copies_an_unknown_field($attributeRepository, ProductInterface $product)
    {
        $attributeRepository->findOneBy(Argument::any())->willReturn(null);
        $this->shouldThrow(new \LogicException('Unknown attribute "unknown_field".'))->during(
            'copyValue', [[$product], 'unknown_field', 'to_field', 'from_locale', 'to_locale', 'from_scope', 'to_scope']
        );
    }
}
