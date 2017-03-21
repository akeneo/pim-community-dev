<?php

namespace spec\Pim\Bundle\DashboardBundle\Widget;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use PhpSpec\ObjectBehavior;

class LinksWidgetSpec extends ObjectBehavior
{
    function let(
        SecurityFacade $securityFacade
    ) {
        $this->beConstructedWith(
            $securityFacade,
            [
                [
                    'acl'   => 'pim_enrich_product_index',
                    'route' => 'pim_enrich_product_index',
                    'label' => 'pim_dashboard.link.label.product',
                    'image' => 'widget_links_products.png'
                ],
                [
                    'acl' => 'pim_enrich_family_index',
                    'route' => 'pim_enrich_family_index',
                    'label' => 'pim_dashboard.link.label.family',
                    'image' => 'widget_links_families.png',
                ]
            ]
        );
    }

    function it_is_a_widget()
    {
        $this->shouldImplement('Pim\Bundle\DashboardBundle\Widget\WidgetInterface');
    }

    function it_has_an_alias()
    {
        $this->getAlias()->shouldReturn('links');
    }

    function it_exposes_the_links_widget_template()
    {
        $this->getTemplate()->shouldReturn('PimDashboardBundle:Widget:links.html.twig');
    }

    function it_exposes_the_links_template_parameters($repository, SecurityFacade $securityFacade)
    {
        $securityFacade->isGranted('pim_enrich_product_index')->willReturn(true);
        $securityFacade->isGranted('pim_enrich_family_index')->willReturn(false);
        $this->getParameters()->shouldReturn(
            [
                'links' => [
                    [
                        'acl' => 'pim_enrich_product_index',
                        'route' => 'pim_enrich_product_index',
                        'label' => 'pim_dashboard.link.label.product',
                        'image' => 'widget_links_products.png',
                    ]
                ],
            ]
        );
    }
}
