<?php

namespace Oro\Bundle\UIBundle\Twig;

use Twig_Environment;

class WidgetExtension extends \Twig_Extension
{
    const EXTENSION_NAME = 'oro_widget';

    /**
     * Protect extension from infinite loop
     *
     * @var bool
     */
    protected $rendered = array();

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'oro_widget_render' => new \Twig_Function_Method(
                $this,
                'render',
                array(
                    'is_safe' => array('html'),
                    'needs_environment' => true
                )
            )
        );
    }

    /**
     * Renders a widget.
     *
     * @param \Twig_Environment $environment
     * @param array $options
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    public function render(Twig_Environment $environment, array $options = array())
    {
        $optionsHash = spl_object_hash((object)$options);
        if (!empty($this->rendered[$optionsHash])) {
            return '';
        }
        $this->rendered[$optionsHash] = true;

        if (!array_key_exists('url', $options)) {
            throw new \InvalidArgumentException('Option url is required');
        }
        if (!array_key_exists('widgetType', $options)) {
            throw new \InvalidArgumentException('Option widgetType is required');
        } else {
            $widgetType = $options['widgetType'];
            unset($options['widgetType']);
        }
        if (!array_key_exists('elementFirst', $options)) {
            $options['elementFirst'] = true;
        }
        $options['wid'] = $this->getUniqueIdentifier();
        $elementId = 'widget-container-' . $options['wid'];
        $options['el'] = '#' . $elementId . ' .widget-content';
        $options['url'] = $this->getUrlWithContainer($options['url'], $widgetType, $options['wid']);

        return $environment->render(
            "OroUIBundle::widget_loader.html.twig",
            array(
                "widgetType" => $widgetType,
                "elementId" => $elementId,
                "options" => $options
            )
        );
    }

    /**
     * @param string $url
     * @param string $widgetType
     * @param string $wid
     * @return string
     */
    protected function getUrlWithContainer($url, $widgetType, $wid)
    {
        if (strpos($url, '_widgetContainer=') === false) {
            $parts = parse_url($url);
            $widgetPart = '_widgetContainer=' . $widgetType . '&_wid=' . $wid;
            if (array_key_exists('query', $parts)) {
                $separator = $parts['query'] ? '&' : '';
                $newQuery = $parts['query'] . $separator . $widgetPart;
                $url = str_replace($parts['query'], $newQuery, $url);
            } else {
                $url .= '?' . $widgetPart;
            }
        }
        return $url;
    }

    /**
     * @return string
     */
    protected function getUniqueIdentifier()
    {
        return str_replace('.', '-', uniqid('', true));
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::EXTENSION_NAME;
    }
}
