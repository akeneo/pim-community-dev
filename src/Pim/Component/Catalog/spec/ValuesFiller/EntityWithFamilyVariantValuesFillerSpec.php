<?php

namespace spec\Pim\Component\Catalog\ValuesFiller;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Builder\EntityWithValuesBuilderInterface;
use Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Manager\AttributeValuesResolverInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Repository\CurrencyRepositoryInterface;
use Prophecy\Argument;

class EntityWithFamilyVariantValuesFillerSpec extends ObjectBehavior
{
    function let(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeValuesResolverInterface $valuesResolver,
        CurrencyRepositoryInterface $currencyRepository,
        EntityWithFamilyVariantAttributesProvider $attributesProvider
    ) {
        $this->beConstructedWith($entityWithValuesBuilder, $valuesResolver, $currencyRepository, $attributesProvider);
    }

    function it_throws_an_exception_if_this_is_not_an_entity_with_a_family_variant(
        EntityWithFamilyInterface $foo
    ) {
        $this->shouldThrow(\InvalidArgumentException::class)->during('fillMissingValues', [$foo]);
    }

    function it_fills_missing_product_values_from_attribute_set_on_new_entity_with_family_variant(
        $attributesProvider,
        $valuesResolver,
        $entityWithValuesBuilder,
        ProductModelInterface $entity,
        ProductModelInterface $parentEntity,
        AttributeInterface $name,
        AttributeInterface $color,
        AttributeInterface $sku,
        ValueInterface $skuValue,
        ValueInterface $colorValue
    ) {
        $sku->getCode()->willReturn('sku');
        $sku->getType()->willReturn('pim_catalog_identifier');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $name->getCode()->willReturn('name');
        $name->getType()->willReturn('pim_catalog_text');
        $name->isLocalizable()->willReturn(true);
        $name->isScopable()->willReturn(false);

        $color->getCode()->willReturn('color');
        $color->getType()->willReturn('pim_catalog_simpleselect');
        $color->isLocalizable()->willReturn(false);
        $color->isScopable()->willReturn(false);

        $expectedParentAttributes = [$name];
        $expectedAttributes = [$sku, $color];

        $parentEntity->getParent()->willReturn(null);
        $entity->getParent()->willReturn($parentEntity);

        $attributesProvider->getAttributes($parentEntity)->willReturn($expectedParentAttributes);
        $attributesProvider->getAttributes($entity)->willReturn($expectedAttributes);

        $valuesResolver->resolveEligibleValues(['sku' => $sku, 'name' => $name, 'color' => $color])
            ->willReturn([
                [
                    'attribute' => 'sku',
                    'type' => 'pim_catalog_identifier',
                    'locale' => null,
                    'scope' => null
                ],
                [
                    'attribute' => 'name',
                    'type' => 'pim_catalog_text',
                    'locale' => 'fr_FR',
                    'scope' => null
                ],
                [
                    'attribute' => 'name',
                    'type' => 'pim_catalog_text',
                    'locale' => 'en_US',
                    'scope' => null
                ],
                [
                    'attribute' => 'color',
                    'type' => 'pim_catalog_simpleselect',
                    'locale' => null,
                    'scope' => null
                ]
            ]);

        // get existing values
        $skuValue->getAttribute()->willReturn($sku);
        $skuValue->getLocale()->willReturn(null);
        $skuValue->getScope()->willReturn(null);

        $colorValue->getAttribute()->willReturn($color);
        $colorValue->getLocale()->willReturn(null);
        $colorValue->getScope()->willReturn(null);

        $entity->getValues()->willReturn([$skuValue, $colorValue]);

        $entityWithValuesBuilder->addOrReplaceValue(Argument::cetera())->shouldBeCalledTimes(2);

        $this->fillMissingValues($entity);
    }

    function it_fills_missing_product_values_from_attribute_set_on_new_entity_with_family_variant_without_parent(
        $attributesProvider,
        $valuesResolver,
        $entityWithValuesBuilder,
        ProductModelInterface $entity,
        AttributeInterface $name
    ) {
        $name->getCode()->willReturn('name');
        $name->getType()->willReturn('pim_catalog_text');
        $name->isLocalizable()->willReturn(true);
        $name->isScopable()->willReturn(false);

        $expectedAttributes = [$name];

        $entity->getParent()->willReturn(null);
        $attributesProvider->getAttributes($entity)->willReturn($expectedAttributes);

        $valuesResolver->resolveEligibleValues(['name' => $name])
            ->willReturn([
                [
                    'attribute' => 'name',
                    'type' => 'pim_catalog_text',
                    'locale' => 'fr_FR',
                    'scope' => null
                ],
                [
                    'attribute' => 'name',
                    'type' => 'pim_catalog_text',
                    'locale' => 'en_US',
                    'scope' => null
                ]
            ]);

        $entity->getValues()->willReturn([]);
        $entityWithValuesBuilder->addOrReplaceValue(Argument::cetera())->shouldBeCalledTimes(2);

        $this->fillMissingValues($entity);
    }

    function it_fills_nothing_if_the_entity_with_family_variant_already_has_all_its_values(
        $attributesProvider,
        $valuesResolver,
        $entityWithValuesBuilder,
        ProductModelInterface $entity,
        ProductModelInterface $parentEntity,
        AttributeInterface $name,
        AttributeInterface $color,
        AttributeInterface $sku,
        ValueInterface $skuValue,
        ValueInterface $nameFRValue,
        ValueInterface $nameENValue,
        ValueInterface $colorValue
    ) {
        $sku->getCode()->willReturn('sku');
        $sku->getType()->willReturn('pim_catalog_identifier');
        $sku->isLocalizable()->willReturn(false);
        $sku->isScopable()->willReturn(false);

        $name->getCode()->willReturn('name');
        $name->getType()->willReturn('pim_catalog_text');
        $name->isLocalizable()->willReturn(true);
        $name->isScopable()->willReturn(false);

        $color->getCode()->willReturn('color');
        $color->getType()->willReturn('pim_catalog_simpleselect');
        $color->isLocalizable()->willReturn(false);
        $color->isScopable()->willReturn(false);

        $expectedParentAttributes = [$name];
        $expectedAttributes = [$sku, $color];

        $parentEntity->getParent()->willReturn(null);
        $entity->getParent()->willReturn($parentEntity);

        $attributesProvider->getAttributes($parentEntity)->willReturn($expectedParentAttributes);
        $attributesProvider->getAttributes($entity)->willReturn($expectedAttributes);

        $valuesResolver->resolveEligibleValues(['sku' => $sku, 'name' => $name, 'color' => $color])
            ->willReturn([
                [
                    'attribute' => 'sku',
                    'type' => 'pim_catalog_identifier',
                    'locale' => null,
                    'scope' => null
                ],
                [
                    'attribute' => 'name',
                    'type' => 'pim_catalog_text',
                    'locale' => 'fr_FR',
                    'scope' => null
                ],
                [
                    'attribute' => 'name',
                    'type' => 'pim_catalog_text',
                    'locale' => 'en_US',
                    'scope' => null
                ],
                [
                    'attribute' => 'color',
                    'type' => 'pim_catalog_simpleselect',
                    'locale' => null,
                    'scope' => null
                ]
            ]);

        // get existing values
        $skuValue->getAttribute()->willReturn($sku);
        $skuValue->getLocale()->willReturn(null);
        $skuValue->getScope()->willReturn(null);

        $nameFRValue->getAttribute()->willReturn($name);
        $nameFRValue->getLocale()->willReturn('fr_FR');
        $nameFRValue->getScope()->willReturn(null);

        $nameENValue->getAttribute()->willReturn($name);
        $nameENValue->getLocale()->willReturn('en_US');
        $nameENValue->getScope()->willReturn(null);

        $colorValue->getAttribute()->willReturn($color);
        $colorValue->getLocale()->willReturn(null);
        $colorValue->getScope()->willReturn(null);

        $entity->getValues()->willReturn([$skuValue, $nameFRValue, $nameENValue, $colorValue]);

        $entityWithValuesBuilder->addOrReplaceValue(Argument::cetera())->shouldNotBeCalled();

        $this->fillMissingValues($entity);
    }
}
