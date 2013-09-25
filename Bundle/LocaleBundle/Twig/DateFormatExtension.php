<?php

namespace Oro\Bundle\LocaleBundle\Twig;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class DateFormatExtension extends \Twig_Extension
{
    /**
    public function getFilters()
    {
        return array(
            'convert_format' => new \Twig_SimpleFilter('convert_format', array($this, 'convertDateFormat'),
     * array('needs_environment' => true))
            //'localizeddate' => new Twig_Filter_Function('twig_localized_date_filter',
     * array('needs_environment' => true)),
        );
    }

    public function formatDateTime(Twig_Environment $env, $dateTimeFormat, $)
    {
        twig_escape_filter(
            $this->env,
            twig_localized_date_filter(
                $this->env,
                (isset($context["time"]) ? $context["time"] : $this->getContext($context, "time")),
                "short",
                "short"
            ),
            "html",
            null,
            true
        );

        return ;
    }

     **/

    const TIMEZONE_CONFIG_KEY = 'oro_locale.timezone';

    /** @var ConfigManager */
    protected $cm;

    /**
     * @param ConfigManager $cm
     */
    public function __construct(ConfigManager $cm)
    {
        $this->cm = $cm;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'oro_config_timezone'  => new \Twig_Function_Method($this, 'getTimeZone')
        );
    }

    /**
     * Get config time zone
     *
     * @return string
     */
    public function getTimeZone()
    {
        $timezone = $this->cm->get(self::TIMEZONE_CONFIG_KEY);

        $result = '+00:00';
        if ($timezone) {
            $date = new \DateTime('now', new \DateTimeZone($timezone));

            $result = $date->format('P');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_locale';
    }
}
