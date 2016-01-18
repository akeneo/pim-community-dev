<?php

namespace spec\Pim\Bundle\EnrichBundle\Filter;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Prophecy\Argument;

class ProductEditDataFilterSpec extends ObjectBehavior
{
    function let(
        SecurityFacade $securityFacade,
        ObjectFilterInterface $objectFilter,
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository,
        ChannelRepositoryInterface $channelRepository
    ) {
        $this->beConstructedWith(
            $securityFacade,
            $objectFilter,
            $attributeRepository,
            $localeRepository,
            $channelRepository
        );
    }

    function it_filters_non_values_data_when_not_granted($securityFacade, ProductInterface $product)
    {
        $data = [
            'family'        => 'some family',
            'groups'        => [],
            'categories'    => ['lexmark'],
            'enabled'       => true,
            'associations'  => [],
            'values'        => []
        ];

        $securityFacade->isGranted(Argument::any())->willReturn(false);

        $this->filterCollection($data, null, ['product' => $product])->shouldReturn(['values' => []]);
    }

    function it_does_not_filters_non_values_data_when_granted($securityFacade, ProductInterface $product)
    {
        $data = [
            'family'        => 'some family',
            'groups'        => [],
            'categories'    => ['lexmark'],
            'enabled'       => true,
            'associations'  => [],
            'values'        => []
        ];

        $securityFacade->isGranted(Argument::any())->willReturn(true);

        $this->filterCollection($data, null, ['product' => $product])->shouldReturn($data);
    }

    function it_filters_values_data_on_attributes_group_rights(
        $objectFilter,
        $attributeRepository,
        $localeRepository,
        AttributeInterface $nameAttribute,
        AttributeInterface $descriptionAttribute,
        LocaleInterface $enLocale,
        ProductInterface $product
    ) {
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit')->willReturn(true);

        $attributeRepository->findOneByIdentifier('description')->willReturn($descriptionAttribute);
        $objectFilter->filterObject($descriptionAttribute, 'pim.internal_api.attribute.edit')->willReturn(false);

        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $objectFilter->filterObject($enLocale, 'pim.internal_api.locale.edit')->willReturn(false);

        $data = [
            'values' => [
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
            ]
        ];

        $this->filterCollection($data, null, ['product' => $product])->shouldReturn([
            'values' => [
                'description' => [
                    [
                        'locale' => 'en_US',
                        'scope'  => 'mobile',
                        'value'  => 'This product is really awesome !'
                    ]
                ]
            ]
        ]);
    }

    function it_filters_values_data_on_locale_rights(
        $objectFilter,
        $attributeRepository,
        $localeRepository,
        AttributeInterface $nameAttribute,
        AttributeInterface $descriptionAttribute,
        LocaleInterface $enLocale,
        LocaleInterface $svLocale,
        ProductInterface $product
    ) {
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit')->willReturn(false);

        $attributeRepository->findOneByIdentifier('description')->willReturn($descriptionAttribute);
        $objectFilter->filterObject($descriptionAttribute, 'pim.internal_api.attribute.edit')->willReturn(false);

        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $objectFilter->filterObject($enLocale, 'pim.internal_api.locale.edit')->willReturn(true);

        $svLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('sv_SE')->willReturn($svLocale);
        $objectFilter->filterObject($svLocale, 'pim.internal_api.locale.edit')->willReturn(false);

        $data = [
            'values' => [
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
            ]
        ];

        $this->filterCollection($data, null, ['product' => $product])->shouldReturn([
            'values' => [
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
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit')->willReturn(false);

        $nameAttribute->isScopable()->willReturn(true);
        $channelRepository->findOneByIdentifier('print')->willReturn(null);

        $data = [
            'values' => [
                'name' => [
                    [
                        'locale' => null,
                        'scope'  => 'print',
                        'value'  => 'My awesome product'
                    ],
                ],
            ],
        ];

        $this->filterCollection($data, null, ['product' => $product])->shouldReturn(['values' => []]);
    }

    function it_does_not_filter_non_localizable_attributes(
        $objectFilter,
        $attributeRepository,
        $localeRepository,
        AttributeInterface $nameAttribute,
        AttributeInterface $descriptionAttribute,
        LocaleInterface $enLocale,
        LocaleInterface $svLocale,
        ProductInterface $product
    ) {
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit')->willReturn(false);

        $attributeRepository->findOneByIdentifier('description')->willReturn($descriptionAttribute);
        $objectFilter->filterObject($descriptionAttribute, 'pim.internal_api.attribute.edit')->willReturn(false);

        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $objectFilter->filterObject($enLocale, 'pim.internal_api.locale.edit')->willReturn(true);

        $svLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('sv_SE')->willReturn($svLocale);
        $objectFilter->filterObject($svLocale, 'pim.internal_api.locale.edit')->willReturn(false);

        $data = [
            'values' => [
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
            ]
        ];

        $this->filterCollection($data, null, ['product' => $product])->shouldReturn([
            'values' => [
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
            ]
        ]);
    }

    function it_throws_an_exception_when_values_data_contains_a_non_existant_attribute(
        $objectFilter,
        $attributeRepository,
        $localeRepository,
        AttributeInterface $nameAttribute,
        LocaleInterface $enLocale,
        ProductInterface $product
    ) {
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit')->willReturn(false);

        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $objectFilter->filterObject($enLocale, 'pim.internal_api.locale.edit')->willReturn(false);

        $attributeRepository->findOneByIdentifier('wrong')->willReturn(null);

        $data = [
            'values' => [
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
            ]
        ];

        $this->shouldThrow('\Pim\Bundle\CatalogBundle\Exception\ObjectNotFoundException')
             ->during('filterCollection', [$data, null, ['product' => $product]]);
    }

    function it_throws_an_exception_when_values_data_contains_a_non_existant_locale(
        $objectFilter,
        $attributeRepository,
        $localeRepository,
        AttributeInterface $nameAttribute,
        AttributeInterface $descriptionAttribute,
        LocaleInterface $enLocale,
        ProductInterface $product
    ) {
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit')->willReturn(false);

        $attributeRepository->findOneByIdentifier('description')->willReturn($descriptionAttribute);
        $objectFilter->filterObject($descriptionAttribute, 'pim.internal_api.attribute.edit')->willReturn(false);

        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $objectFilter->filterObject($enLocale, 'pim.internal_api.locale.edit')->willReturn(true);

        $localeRepository->findOneByIdentifier('wrong')->willReturn(null);

        $data = [
            'values' => [
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
            ]
        ];

        $this->shouldThrow('\Pim\Bundle\CatalogBundle\Exception\ObjectNotFoundException')
             ->during('filterCollection', [$data, null, ['product' => $product]]);
    }

    function it_throws_an_exception_when_values_data_contains_an_inactive_locale(
        $objectFilter,
        $attributeRepository,
        $localeRepository,
        AttributeInterface $nameAttribute,
        AttributeInterface $descriptionAttribute,
        LocaleInterface $enLocale,
        LocaleInterface $inactiveLocale,
        ProductInterface $product
    ) {
        $attributeRepository->findOneByIdentifier('name')->willReturn($nameAttribute);
        $objectFilter->filterObject($nameAttribute, 'pim.internal_api.attribute.edit')->willReturn(false);

        $attributeRepository->findOneByIdentifier('description')->willReturn($descriptionAttribute);
        $objectFilter->filterObject($descriptionAttribute, 'pim.internal_api.attribute.edit')->willReturn(false);

        $enLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier('en_US')->willReturn($enLocale);
        $objectFilter->filterObject($enLocale, 'pim.internal_api.locale.edit')->willReturn(true);

        $inactiveLocale->isActivated()->willReturn(false);
        $localeRepository->findOneByIdentifier('inactive')->willReturn($inactiveLocale);

        $data = [
            'values' => [
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
            ]
        ];

        $this->shouldThrow('\Pim\Bundle\CatalogBundle\Exception\ObjectNotFoundException')
            ->during('filterCollection', [$data, null, ['product' => $product]]);
    }
}
