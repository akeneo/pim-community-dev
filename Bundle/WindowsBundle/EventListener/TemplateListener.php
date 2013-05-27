<?php

namespace Oro\Bundle\WindowsBundle\EventListener;

use Sensio\Bundle\FrameworkExtraBundle\EventListener\TemplateListener as FrameworkTemplateListener;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine;

class TemplateListener extends FrameworkTemplateListener
{
    const TEMPLATE_PARTS_SEPARATOR = ':';
    const DEFAULT_CONTAINER = 'widget';

    /**
     * {@inheritdoc}
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        if ($container = $request->query->get('_widgetContainer', $request->request->get('_widgetContainer'))) {
            $template = $request->attributes->get('_template');
            if (strpos($template, self::TEMPLATE_PARTS_SEPARATOR) !== false) {
                $templateParts = explode(self::TEMPLATE_PARTS_SEPARATOR, $template);
                if ($templateParts) {
                    $containerTemplate = $this->getTemplateName($templateParts, $container);
                    $widgetTemplate = $this->getTemplateName($templateParts, self::DEFAULT_CONTAINER);

                    /** @var $templating DelegatingEngine */
                    $templating = $this->container->get('templating');
                    if ($templating->exists($containerTemplate)) {
                        $request->attributes->set('_template', $containerTemplate);
                    } elseif ($templating->exists($widgetTemplate)) {
                        $request->attributes->set('_template', $widgetTemplate);
                    }
                }
            }
        }

        return parent::onKernelView($event);
    }

    /**
     * Get new template name based on container
     *
     * @param array $parts
     * @param string $container
     * @return string
     */
    protected function getTemplateName(array $parts, $container)
    {
        $partsCount = count($parts);
        $parts[$partsCount - 1] = $container . '.' . $parts[$partsCount - 1];
        return implode(self::TEMPLATE_PARTS_SEPARATOR, $parts);
    }
}
