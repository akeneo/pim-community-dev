<?php

namespace Pim\Bundle\DashboardBundle\Controller;

use Pim\Bundle\DashboardBundle\Widget\Registry;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Widget controller
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WidgetController
{
    /** @var Registry */
    protected $widgetRegistry;

    /**
     * @param Registry $widgetRegistry
     */
    public function __construct(Registry $widgetRegistry)
    {
        $this->widgetRegistry = $widgetRegistry;
    }

    /**
     * Return data for a widget
     *
     * @param string $alias
     *
     * @return JsonResponse
     */
    public function dataAction($alias)
    {
        $widget = $this->widgetRegistry->get($alias);

        $data = null !== $widget ? $widget->getData() : null;

        return new JsonResponse($data);
    }
}
