<?php

namespace spec\Pim\Bundle\TransformBundle\Builder;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\SmartManagerRegistry;
use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\AssociationTypeInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;

class FieldNameBuilderSpec extends ObjectBehavior
{
    const ASSOC_TYPE_CLASS = 'Pim\Bundle\CatalogBundle\Entity\AssociationType';
    const ATTRIBUTE_CLASS  = 'Pim\Bundle\CatalogBundle\Entity\Attribute';
    const CHANNEL_CLASS  = 'Pim\Bundle\CatalogBundle\Entity\Channel';
    const LOCALE_CLASS  = 'Pim\Bundle\CatalogBundle\Entity\Locale';

    function let(SmartManagerRegistry $managerRegistry)
    {
        $this->beConstructedWith($managerRegistry, self::ASSOC_TYPE_CLASS, self::ATTRIBUTE_CLASS, self::CHANNEL_CLASS, self::LOCALE_CLASS);
    }

    function it_returns_association_type_field_names(
        $managerRegistry,
        AssociationTypeRepositoryInterface $repository,
        AssociationTypeInterface $assocType1,
        AssociationTypeInterface $assocType2
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
        AttributeRepositoryInterface $repository,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('foo');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('bar');
        $repository->findOneByIdentifier('foo')->willReturn($attribute);
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
        AttributeRepositoryInterface $repository
    ) {
        $repository->findOneByIdentifier('foo')->willReturn(null);
        $managerRegistry->getRepository(self::ATTRIBUTE_CLASS)->willReturn($repository);

        $this->extractAttributeFieldNameInfos('foo')->shouldReturn(null);
    }

    function it_returns_attribute_informations_from_field_name_with_localizable_attribute(
        $managerRegistry,
        AttributeRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        AttributeInterface $attribute,
        LocaleInterface $locale,
        ChannelInterface $channel
    ) {
        $attribute->getCode()->willReturn('foo');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('bar');
        $attribute->isLocaleSpecific()->willReturn(false);

        $managerRegistry->getRepository(self::CHANNEL_CLASS)->shouldBeCalled()->willReturn($channelRepository);
        $channelRepository->findOneByIdentifier('ecommerce')->shouldBeCalled()->willReturn($channel);

        $managerRegistry->getRepository(self::LOCALE_CLASS)->shouldBeCalled()->willReturn($localeRepository);
        $localeRepository->findOneByIdentifier('en_US')->shouldBeCalled()->willReturn($locale);

        $attributeRepository->findOneByIdentifier('foo')->willReturn($attribute);
        $managerRegistry->getRepository(self::ATTRIBUTE_CLASS)->willReturn($attributeRepository);

        $channel->hasLocale($locale)->shouldBeCalled()->willReturn(true);

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
        AttributeRepositoryInterface $repository,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('foo');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(true);
        $attribute->getBackendType()->willReturn('bar');
        $repository->findOneByIdentifier('foo')->willReturn($attribute);
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
        AttributeRepositoryInterface $repository,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('foo');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('prices');
        $repository->findOneByIdentifier('foo')->willReturn($attribute);
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
        AttributeRepositoryInterface $repository,
        AttributeInterface $attribute
    ) {
        // global with extra locale
        $attribute->getCode()->willReturn('sku');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('text');
        $repository->findOneByIdentifier('sku')->willReturn($attribute);
        $managerRegistry->getRepository(self::ATTRIBUTE_CLASS)->willReturn($repository);

        $this->shouldThrow(new \InvalidArgumentException('The field "sku-fr_FR" is not well-formatted, attribute "sku" expects no locale, no scope, no currency'))
            ->duringExtractAttributeFieldNameInfos('sku-fr_FR');

        // localizable without any locale
        $attribute->getCode()->willReturn('name');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('text');
        $repository->findOneByIdentifier('name')->willReturn($attribute);

        $this->shouldThrow(new \InvalidArgumentException('The field "name" is not well-formatted, attribute "name" expects a locale, no scope, no currency'))
            ->duringExtractAttributeFieldNameInfos('name');

        // localizable, scopable and price without any currency
        $attribute->getCode()->willReturn('cost');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $attribute->getBackendType()->willReturn('prices');
        $repository->findOneByIdentifier('cost')->willReturn($attribute);

        $this->shouldThrow(new \InvalidArgumentException('The field "cost" is not well-formatted, attribute "cost" expects a locale, a scope, an optional currency'))
            ->duringExtractAttributeFieldNameInfos('cost');
    }

    function it_throws_exception_when_the_field_name_is_not_consistent_with_the_channel_locale(
        $managerRegistry,
        IdentifiableObjectRepositoryInterface $repository,
        AttributeInterface $attribute,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        LocaleInterface $locale,
        ChannelInterface $channel
    ) {
        // localizable without the associated locale not in the channel
        $attribute->getCode()->willReturn('description');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $attribute->getBackendType()->willReturn('text');
        $attribute->isLocaleSpecific()->willReturn(false);

        $attributeInfos =
            [
                'attribute'   => $attribute,
                'locale_code' => 'de_DE',
                'scope_code'  => 'mobile'
            ];

        $managerRegistry->getRepository(self::ATTRIBUTE_CLASS)->willReturn($repository);
        $repository->findOneByIdentifier('description')->willReturn($attribute);

        $managerRegistry->getRepository(self::CHANNEL_CLASS)->shouldBeCalled()->willReturn($channelRepository);
        $channelRepository->findOneByIdentifier($attributeInfos['scope_code'])->shouldBeCalled()->willReturn($channel);

        $managerRegistry->getRepository(self::LOCALE_CLASS)->shouldBeCalled()->willReturn($localeRepository);
        $localeRepository->findOneByIdentifier($attributeInfos['locale_code'])->shouldBeCalled()->willReturn($locale);

        $channel->hasLocale($locale)->shouldBeCalled()->willReturn(false);

        $this->shouldThrow(new \InvalidArgumentException('The locale "de_DE" of the field "description-de_DE-mobile" is not available in scope "mobile"'))
            ->duringExtractAttributeFieldNameInfos('description-de_DE-mobile');
    }
}
