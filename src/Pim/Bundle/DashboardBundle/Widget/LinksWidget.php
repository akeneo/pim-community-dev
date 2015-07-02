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
    public function getAlias()
    {
        return 'links';
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return [
            'links' => [
                [
                    'route' => 'pim_enrich_product_index',
                    'label' => 'pim_dashboard.link.label.product',
                    'icon'  => 'barcode',
                ],
                [
                    'route' => 'pim_enrich_family_create',
                    'label' => 'pim_dashboard.link.label.family',
                    'icon'  => 'folder-open-alt',
                ],
                [
                    'route' => 'pim_enrich_attribute_index',
                    'label' => 'pim_dashboard.link.label.attribute',
                    'icon'  => 'list-ul',
                ],
                [
                    'route' => 'pim_enrich_categorytree_index',
                    'label' => 'pim_dashboard.link.label.category',
                    'icon'  => 'sitemap',
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return null;
    }
}
