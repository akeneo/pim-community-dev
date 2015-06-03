<?php

namespace spec\Pim\Bundle\EnrichBundle\Filter;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Prophecy\Argument;

class ProductEditDataFilterSpec extends ObjectBehavior
{
    function let(
        SecurityFacade $securityFacade,
        ObjectFilterInterface $objectFilter,
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository,
        AttributeInterface $nameAttribute,
        AttributeInterface $descriptionAttribute,
        LocaleInterface $enLocale,
        LocaleInterface $svLocale
    ) {
        $attributeRepository->findOneByIdentifier(Argument::is('name'))->willReturn($nameAttribute);
        $attributeRepository->findOneByIdentifier(Argument::is('description'))->willReturn($descriptionAttribute);

        $enLocale->isActivated()->willReturn(true);
        $svLocale->isActivated()->willReturn(true);
        $localeRepository->findOneByIdentifier(Argument::is('en_US'))->willReturn($enLocale);
        $localeRepository->findOneByIdentifier(Argument::is('sv_SE'))->willReturn($svLocale);

        $this->beConstructedWith(
            $securityFacade,
            $objectFilter,
            $attributeRepository,
            $localeRepository,
            $nameAttribute,
            $descriptionAttribute,
            $enLocale,
            $svLocale
        );
    }

    function it_filters_non_values_data_when_not_granted($securityFacade)
    {
        $data = [
            'family'        => 'some family',
            'groups'        => [],
            'categories'    => ['lexmark'],
            'enabled'       => true,
            'associations'  => [],
            'values'        => []
        ];

        $securityFacade->isGranted(Argument::any())
             ->shouldBeCalled()
             ->willReturn(false);

        $this->filterCollection($data, null)->shouldReturn(['values' => []]);
    }

    function it_does_not_filters_non_values_data_when_granted($securityFacade)
    {
        $data = [
            'family'        => 'some family',
            'groups'        => [],
            'categories'    => ['lexmark'],
            'enabled'       => true,
            'associations'  => [],
            'values'        => []
        ];

        $securityFacade->isGranted(Argument::any())
             ->shouldBeCalled()
             ->willReturn(true);

        $this->filterCollection($data, null)->shouldReturn($data);
    }

    function it_filters_values_data_on_attributes_group_rights(
        $objectFilter,
        $nameAttribute,
        $descriptionAttribute,
        $enLocale
    ) {
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

        $objectFilter->filterObject(
            Argument::is($nameAttribute->getWrappedObject()),
            Argument::is('pim:internal_api:attribute:edit')
        )
            ->shouldBeCalled()
            ->willReturn(true);

        $objectFilter->filterObject(
            Argument::is($descriptionAttribute->getWrappedObject()),
            Argument::is('pim:internal_api:attribute:edit')
        )
            ->shouldBeCalled()
            ->willReturn(false);

        $objectFilter->filterObject(
            Argument::is($enLocale->getWrappedObject()),
            Argument::is('pim:internal_api:locale:edit')
        )
            ->shouldBeCalled()
            ->willReturn(false);

        $this->filterCollection($data, null)->shouldReturn([
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
        $nameAttribute,
        $descriptionAttribute,
        $enLocale,
        $svLocale
    ) {
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

        $objectFilter->filterObject(
            Argument::is($nameAttribute->getWrappedObject()),
            Argument::is('pim:internal_api:attribute:edit')
        )
            ->shouldBeCalled()
            ->willReturn(false);

        $objectFilter->filterObject(
            Argument::is($descriptionAttribute->getWrappedObject()),
            Argument::is('pim:internal_api:attribute:edit')
        )
            ->shouldBeCalled()
            ->willReturn(false);

        $objectFilter->filterObject(
            Argument::is($enLocale->getWrappedObject()),
            Argument::is('pim:internal_api:locale:edit')
        )
            ->shouldBeCalled()
            ->willReturn(true);

        $objectFilter->filterObject(
            Argument::is($svLocale->getWrappedObject()),
            Argument::is('pim:internal_api:locale:edit')
        )
            ->shouldBeCalled()
            ->willReturn(false);

        $this->filterCollection($data, null)->shouldReturn([
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

    function it_does_not_filter_non_localizable_attributes(
        $objectFilter,
        $nameAttribute,
        $descriptionAttribute,
        $enLocale,
        $svLocale
    ) {
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

        $objectFilter->filterObject(
            Argument::is($nameAttribute->getWrappedObject()),
            Argument::is('pim:internal_api:attribute:edit')
        )
            ->shouldBeCalled()
            ->willReturn(false);

        $objectFilter->filterObject(
            Argument::is($descriptionAttribute->getWrappedObject()),
            Argument::is('pim:internal_api:attribute:edit')
        )
            ->shouldBeCalled()
            ->willReturn(false);

        $objectFilter->filterObject(
            Argument::is($enLocale->getWrappedObject()),
            Argument::is('pim:internal_api:locale:edit')
        )
            ->shouldBeCalled()
            ->willReturn(true);

        $objectFilter->filterObject(
            Argument::is($svLocale->getWrappedObject()),
            Argument::is('pim:internal_api:locale:edit')
        )
            ->shouldBeCalled()
            ->willReturn(false);

        $this->filterCollection($data, null)->shouldReturn([
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
        $nameAttribute,
        $enLocale,
        $attributeRepository
    ) {
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

        $objectFilter->filterObject(
            Argument::is($nameAttribute->getWrappedObject()),
            Argument::is('pim:internal_api:attribute:edit')
        )
            ->shouldBeCalled()
            ->willReturn(false);

        $objectFilter->filterObject(
            Argument::is($enLocale->getWrappedObject()),
            Argument::is('pim:internal_api:locale:edit')
        )
            ->shouldBeCalled()
            ->willReturn(false);

        $attributeRepository->findOneByIdentifier(Argument::is('wrong'))->willReturn(null);

        $this->shouldThrow('\Pim\Bundle\CatalogBundle\Exception\ObjectNotFoundException')
             ->during('filterCollection', [$data, null]);
    }

    function it_throws_an_exception_when_values_data_contains_a_non_existant_locale(
        $objectFilter,
        $nameAttribute,
        $descriptionAttribute,
        $enLocale,
        $localeRepository
    ) {
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

        $objectFilter->filterObject(
            Argument::is($nameAttribute->getWrappedObject()),
            Argument::is('pim:internal_api:attribute:edit')
        )
            ->shouldBeCalled()
            ->willReturn(false);

        $objectFilter->filterObject(
            Argument::is($descriptionAttribute->getWrappedObject()),
            Argument::is('pim:internal_api:attribute:edit')
        )
            ->shouldBeCalled()
            ->willReturn(false);

        $objectFilter->filterObject(
            Argument::is($enLocale->getWrappedObject()),
            Argument::is('pim:internal_api:locale:edit')
        )
            ->shouldBeCalled()
            ->willReturn(true);

        $localeRepository->findOneByIdentifier(Argument::is('wrong'))->willReturn(null);

        $this->shouldThrow('\Pim\Bundle\CatalogBundle\Exception\ObjectNotFoundException')
             ->during('filterCollection', [$data, null]);
    }

    function it_throws_an_exception_when_values_data_contains_an_inactive_locale(
        $objectFilter,
        $nameAttribute,
        $descriptionAttribute,
        $enLocale,
        $localeRepository,
        LocaleInterface $inactiveLocale
    ) {
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

        $objectFilter->filterObject(
            Argument::is($nameAttribute->getWrappedObject()),
            Argument::is('pim:internal_api:attribute:edit')
        )
            ->shouldBeCalled()
            ->willReturn(false);

        $objectFilter->filterObject(
            Argument::is($descriptionAttribute->getWrappedObject()),
            Argument::is('pim:internal_api:attribute:edit')
        )
            ->shouldBeCalled()
            ->willReturn(false);

        $objectFilter->filterObject(
            Argument::is($enLocale->getWrappedObject()),
            Argument::is('pim:internal_api:locale:edit')
        )
            ->shouldBeCalled()
            ->willReturn(true);

        $inactiveLocale->isActivated()->willReturn(false);
        $localeRepository->findOneByIdentifier(Argument::is('inactive'))->willReturn($inactiveLocale);

        $this->shouldThrow('\Pim\Bundle\CatalogBundle\Exception\ObjectNotFoundException')
            ->during('filterCollection', [$data, null]);
    }
}
