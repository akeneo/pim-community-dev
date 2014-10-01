<?php

namespace Pim\Bundle\DashboardBundle\Twig;

use Pim\Bundle\DashboardBundle\Widget\Registry;
use Pim\Bundle\DashboardBundle\Widget\WidgetInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;

/**
 * Twig extension for widgets
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WidgetsExtension extends \Twig_Extension
{
    /** @var Registry */
    protected $registry;

    /** @var EngineInterface */
    protected $templating;

    /**
     * Constructor
     *
     * @param Registry        $registry
     * @param EngineInterface $templating
     */
    public function __construct(Registry $registry, EngineInterface $templating)
    {
        $this->registry   = $registry;
        $this->templating = $templating;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('render_widgets', [$this, 'renderWidgets'], ['is_safe' => ['html']])
        ];
    }

    /**
     * Render the widgets
     *
     * @return string
     */
    public function renderWidgets()
    {
        $output = '';
        $widgets = $this->registry->getAll();

        foreach ($widgets as $widget) {
            $output .= $this->render($widget);
        }

        return $output;
    }

    /**
     * Returns a rendered widget template
     *
     * @param WidgetInterface $widget
     *
     * @return string
     */
    protected function render(WidgetInterface $widget)
    {
        return $this->templating->render($widget->getTemplate(), $widget->getParameters());
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_widgets_extension';
    }
}
