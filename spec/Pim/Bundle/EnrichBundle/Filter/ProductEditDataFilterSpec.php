<?php

namespace spec\Pim\Bundle\EnrichBundle\Filter;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Filter\ObjectFilterInterface;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Prophecy\Argument;

class ProductEditDataFilterSpec extends ObjectBehavior
{
    function let(
        SecurityFacade $securityFacade,
        ObjectFilterInterface $objectFilter,
        AttributeRepositoryInterface $attributeRepository,
        LocaleManager $localeManager
    ) {
        $this->beConstructedWith($securityFacade, $objectFilter, $attributeRepository, $localeManager);

        $attributeRepository->getAttributesAsArray()->willReturn([
            'name'        => 'fake_name_attribute',
            'description' => 'fake_description_attribute'
        ]);

        $localeManager->getActiveLocales()->willReturn([
            'en_US' => 'fake_en_us_locale',
            'sv_SE' => 'fake_sv_se_locale'
        ]);
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

    function it_filters_values_data_on_attributes_group_rights($objectFilter)
    {
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
            Argument::is('fake_name_attribute'),
            Argument::is('pim:internal_api:attribute:edit')
        )
            ->shouldBeCalled()
            ->willReturn(true);

        $objectFilter->filterObject(
            Argument::is('fake_description_attribute'),
            Argument::is('pim:internal_api:attribute:edit')
        )
            ->shouldBeCalled()
            ->willReturn(false);

        $objectFilter->filterObject(
            Argument::is('fake_en_us_locale'),
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

    function it_filters_values_data_on_locale_rights($objectFilter)
    {
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
            Argument::is('fake_name_attribute'),
            Argument::is('pim:internal_api:attribute:edit')
        )
            ->shouldBeCalled()
            ->willReturn(false);

        $objectFilter->filterObject(
            Argument::is('fake_description_attribute'),
            Argument::is('pim:internal_api:attribute:edit')
        )
            ->shouldBeCalled()
            ->willReturn(false);

        $objectFilter->filterObject(
            Argument::is('fake_en_us_locale'),
            Argument::is('pim:internal_api:locale:edit')
        )
            ->shouldBeCalled()
            ->willReturn(true);

        $objectFilter->filterObject(
            Argument::is('fake_sv_se_locale'),
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

    function it_throws_an_exception_when_values_data_contains_a_non_existant_attribute()
    {
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
             ->during('filterCollection', [$data, null]);
    }

    function it_throws_an_exception_when_values_data_contains_a_non_existant_locale()
    {
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
             ->during('filterCollection', [$data, null]);
    }
}
