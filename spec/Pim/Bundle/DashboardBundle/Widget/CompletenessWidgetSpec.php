<?php

namespace spec\Pim\Bundle\DashboardBundle\Widget;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Helper\LocaleHelper;
use Pim\Bundle\CatalogBundle\Repository\CompletenessRepositoryInterface;

class CompletenessWidgetSpec extends ObjectBehavior
{
    function let(CompletenessRepositoryInterface $completenessRepo, LocaleHelper $localeHelper)
    {
        $this->beConstructedWith($completenessRepo, $localeHelper);
    }

    function it_is_a_widget()
    {
        $this->shouldImplement('Pim\Bundle\DashboardBundle\Widget\WidgetInterface');
    }

    function it_has_an_alias()
    {
        $this->getAlias()->shouldReturn('completeness');
    }

    function it_exposes_the_completeness_widget_template()
    {
        $this->getTemplate()->shouldReturn('PimDashboardBundle:Widget:completeness.html.twig');
    }

    function it_has_no_template_parameters()
    {
        $this->getParameters()->shouldReturn([]);
    }

    function it_exposes_the_completeness_data($completenessRepo, $localeHelper)
    {
        $completenessRepo->getProductsCountPerChannels()->willReturn(
            [
                [
                    'label' => 'Mobile',
                    'total' => 40,
                ],
                [
                    'label' => 'E-Commerce',
                    'total' => 25,
                ],
            ]
        );
        $completenessRepo->getCompleteProductsCountPerChannels()->willReturn(
            [
                [
                    'label'  => 'Mobile',
                    'locale' => 'en_US',
                    'total'  => 10,
                ],
                [
                    'label'  => 'Mobile',
                    'locale' => 'fr_FR',
                    'total'  => 0,
                ],
                [
                    'label'  => 'E-Commerce',
                    'locale' => 'en_US',
                    'total'  => 25,
                ],
                [
                    'label'  => 'E-Commerce',
                    'locale' => 'fr_FR',
                    'total'  => 5,
                ],
            ]
        );
        $localeHelper->getLocaleLabel('en_US')->willReturn('English');
        $localeHelper->getLocaleLabel('fr_FR')->willReturn('French');

        $this->getData()->shouldReturn(
            [
                'Mobile' => [
                    'total'    => 40,
                    'complete' => 10,
                    'locales'  => [
                        'English' => 10,
                        'French'  => 0,
                    ],
                ],
                'E-Commerce' => [
                    'total' => 25,
                    'complete' => 30,
                    'locales' => [
                        'English' => 25,
                        'French'  => 5,
                    ]
                ]
            ]
        );
    }
}
