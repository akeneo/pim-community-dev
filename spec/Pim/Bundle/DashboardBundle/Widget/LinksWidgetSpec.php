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
                'image' => 'enrich-image',
                'label' => 'pim_dashboard.menu.label.enrich',
                'links' => array(
                    array(
                        'route' => 'pim_catalog_product_index',
                        'label' => 'pim_dashboard.link.label.product',
                    ),
                    array(
                        'route' => 'pim_catalog_categorytree_create',
                        'label' => 'pim_dashboard.link.label.category',
                    ),
                    array(
                        'route' => 'pim_catalog_variant_group_index',
                        'label' => 'pim_dashboard.link.label.variant',
                    ),
                    array(
                        'route' => 'pim_catalog_group_index',
                        'label' => 'pim_dashboard.link.label.group',
                    ),
                    array(
                        'route' => 'pim_catalog_attribute_index',
                        'label' => 'pim_dashboard.link.label.attribute',
                    ),
                    array(
                        'route' => 'pim_catalog_family_create',
                        'label' => 'pim_dashboard.link.label.family',
                    )
                ),
            )
        );
    }
}
