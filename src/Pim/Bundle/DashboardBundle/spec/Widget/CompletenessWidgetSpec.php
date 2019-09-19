<?php

namespace spec\Pim\Bundle\DashboardBundle\Widget;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\EnrichBundle\Storage\ElasticsearchAndSql\FollowUp\GetCompletenessPerChannelAndLocale;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Enrich\FollowUp\ReadModel\ChannelCompleteness;
use Pim\Component\Enrich\FollowUp\ReadModel\CompletenessWidget;
use Pim\Component\Enrich\FollowUp\ReadModel\LocaleCompleteness;

class CompletenessWidgetSpec extends ObjectBehavior
{
    function let(UserContext $userContext, GetCompletenessPerChannelAndLocale $getCompletenessPerChannelAndLocale)
    {
        $this->beConstructedWith($userContext, $getCompletenessPerChannelAndLocale);
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

    function it_exposes_the_completeness_data(
        UserContext $userContext,
        GetCompletenessPerChannelAndLocale $getCompletenessPerChannelAndLocale
    ) {
        $userContext->getCurrentLocaleCode()->willReturn('en_US');
        $mobileCompleteness = new ChannelCompleteness('Mobile', 10, 40, [
            'English (United States)' => new LocaleCompleteness('English (United States)', 10),
            'French (France)' => new LocaleCompleteness('French (France)', 0)
        ]);
        $ecommerceCompleteness = new ChannelCompleteness('E-Commerce', 25, 30, [
            'English (United States)' => new LocaleCompleteness('English (United States)', 25),
            'French (France)' => new LocaleCompleteness('French (France)', 5)
        ]);
        $completenessWidget = new CompletenessWidget([$mobileCompleteness, $ecommerceCompleteness]);
        $getCompletenessPerChannelAndLocale->fetch('en_US')->willReturn($completenessWidget);

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
                    'total' => 30,
                    'complete' => 25,
                    'locales' => [
                        'English (United States)' => 25,
                        'French (France)'  => 5,
                    ]
                ]
            ]
        );
    }
}
