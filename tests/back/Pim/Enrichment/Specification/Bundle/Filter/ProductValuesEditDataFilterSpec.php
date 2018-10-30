<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Filter;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;

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
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])->willReturn(true);

        $attributeRepository->findOneByIdentifier('description')->willReturn($descriptionAttribute);
        $descriptionAttribute->isLocaleSpecific()->willReturn(false);
        $descriptionAttribute->isLocalizable()->willReturn(true);
        $descriptionAttribute->isScopable()->willReturn(true);
        $objectFilter->filterObject($descriptionAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])->willReturn(false);

        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $objectFilter->filterObject($enLocale, 'pim.internal_api.locale.edit', ['product' => $product])->willReturn(false);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($mobileChannel);

        $data = [
            'name' => [
                [
                    'locale' => 'en_US',
                    'scope'  => 'mobile',
                    'value'  => 'My awesome product'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope'  => 'mobile',
                    'value'  => 'This product is really awesome !'
                ]
            ]
        ];

        $this->filterCollection($data, null, ['product' => $product])->shouldReturn([
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope'  => 'mobile',
                    'value'  => 'This product is really awesome !'
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
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])->willReturn(false);

        $attributeRepository->findOneByIdentifier('description')->willReturn($descriptionAttribute);
        $descriptionAttribute->isLocaleSpecific()->willReturn(false);
        $descriptionAttribute->isLocalizable()->willReturn(true);
        $descriptionAttribute->isScopable()->willReturn(true);
        $objectFilter->filterObject($descriptionAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])->willReturn(false);

        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $objectFilter->filterObject($enLocale, 'pim.internal_api.locale.edit', ['product' => $product])->willReturn(true);

        $svLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('sv_SE')->willReturn($svLocale);
        $objectFilter->filterObject($svLocale, 'pim.internal_api.locale.edit', ['product' => $product])->willReturn(false);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($mobileChannel);

        $data = [
            'name' => [
                [
                    'locale' => 'en_US',
                    'scope'  => 'mobile',
                    'value'  => 'My awesome product'
                ],
                [
                    'locale' => 'sv_SE',
                    'scope'  => 'mobile',
                    'value'  => 'Min juste produkt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'en_US',
                    'scope'  => 'mobile',
                    'value'  => 'This product is really awesome !'
                ],
                [
                    'locale' => 'sv_SE',
                    'scope'  => 'mobile',
                    'value'  => 'Denna produkt är verkligen häftigt !'
                ]
            ]
        ];

        $this->filterCollection($data, null, ['product' => $product])->shouldReturn([
            'name' => [
                [
                    'locale' => 'sv_SE',
                    'scope'  => 'mobile',
                    'value'  => 'Min juste produkt'
                ]
            ],
            'description' => [
                [
                    'locale' => 'sv_SE',
                    'scope'  => 'mobile',
                    'value'  => 'Denna produkt är verkligen häftigt !'
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
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])->willReturn(false);

        $nameAttribute->isScopable()->willReturn(true);
        $channelRepository->findOneByIdentifier('print')->willReturn(null);

        $data = [
            'name' => [
                [
                    'locale' => null,
                    'scope'  => 'print',
                    'value'  => 'My awesome product'
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
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])->willReturn(false);

        $attributeRepository->findOneByIdentifier('description')->willReturn($descriptionAttribute);
        $descriptionAttribute->isLocaleSpecific()->willReturn(false);
        $descriptionAttribute->isLocalizable()->willReturn(false);
        $descriptionAttribute->isScopable()->willReturn(true);
        $objectFilter->filterObject($descriptionAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])->willReturn(false);

        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $objectFilter->filterObject($enLocale, 'pim.internal_api.locale.edit', ['product' => $product])->willReturn(true);

        $svLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('sv_SE')->willReturn($svLocale);
        $objectFilter->filterObject($svLocale, 'pim.internal_api.locale.edit', ['product' => $product])->willReturn(false);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($mobileChannel);

        $data = [
            'name' => [
                [
                    'locale' => 'en_US',
                    'scope'  => 'mobile',
                    'value'  => 'My awesome product'
                ],
                [
                    'locale' => 'sv_SE',
                    'scope'  => 'mobile',
                    'value'  => 'Min juste produkt'
                ]
            ],
            'description' => [
                [
                    'locale' => null,
                    'scope'  => 'mobile',
                    'value'  => 'This product is really awesome !'
                ]
            ]
        ];

        $this->filterCollection($data, null, ['product' => $product])->shouldReturn([
            'name' => [
                [
                    'locale' => 'sv_SE',
                    'scope'  => 'mobile',
                    'value'  => 'Min juste produkt'
                ]
            ],
            'description' => [
                [
                    'locale' => null,
                    'scope'  => 'mobile',
                    'value'  => 'This product is really awesome !'
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
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])->willReturn(false);

        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $objectFilter->filterObject($enLocale, 'pim.internal_api.locale.edit', ['product' => $product])->willReturn(false);

        $attributeRepository->findOneByIdentifier('wrong')->willReturn(null);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($mobileChannel);

        $data = [
            'name' => [
                [
                    'locale' => 'en_US',
                    'scope'  => 'mobile',
                    'value'  => 'My awesome product'
                ]
            ],
            'wrong' => [
                [
                    'locale' => 'en_US',
                    'scope'  => 'mobile',
                    'value'  => ''
                ]
            ]
        ];

        $this->filterCollection($data, null, ['product' => $product])->shouldReturn(
            [
                'name' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => 'mobile',
                        'value'  => 'My awesome product'
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
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])->willReturn(false);

        $attributeRepository->findOneByIdentifier('description')->willReturn($descriptionAttribute);
        $descriptionAttribute->isLocaleSpecific()->willReturn(false);
        $descriptionAttribute->isLocalizable()->willReturn(true);
        $descriptionAttribute->isScopable()->willReturn(true);
        $objectFilter->filterObject($descriptionAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])->willReturn(false);

        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $objectFilter->filterObject($enLocale, 'pim.internal_api.locale.edit', ['product' => $product])->willReturn(false);

        $localeRepository->findOneByIdentifier('wrong')->willReturn(null);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($mobileChannel);

        $data = [
            'name' => [
                [
                    'locale' => 'en_US',
                    'scope'  => 'mobile',
                    'value'  => 'My awesome product'
                ]
            ],
            'description' => [
                [
                    'locale' => 'wrong',
                    'scope'  => 'mobile',
                    'value'  => ''
                ]
            ]
        ];

        $this->filterCollection($data, null, ['product' => $product])
            ->shouldReturn([
                'name' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => 'mobile',
                        'value'  => 'My awesome product'
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
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])->willReturn(false);

        $attributeRepository->findOneByIdentifier('description')->willReturn($descriptionAttribute);
        $descriptionAttribute->isLocaleSpecific()->willReturn(false);
        $descriptionAttribute->isLocalizable()->willReturn(true);
        $descriptionAttribute->isScopable()->willReturn(true);
        $objectFilter->filterObject($descriptionAttribute, 'pim.internal_api.attribute.edit', ['product' => $product])->willReturn(false);

        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $objectFilter->filterObject($enLocale, 'pim.internal_api.locale.edit', ['product' => $product])->willReturn(false);

        $inactiveLocale->isActivated()->willReturn(false);
        $localeRepository->findOneByIdentifier('inactive')->willReturn($inactiveLocale);

        $channelRepository->findOneByIdentifier('mobile')->willReturn($mobileChannel);

        $data = [
            'name' => [
                [
                    'locale' => 'en_US',
                    'scope'  => 'mobile',
                    'value'  => 'My awesome product'
                ]
            ],
            'description' => [
                [
                    'locale' => 'inactive',
                    'scope'  => 'mobile',
                    'value'  => ''
                ]
            ]
        ];

        $this->filterCollection($data, null, ['product' => $product])
            ->shouldReturn([
                'name' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => 'mobile',
                        'value'  => 'My awesome product'
                    ]
                ]
            ]);
    }
}
