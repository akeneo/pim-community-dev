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
        );
    }
}
