<?php

namespace spec\Akeneo\Pim\Enrichment\Bundle\Widget;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\Pim\Enrichment\Component\Product\Repository\CompletenessRepositoryInterface;
use Prophecy\Argument;

class CompletenessWidgetSpec extends ObjectBehavior
{
    function let(CompletenessRepositoryInterface $completenessRepo, UserContext $userContext, ObjectFilterInterface $objectFilter, IdentifiableObjectRepositoryInterface $localeRepository)
    {
        $this->beConstructedWith($completenessRepo, $userContext, $objectFilter, $localeRepository);
    }

    function it_is_a_widget()
    {
        $this->shouldImplement('Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface');
    }

    function it_has_an_alias()
    {
        $this->getAlias()->shouldReturn('completeness');
    }

    function it_exposes_the_completeness_widget_template()
    {
        $this->getTemplate()->shouldReturn('AkeneoPimEnrichmentBundle:Widget:completeness.html.twig');
    }

    function it_has_no_template_parameters()
    {
        $this->getParameters()->shouldReturn([]);
    }

    function it_exposes_the_completeness_data($completenessRepo, $userContext)
    {
        $completenessRepo->getProductsCountPerChannels(Argument::any())->willReturn(
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
        $completenessRepo->getCompleteProductsCountPerChannels(Argument::any())->willReturn(
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
        $userContext->getCurrentLocaleCode()->willReturn('en_US');

        $this->getData()->shouldReturn(
            [
                'Mobile' => [
                    'total'    => 40,
                    'complete' => 10,
                    'locales'  => [
                        'English (United States)' => 10,
                        'French (France)'  => 0,
                    ],
                ],
                'E-Commerce' => [
                    'total' => 25,
                    'complete' => 30,
                    'locales' => [
                        'English (United States)' => 25,
                        'French (France)'  => 5,
                    ]
                ]
            ]
        );
    }
}
