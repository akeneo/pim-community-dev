<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;

class AttributeColumnInfoExtractorSpec extends ObjectBehavior
{
    const ASSOC_TYPE_CLASS = AssociationType::class;
    const ATTRIBUTE_CLASS = Attribute::class;
    const CHANNEL_CLASS = Channel::class;
    const LOCALE_CLASS = Locale::class;

    function let(
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        AssociationColumnsResolver $assoColumnResolver
    ) {
        $this->beConstructedWith($attributeRepository, $channelRepository, $localeRepository, $assoColumnResolver);
    }

    function it_returns_attribute_informations_from_field_name(
        $attributeRepository,
        $assoColumnResolver,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('foo');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('bar');
        $attributeRepository->findOneByIdentifier('foo')->willReturn($attribute);
        $assoColumnResolver->resolveAssociationColumns()->willReturn([]);

        $this->extractColumnInfo('foo')->shouldReturn(
            [
                'attribute'   => $attribute,
                'locale_code' => null,
                'scope_code'  => null
            ]
        );
    }

    function it_returns_null_attribute_informations_from_unknown_field_name(
        $channelRepository,
        $assoColumnResolver
    ) {
        $channelRepository->findOneByIdentifier('foo')->willReturn(null);
        $assoColumnResolver->resolveAssociationColumns()->willReturn([]);

        $this->extractColumnInfo('foo')->shouldReturn(null);
    }

    function it_returns_attribute_informations_from_field_name_with_localizable_attribute(
        $attributeRepository,
        $channelRepository,
        $localeRepository,
        $assoColumnResolver,
        AttributeInterface $attribute,
        LocaleInterface $locale,
        ChannelInterface $channel
    ) {
        $attribute->getCode()->willReturn('foo');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('bar');
        $attribute->isLocaleSpecific()->willReturn(false);

        $channelRepository->findOneByIdentifier('ecommerce')->shouldBeCalled()->willReturn($channel);
        $localeRepository->findOneByIdentifier('en_US')->shouldBeCalled()->willReturn($locale);
        $attributeRepository->findOneByIdentifier('foo')->willReturn($attribute);
        $assoColumnResolver->resolveAssociationColumns()->willReturn([]);

        $channel->hasLocale($locale)->shouldBeCalled()->willReturn(true);

        // Test only localizable attribute
        $this->extractColumnInfo('foo-en_US')->shouldReturn(
            [
                'attribute'   => $attribute,
                'locale_code' => 'en_US',
                'scope_code'  => null
            ]
        );

        // Test localizable + scopable attribute
        $attribute->isScopable()->willReturn(true);
        $this->extractColumnInfo('foo-en_US-ecommerce')->shouldReturn(
            [
                'attribute'   => $attribute,
                'locale_code' => 'en_US',
                'scope_code'  => 'ecommerce'
            ]
        );

        // Test localizable + scopable + price attribute
        $attribute->getBackendType()->willReturn('prices');
        $this->extractColumnInfo('foo-en_US-ecommerce-EUR')->shouldReturn(
            [
                'attribute'      => $attribute,
                'locale_code'    => 'en_US',
                'scope_code'     => 'ecommerce',
                'price_currency' => 'EUR'
            ]
        );

        // Test localizable + price attribute
        $attribute->isScopable()->willReturn(false);
        $this->extractColumnInfo('foo-en_US-EUR')->shouldReturn(
            [
                'attribute'      => $attribute,
                'locale_code'    => 'en_US',
                'scope_code'     => null,
                'price_currency' => 'EUR'
            ]
        );
    }

    function it_returns_attribute_informations_from_field_name_with_scopable_attribute(
        $attributeRepository,
        $assoColumnResolver,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('foo');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(true);
        $attribute->getBackendType()->willReturn('bar');
        $attributeRepository->findOneByIdentifier('foo')->willReturn($attribute);
        $assoColumnResolver->resolveAssociationColumns()->willReturn([]);

        // Test only scopable attribute
        $this->extractColumnInfo('foo-ecommerce')->shouldReturn(
            [
                'attribute'   => $attribute,
                'locale_code' => null,
                'scope_code'  => 'ecommerce'
            ]
        );

        // Test scopable + price attribute
        $attribute->getBackendType()->willReturn('prices');
        $this->extractColumnInfo('foo-ecommerce-EUR')->shouldReturn(
            [
                'attribute'      => $attribute,
                'locale_code'    => null,
                'scope_code'     => 'ecommerce',
                'price_currency' => 'EUR'
            ]
        );
    }

    function it_returns_attribute_informations_from_field_name_with_price_attribute(
        $attributeRepository,
        $assoColumnResolver,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('foo');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('prices');
        $attributeRepository->findOneByIdentifier('foo')->willReturn($attribute);
        $assoColumnResolver->resolveAssociationColumns()->willReturn([]);

        $this->extractColumnInfo('foo-USD')->shouldReturn(
            [
                'attribute'   => $attribute,
                'locale_code' => null,
                'scope_code'  => null,
                'price_currency' => 'USD'
            ]
        );
    }

    function it_throws_exception_when_the_field_name_is_not_consistent_with_the_attribute_property(
        $attributeRepository,
        $assoColumnResolver,
        AttributeInterface $attribute
    ) {
        // global with extra locale
        $attribute->getCode()->willReturn('sku');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('text');
        $attributeRepository->findOneByIdentifier('sku')->willReturn($attribute);
        $assoColumnResolver->resolveAssociationColumns()->willReturn([]);

        $this->shouldThrow(new \InvalidArgumentException('The field "sku-fr_FR" is not well-formatted, attribute "sku" expects no locale, no scope, no currency'))
            ->duringExtractColumnInfo('sku-fr_FR');

        // localizable without any locale
        $attribute->getCode()->willReturn('name');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('text');
        $attributeRepository->findOneByIdentifier('name')->willReturn($attribute);

        $this->shouldThrow(new \InvalidArgumentException('The field "name" is not well-formatted, attribute "name" expects a locale, no scope, no currency'))
            ->duringExtractColumnInfo('name');

        // localizable, scopable and price without any currency
        $attribute->getCode()->willReturn('cost');
        $attribute->isLocalizable()->willReturn(true);
        $attribute->isScopable()->willReturn(true);
        $attribute->getBackendType()->willReturn('prices');
        $attributeRepository->findOneByIdentifier('cost')->willReturn($attribute);

        $this->shouldThrow(new \InvalidArgumentException('The field "cost" is not well-formatted, attribute "cost" expects a locale, a scope, an optional currency'))
            ->duringExtractColumnInfo('cost');
    }

    function it_doesnt_hrow_exception_when_the_field_is_an_association_column(
        $attributeRepository,
        $assoColumnResolver,
        AttributeInterface $attribute
    ) {
        $attribute->getCode()->willReturn('cost');
        $attribute->isLocalizable()->willReturn(false);
        $attribute->isScopable()->willReturn(false);
        $attribute->getBackendType()->willReturn('text');
        $attributeRepository->findOneByIdentifier('cost')->willReturn($attribute);
        $assoColumnResolver->resolveAssociationColumns()->willReturn(['cost-products', 'cost-groups']);

        $this->shouldNotThrow(new \InvalidArgumentException())
            ->duringExtractColumnInfo('cost-products');
    }

    function it_throws_exception_when_the_field_name_is_not_consistent_with_the_channel_locale(
        $attributeRepository,
        $channelRepository,
        $localeRepository,
        $assoColumnResolver,
        AttributeInterface $attribute,
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

        $attributeRepository->findOneByIdentifier('description')->willReturn($attribute);
        $channelRepository->findOneByIdentifier($attributeInfos['scope_code'])->shouldBeCalled()->willReturn($channel);
        $localeRepository->findOneByIdentifier($attributeInfos['locale_code'])->shouldBeCalled()->willReturn($locale);
        $assoColumnResolver->resolveAssociationColumns()->willReturn([]);

        $channel->hasLocale($locale)->shouldBeCalled()->willReturn(false);

        $this->shouldThrow(new \InvalidArgumentException('The locale "de_DE" of the field "description-de_DE-mobile" is not available in scope "mobile"'))
            ->duringExtractColumnInfo('description-de_DE-mobile');
    }
}
