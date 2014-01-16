<?php

namespace Pim\Bundle\DashboardBundle\Widget;

/**
 * Widget to display links
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LinksWidget implements WidgetInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return 'PimDashboardBundle:Widget:links.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return array(
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
        );
    }
}
