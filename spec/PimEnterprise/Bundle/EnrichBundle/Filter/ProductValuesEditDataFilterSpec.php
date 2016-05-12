<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Filter;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Prophecy\Argument;

class ProductValuesEditDataFilterSpec extends ObjectBehavior
{
    function let(
        ObjectFilterInterface $objectFilter,
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository
    ) {
        $this->beConstructedWith(
            $objectFilter,
            $attributeRepository,
            $localeRepository,
            $channelRepository
        );
    }

    function it_filters_values_data_on_attributes_group_rights(
        $objectFilter,
        $attributeRepository,
        $localeRepository,
        $channelRepository,
        AttributeInterface $nameAttribute,
        AttributeInterface $descriptionAttribute,
        LocaleInterface $enLocale,
        ChannelInterface $mobileChannel,
        ProductInterface $product
    ) {
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])
            ->willReturn(true);

        $attributeRepository->findOneByIdentifier('description')->willReturn($descriptionAttribute);
        $descriptionAttribute->isLocaleSpecific()->willReturn(false);
        $descriptionAttribute->isLocalizable()->willReturn(true);
        $descriptionAttribute->isScopable()->willReturn(true);
        $descriptionAttribute->isReadOnly()->willReturn(false);
        $objectFilter->filterObject($descriptionAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])
            ->willReturn(false);

        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $objectFilter->filterObject($enLocale, 'pim.internal_api.locale.edit', ['product' => $product])
            ->willReturn(false);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($mobileChannel);

        $data = [
            'name' => [
                [
                    'locale'       => 'en_US',
                    'scope'        => 'mobile',
                    'value'        => 'My awesome product',
                    'is_read_only' => false,
                ]
            ],
            'description' => [
                [
                    'locale'       => 'en_US',
                    'scope'        => 'mobile',
                    'value'        => 'This product is really awesome !',
                    'is_read_only' => false,
                ]
            ]
        ];

        $this->filterCollection($data, null, ['product' => $product])->shouldReturn([
            'description' => [
                [
                    'locale'       => 'en_US',
                    'scope'        => 'mobile',
                    'value'        => 'This product is really awesome !',
                    'is_read_only' => false,
                ]
            ]
        ]);
    }

    function it_filters_values_data_on_locale_rights(
        $objectFilter,
        $attributeRepository,
        $localeRepository,
        $channelRepository,
        AttributeInterface $nameAttribute,
        AttributeInterface $descriptionAttribute,
        LocaleInterface $enLocale,
        LocaleInterface $svLocale,
        ChannelInterface $mobileChannel,
        ProductInterface $product
    ) {
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $nameAttribute->isLocaleSpecific()->willReturn(false);
        $nameAttribute->isLocalizable()->willReturn(true);
        $nameAttribute->isScopable()->willReturn(true);
        $nameAttribute->isReadOnly()->willReturn(true);
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])
            ->willReturn(false);

        $attributeRepository->findOneByIdentifier('description')->willReturn($descriptionAttribute);
        $descriptionAttribute->isLocaleSpecific()->willReturn(false);
        $descriptionAttribute->isLocalizable()->willReturn(true);
        $descriptionAttribute->isScopable()->willReturn(true);
        $descriptionAttribute->isReadOnly()->willReturn(false);
        $objectFilter->filterObject($descriptionAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])
            ->willReturn(false);

        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $objectFilter->filterObject($enLocale, 'pim.internal_api.locale.edit', ['product' => $product])
            ->willReturn(true);

        $svLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('sv_SE')->willReturn($svLocale);
        $objectFilter->filterObject($svLocale, 'pim.internal_api.locale.edit', ['product' => $product])
            ->willReturn(false);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($mobileChannel);

        $data = [
            'name' => [
                [
                    'locale'       => 'en_US',
                    'scope'        => 'mobile',
                    'value'        => 'My awesome product',
                    'is_read_only' => true,
                ],
                [
                    'locale'       => 'sv_SE',
                    'scope'        => 'mobile',
                    'value'        => 'Min juste produkt',
                    'is_read_only' => true,
                ]
            ],
            'description' => [
                [
                    'locale'       => 'en_US',
                    'scope'        => 'mobile',
                    'value'        => 'This product is really awesome !',
                    'is_read_only' => false,
                ],
                [
                    'locale'       => 'sv_SE',
                    'scope'        => 'mobile',
                    'value'        => 'Denna produkt är verkligen häftigt !',
                    'is_read_only' => false,
                ]
            ]
        ];

        $this->filterCollection($data, null, ['product' => $product])->shouldReturn([
            'description' => [
                [
                    'locale'       => 'sv_SE',
                    'scope'        => 'mobile',
                    'value'        => 'Denna produkt är verkligen häftigt !',
                    'is_read_only' => false,
                ]
            ]
        ]);
    }

    function it_filters_scopable_attribute_if_channel_has_been_removed(
        $objectFilter,
        $attributeRepository,
        $channelRepository,
        AttributeInterface $nameAttribute,
        ProductInterface $product
    ) {
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])
            ->willReturn(false);

        $nameAttribute->isScopable()->willReturn(true);
        $nameAttribute->isReadOnly()->willReturn(false);
        $channelRepository->findOneByIdentifier('print')->willReturn(null);

        $data = [
            'name' => [
                [
                    'locale'       => null,
                    'scope'        => 'print',
                    'value'        => 'My awesome product',
                    'is_read_only' => false,
                ],
            ],
        ];

        $this->filterCollection($data, null, ['product' => $product])->shouldReturn([]);
    }

    function it_does_not_filter_non_localizable_attributes(
        $objectFilter,
        $attributeRepository,
        $localeRepository,
        $channelRepository,
        AttributeInterface $nameAttribute,
        AttributeInterface $descriptionAttribute,
        LocaleInterface $enLocale,
        LocaleInterface $svLocale,
        ChannelInterface $mobileChannel,
        ProductInterface $product
    ) {
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $nameAttribute->isLocaleSpecific()->willReturn(false);
        $nameAttribute->isLocalizable()->willReturn(true);
        $nameAttribute->isScopable()->willReturn(true);
        $nameAttribute->isReadOnly()->willReturn(false);
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])
            ->willReturn(false);

        $attributeRepository->findOneByIdentifier('description')->willReturn($descriptionAttribute);
        $descriptionAttribute->isLocaleSpecific()->willReturn(false);
        $descriptionAttribute->isLocalizable()->willReturn(false);
        $descriptionAttribute->isScopable()->willReturn(true);
        $descriptionAttribute->isReadOnly()->willReturn(false);
        $objectFilter->filterObject($descriptionAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])
            ->willReturn(false);

        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $objectFilter->filterObject($enLocale, 'pim.internal_api.locale.edit', ['product' => $product])
            ->willReturn(true);

        $svLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('sv_SE')->willReturn($svLocale);
        $objectFilter->filterObject($svLocale, 'pim.internal_api.locale.edit', ['product' => $product])
            ->willReturn(false);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($mobileChannel);

        $data = [
            'name' => [
                [
                    'locale'       => 'en_US',
                    'scope'        => 'mobile',
                    'value'        => 'My awesome product',
                    'is_read_only' => false,
                ],
                [
                    'locale'       => 'sv_SE',
                    'scope'        => 'mobile',
                    'value'        => 'Min juste produkt',
                    'is_read_only' => false,
                ]
            ],
            'description' => [
                [
                    'locale'       => null,
                    'scope'        => 'mobile',
                    'value'        => 'This product is really awesome !',
                    'is_read_only' => false,
                ]
            ]
        ];

        $this->filterCollection($data, null, ['product' => $product])->shouldReturn([
            'name' => [
                [
                    'locale'       => 'sv_SE',
                    'scope'        => 'mobile',
                    'value'        => 'Min juste produkt',
                    'is_read_only' => false,
                ]
            ],
            'description' => [
                [
                    'locale'       => null,
                    'scope'        => 'mobile',
                    'value'        => 'This product is really awesome !',
                    'is_read_only' => false,
                ]
            ]
        ]);
    }

    function it_filters_when_values_data_contains_a_non_existant_attribute(
        $objectFilter,
        $attributeRepository,
        $localeRepository,
        $channelRepository,
        AttributeInterface $nameAttribute,
        LocaleInterface $enLocale,
        ChannelInterface $mobileChannel,
        ProductInterface $product
    ) {
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $nameAttribute->isLocaleSpecific()->willReturn(false);
        $nameAttribute->isLocalizable()->willReturn(true);
        $nameAttribute->isScopable()->willReturn(true);
        $nameAttribute->isReadOnly()->willReturn(false);
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])
            ->willReturn(false);

        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $objectFilter->filterObject($enLocale, 'pim.internal_api.locale.edit', ['product' => $product])
            ->willReturn(false);

        $attributeRepository->findOneByIdentifier('wrong')->willReturn(null);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($mobileChannel);

        $data = [
            'name' => [
                [
                    'locale'       => 'en_US',
                    'scope'        => 'mobile',
                    'value'        => 'My awesome product',
                    'is_read_only' => false,
                ]
            ],
            'wrong' => [
                [
                    'locale'       => 'en_US',
                    'scope'        => 'mobile',
                    'value'        => '',
                    'is_read_only' => false,
                ]
            ]
        ];

        $this->filterCollection($data, null, ['product' => $product])->shouldReturn(
            [
                'name' => [
                    [
                        'locale'       => 'en_US',
                        'scope'        => 'mobile',
                        'value'        => 'My awesome product',
                        'is_read_only' => false,
                    ]
                ]
            ]
        );
    }

    function it_filters_when_values_data_contains_a_non_existant_locale(
        $objectFilter,
        $attributeRepository,
        $localeRepository,
        $channelRepository,
        AttributeInterface $nameAttribute,
        AttributeInterface $descriptionAttribute,
        LocaleInterface $enLocale,
        ChannelInterface $mobileChannel,
        ProductInterface $product
    ) {
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $nameAttribute->isLocaleSpecific()->willReturn(false);
        $nameAttribute->isLocalizable()->willReturn(true);
        $nameAttribute->isScopable()->willReturn(true);
        $nameAttribute->isReadOnly()->willReturn(false);
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])
            ->willReturn(false);

        $attributeRepository->findOneByIdentifier('description')->willReturn($descriptionAttribute);
        $descriptionAttribute->isLocaleSpecific()->willReturn(false);
        $descriptionAttribute->isLocalizable()->willReturn(true);
        $descriptionAttribute->isScopable()->willReturn(true);
        $descriptionAttribute->isReadOnly()->willReturn(false);
        $objectFilter->filterObject($descriptionAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])
            ->willReturn(false);

        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $objectFilter->filterObject($enLocale, 'pim.internal_api.locale.edit', ['product' => $product])
            ->willReturn(false);

        $localeRepository->findOneByIdentifier('wrong')->willReturn(null);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($mobileChannel);

        $data = [
            'name' => [
                [
                    'locale'       => 'en_US',
                    'scope'        => 'mobile',
                    'value'        => 'My awesome product',
                    'is_read_only' => false,
                ]
            ],
            'description' => [
                [
                    'locale'       => 'wrong',
                    'scope'        => 'mobile',
                    'value'        => '',
                    'is_read_only' => false,
                ]
            ]
        ];

        $this->filterCollection($data, null, ['product' => $product])
            ->shouldReturn([
                'name' => [
                    [
                        'locale'       => 'en_US',
                        'scope'        => 'mobile',
                        'value'        => 'My awesome product',
                        'is_read_only' => false,
                    ]
                ]
            ]);
    }

    function it_filters_when_values_data_contains_an_inactive_locale(
        $objectFilter,
        $attributeRepository,
        $localeRepository,
        $channelRepository,
        AttributeInterface $nameAttribute,
        AttributeInterface $descriptionAttribute,
        LocaleInterface $enLocale,
        LocaleInterface $inactiveLocale,
        ChannelInterface $mobileChannel,
        ProductInterface $product
    ) {
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $nameAttribute->isLocaleSpecific()->willReturn(false);
        $nameAttribute->isLocalizable()->willReturn(true);
        $nameAttribute->isScopable()->willReturn(true);
        $nameAttribute->isReadOnly()->willReturn(false);
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])
            ->willReturn(false);

        $attributeRepository->findOneByIdentifier('description')->willReturn($descriptionAttribute);
        $descriptionAttribute->isLocaleSpecific()->willReturn(false);
        $descriptionAttribute->isLocalizable()->willReturn(true);
        $descriptionAttribute->isScopable()->willReturn(true);
        $descriptionAttribute->isReadOnly()->willReturn(false);
        $objectFilter->filterObject($descriptionAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])
            ->willReturn(false);

        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $objectFilter->filterObject($enLocale, 'pim.internal_api.locale.edit', ['product' => $product])
            ->willReturn(false);

        $inactiveLocale->isActivated()->willReturn(false);
        $localeRepository->findOneByIdentifier('inactive')->willReturn($inactiveLocale);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($mobileChannel);

        $data = [
            'name' => [
                [
                    'locale'       => 'en_US',
                    'scope'        => 'mobile',
                    'value'        => 'My awesome product',
                    'is_read_only' => false,
                ]
            ],
            'description' => [
                [
                    'locale'       => 'inactive',
                    'scope'        => 'mobile',
                    'value'        => '',
                    'is_read_only' => false,
                ]
            ]
        ];

        $this->filterCollection($data, null, ['product' => $product])
            ->shouldReturn([
                'name' => [
                    [
                        'locale'       => 'en_US',
                        'scope'        => 'mobile',
                        'value'        => 'My awesome product',
                        'is_read_only' => false,
                    ]
                ]
            ]);
    }
}
