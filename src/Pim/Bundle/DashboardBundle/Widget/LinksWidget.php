<?php

namespace Pim\Bundle\DashboardBundle\Widget;

use Oro\Bundle\SecurityBundle\SecurityFacade;

/**
 * Widget to display links
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LinksWidget implements WidgetInterface
{
    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var array */
    protected $widgets;

    /**
     * @param SecurityFacade $securityFacade
     * @param array          $widgets
     */
    public function __construct(SecurityFacade $securityFacade, array $widgets)
    {
        $this->securityFacade = $securityFacade;
        $this->widgets        = $widgets;
    }

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
        $result = ['links' => []];

        foreach ($this->widgets as $widget) {
            if ($this->securityFacade->isGranted($widget['acl'])) {
                $result['links'][] = $widget;
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return null;
    }
}
