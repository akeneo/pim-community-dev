<?php

namespace Oro\Bundle\UIBundle\Twig;

class SkypeButtonExtension extends \Twig_Extension
{
    const SKYPE_BUTTON_TEMPLATE = 'OroUIBundle::skype_button.html.twig';

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'skype_button' => new \Twig_Function_Method(
                $this,
                'getSkypeButton',
                array(
                    'is_safe' => array('html'),
                    'needs_environment' => true
                )
            ),
        );
    }

    /**
     * Skype.UI wrapper
     *
     * @param \Twig_Environment $environment
     * @param string $skypeUserName
     * @param array $options
     * @return int
     */
    public function getSkypeButton(\Twig_Environment $environment, $skypeUserName, $options = array())
    {
        if (!isset($options['element'])) {
            $options['element'] = 'skype_button_' . md5($skypeUserName) . '_' . mt_rand(1, 99999);
        }
        if (!isset($options['participants'])) {
            $options['participants'] = (array)$skypeUserName;
        }
        if (!isset($options['name'])) {
            $options['name'] = 'call';
        }

        $templateName = isset($options['template']) ? $options['template'] : self::SKYPE_BUTTON_TEMPLATE;
        unset($options['template']);
        return $environment->render(
            $templateName,
            array(
                'options' => $options
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_ui.skype_button';
    }
}
