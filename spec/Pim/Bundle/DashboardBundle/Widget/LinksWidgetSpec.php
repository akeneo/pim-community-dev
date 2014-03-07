<?php

namespace spec\Pim\Bundle\DashboardBundle\Widget;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class LinksWidgetSpec extends ObjectBehavior
{
    function it_is_a_widget()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DashboardBundle\Widget\WidgetInterface');
    }

    function it_exposes_the_links_widget_template()
    {
        $this->getTemplate()->shouldReturn('PimDashboardBundle:Widget:links.html.twig');
    }

    function it_exposes_the_links_template_parameters($repository)
    {
        $this->getParameters()->shouldReturn(
            array(
                'links' => array(
                    array(
                        'route' => 'pim_enrich_product_index',
                        'label' => 'pim_dashboard.link.label.product',
                        'icon' => 'barcode',
                    ),
                    array(
                        'route' => 'pim_enrich_family_create',
                        'label' => 'pim_dashboard.link.label.family',
                        'icon' => 'folder-open-alt',
                    ),
                    array(
                        'route' => 'pim_enrich_attribute_index',
                        'label' => 'pim_dashboard.link.label.attribute',
                        'icon' => 'list-ul',
                    ),
                    array(
                        'route' => 'pim_enrich_categorytree_create',
                        'label' => 'pim_dashboard.link.label.category',
                        'icon' => 'sitemap',
                    ),
                ),
            )
        );
    }
}
