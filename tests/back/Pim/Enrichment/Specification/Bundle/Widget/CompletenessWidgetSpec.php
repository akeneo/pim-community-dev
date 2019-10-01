<?php

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Widget;

use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\FollowUp\GetCompletenessPerChannelAndLocale;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\ChannelCompleteness;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\CompletenessWidget;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\LocaleCompleteness;
use Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Bundle\Context\UserContext;

class CompletenessWidgetSpec extends ObjectBehavior
{
    function let(UserContext $userContext, GetCompletenessPerChannelAndLocale $completenessWidgetQuery)
    {
        $this->beConstructedWith($userContext, $completenessWidgetQuery);
    }

    function it_is_a_widget()
    {
        $this->shouldImplement(WidgetInterface::class);
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

    function it_exposes_the_completeness_data($completenessWidgetQuery, $userContext)
    {
        $userContext->getUiLocaleCode()->willReturn('en_US');
        $mobileCompleteness = new ChannelCompleteness('Mobile', 10, 40, [
            'English (United States)' => new LocaleCompleteness('English (United States)', 10),
            'French (France)' => new LocaleCompleteness('French (France)', 0)
        ]);
        $ecommerceCompleteness = new ChannelCompleteness('E-Commerce', 25, 30, [
            'English (United States)' => new LocaleCompleteness('English (United States)', 25),
            'French (France)' => new LocaleCompleteness('French (France)', 5)
        ]);
        $completenessWidget = new CompletenessWidget([$mobileCompleteness, $ecommerceCompleteness]);

        $completenessWidgetQuery->fetch('en_US')->willReturn($completenessWidget);

        $this->getData()->shouldReturn(
            [
                'Mobile' => [
                    'labels'   => [],
                    'total'    => 40,
                    'complete' => 10,
                    'locales'  => [
                        'English (United States)' => 10,
                        'French (France)'  => 0,
                    ],
                ],
                'E-Commerce' => [
                    'labels'   => [],
                    'total' => 30,
                    'complete' => 25,
                    'locales' => [
                        'English (United States)' => 25,
                        'French (France)'  => 5,
                    ],
                ]
            ]
        );
    }
}
