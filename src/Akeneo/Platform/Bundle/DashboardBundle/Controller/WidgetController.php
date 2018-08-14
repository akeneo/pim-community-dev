<?php

namespace Akeneo\Platform\Bundle\DashboardBundle\Controller;

use Akeneo\Platform\Bundle\DashboardBundle\Widget\Registry;
use Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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

    /** @var EngineInterface */
    protected $templating;

    /**
     * @param Registry        $widgetRegistry
     * @param EngineInterface $templating
     */
    public function __construct(Registry $widgetRegistry, EngineInterface $templating)
    {
        $this->widgetRegistry = $widgetRegistry;
        $this->templating = $templating;
    }

    /**
     * Renders dashboard widgets
     *
     * @return Response
     */
    public function listAction()
    {
        $output = '';
        $widgets = $this->widgetRegistry->getAll();

        foreach ($widgets as $widget) {
            $output .= $this->renderWidget($widget);
        }

        return new Response($output);
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

    /**
     * Returns a rendered widget template
     *
     * @param WidgetInterface $widget
     *
     * @return string
     */
    protected function renderWidget(WidgetInterface $widget)
    {
        return $this->templating->render($widget->getTemplate(), $widget->getParameters());
    }
}
