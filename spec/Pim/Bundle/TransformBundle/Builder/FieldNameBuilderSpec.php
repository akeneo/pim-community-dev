<?php

namespace spec\Pim\Bundle\TransformBundle\Builder;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\SmartManagerRegistry;
use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

class FieldNameBuilderSpec extends ObjectBehavior
{
    const ASSOC_TYPE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\AssociationType';
    const ATTRIBUTE_CLASS  = 'Pim\Bundle\CatalogBundle\Entity\Attribute';

    function let(SmartManagerRegistry $managerRegistry)
    {
        $this->beConstructedWith($managerRegistry, self::ASSOC_TYPE_CLASS, self::ATTRIBUTE_CLASS);
    }

    function it_returns_association_type_field_names(
        $managerRegistry,
        AssociationTypeRepository $repository,
        AssociationType $assocType1,
        AssociationType $assocType2
    ) {
        $assocType1->getCode()->willReturn("ASSOC_TYPE_1");
        $assocType2->getCode()->willReturn("ASSOC_TYPE_2");
        $repository->findAll()->willReturn([$assocType1, $assocType2]);
        $managerRegistry->getRepository(self::ASSOC_TYPE_CLASS)->willReturn($repository);

        $this->getAssociationFieldNames()->shouldReturn(
            [
                "ASSOC_TYPE_1-groups",
                "ASSOC_TYPE_1-products",
                "ASSOC_TYPE_2-groups",
                "ASSOC_TYPE_2-products"
            ]
        );
    }

    function it_returns_attribute_informations_from_field_name(
        $managerRegistry,
        AttributeRepository $repository,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('foo');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('bar');
        $repository->findByReference('foo')->willReturn($attribute);
        $managerRegistry->getRepository(self::ATTRIBUTE_CLASS)->willReturn($repository);

        $this->extractAttributeFieldNameInfos('foo')->shouldReturn(
            [
                'attribute'   => $attribute,
                'locale_code' => null,
                'scope_code'  => null
            ]
        );
    }

    function it_returns_null_attribute_informations_from_unknown_field_name(
        $managerRegistry,
        AttributeRepository $repository
    ) {
        $repository->findByReference('foo')->willReturn(null);
        $managerRegistry->getRepository(self::ATTRIBUTE_CLASS)->willReturn($repository);

        $this->extractAttributeFieldNameInfos('foo')->shouldReturn(null);
    }

    function it_returns_attribute_informations_from_field_name_with_localizable_attribute(
        $managerRegistry,
        AttributeRepository $repository,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('foo');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('bar');
        $repository->findByReference('foo')->willReturn($attribute);
        $managerRegistry->getRepository(self::ATTRIBUTE_CLASS)->willReturn($repository);

        // Test only localizable attribute
        $this->extractAttributeFieldNameInfos('foo-en_US')->shouldReturn(
            [
                'attribute'   => $attribute,
                'locale_code' => 'en_US',
                'scope_code'  => null
            ]
        );

        // Test localizable + scopable attribute
        $attribute->isScopable()->willReturn(true);
        $this->extractAttributeFieldNameInfos('foo-en_US-ecommerce')->shouldReturn(
            [
                'attribute'   => $attribute,
                'locale_code' => 'en_US',
                'scope_code'  => 'ecommerce'
            ]
        );

        // Test localizable + scopable + price attribute
        $attribute->getBackendType()->willReturn('prices');
        $this->extractAttributeFieldNameInfos('foo-en_US-ecommerce-EUR')->shouldReturn(
            [
                'attribute'      => $attribute,
                'locale_code'    => 'en_US',
                'scope_code'     => 'ecommerce',
                'price_currency' => 'EUR'
            ]
        );

        // Test localizable + price attribute
        $attribute->isScopable()->willReturn(false);
        $this->extractAttributeFieldNameInfos('foo-en_US-EUR')->shouldReturn(
            [
                'attribute'      => $attribute,
                'locale_code'    => 'en_US',
                'scope_code'     => null,
                'price_currency' => 'EUR'
            ]
        );
    }

    function it_returns_attribute_informations_from_field_name_with_scopable_attribute(
        $managerRegistry,
        AttributeRepository $repository,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('foo');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(true);
        $attribute->getBackendType()->willReturn('bar');
        $repository->findByReference('foo')->willReturn($attribute);
        $managerRegistry->getRepository(self::ATTRIBUTE_CLASS)->willReturn($repository);

        // Test only scopable attribute
        $this->extractAttributeFieldNameInfos('foo-ecommerce')->shouldReturn(
            [
                'attribute'   => $attribute,
                'locale_code' => null,
                'scope_code'  => 'ecommerce'
            ]
        );

        // Test scopable + price attribute
        $attribute->getBackendType()->willReturn('prices');
        $this->extractAttributeFieldNameInfos('foo-ecommerce-EUR')->shouldReturn(
            [
                'attribute'      => $attribute,
                'locale_code'    => null,
                'scope_code'     => 'ecommerce',
                'price_currency' => 'EUR'
            ]
        );
    }

    function it_returns_attribute_informations_from_field_name_with_price_attribute(
        $managerRegistry,
        AttributeRepository $repository,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('foo');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('prices');
        $repository->findByReference('foo')->willReturn($attribute);
        $managerRegistry->getRepository(self::ATTRIBUTE_CLASS)->willReturn($repository);

        $this->extractAttributeFieldNameInfos('foo-USD')->shouldReturn(
            [
                'attribute'   => $attribute,
                'locale_code' => null,
                'scope_code'  => null,
                'price_currency' => 'USD'
            ]
        );
    }

    function it_extracts_association_field_name_informations()
    {
        $this
            ->extractAssociationFieldNameInfos('X_SELL-groups')
            ->shouldReturn(['assoc_type_code' => 'X_SELL', 'part' => 'groups']);

        $this
            ->extractAssociationFieldNameInfos('X_SELL-products')
            ->shouldReturn(['assoc_type_code' => 'X_SELL', 'part' => 'products']);

        $this
            ->extractAssociationFieldNameInfos('10Foo-groups')
            ->shouldReturn(['assoc_type_code' => '10Foo', 'part' => 'groups']);

        $this
            ->extractAssociationFieldNameInfos('X_SELL-foo')
            ->shouldBe(null);

        $this
            ->extractAssociationFieldNameInfos('bar')
            ->shouldBe(null);
    }

    function it_throws_exception_when_the_field_name_is_not_consistent_with_the_attribute_property(
        $managerRegistry,
        AttributeRepository $repository,
        AttributeInterface $attribute
    ) {
        // global with extra locale
        $attribute->getCode()->willReturn('sku');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('text');
        $repository->findByReference('sku')->willReturn($attribute);
        $managerRegistry->getRepository(self::ATTRIBUTE_CLASS)->willReturn($repository);

        $this->shouldThrow(new \InvalidArgumentException('The field "sku-fr_FR" is not well-formated, attribute "sku" expects no locale, no scope, no currency'))
            ->duringExtractAttributeFieldNameInfos('sku-fr_FR');

        // localizable without any locale
        $attribute->getCode()->willReturn('name');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('text');
        $repository->findByReference('name')->willReturn($attribute);

        $this->shouldThrow(new \InvalidArgumentException('The field "name" is not well-formated, attribute "name" expects a locale, no scope, no currency'))
            ->duringExtractAttributeFieldNameInfos('name');

        // localizable, scopable and price without any currency
        $attribute->getCode()->willReturn('cost');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $attribute->getBackendType()->willReturn('prices');
        $repository->findByReference('cost')->willReturn($attribute);

        $this->shouldThrow(new \InvalidArgumentException('The field "cost" is not well-formated, attribute "cost" expects a locale, a scope, an optional currency'))
            ->duringExtractAttributeFieldNameInfos('cost');
    }
}
