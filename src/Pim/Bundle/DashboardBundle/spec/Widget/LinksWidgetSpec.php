<?php

namespace spec\Pim\Bundle\DashboardBundle\Widget;

use PhpSpec\ObjectBehavior;

class LinksWidgetSpec extends ObjectBehavior
{
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

    function it_exposes_the_links_template_parameters($repository)
    {
        $this->getParameters()->shouldReturn(
            [
                'links' => [
                    [
                        'route' => 'pim_enrich_product_index',
                        'label' => 'pim_dashboard.link.label.product',
                        'image' => 'widget_links_products.png',
                    ],
                    [
                        'route' => 'pim_enrich_family_index',
                        'label' => 'pim_dashboard.link.label.family',
                        'image' => 'widget_links_families.png',
                    ],
                    [
                        'route' => 'pim_enrich_attribute_index',
                        'label' => 'pim_dashboard.link.label.attribute',
                        'image' => 'widget_links_attributes.png',
                    ],
                    [
                        'route' => 'pim_enrich_categorytree_index',
                        'label' => 'pim_dashboard.link.label.category',
                        'image' => 'widget_links_categories.png',
                    ],
                ],
            ]
        );
    }
}
