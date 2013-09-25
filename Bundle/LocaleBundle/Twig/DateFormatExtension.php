<?php

namespace Oro\Bundle\LocaleBundle\Twig;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class DateFormatExtension extends \Twig_Extension
{
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

    public function getFilters()
    {
        return array(
            'locale_date' => new \Twig_SimpleFilter(
                'locale_date',
                array($this, 'formatDateTime'),
                array('needs_environment' => true)
            ),
        );
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
     *
     * @param \Twig_Environment $env
     * @param $date
     * @param $dateTimeFormat
     * @param null $locale
     * @param $timezone
     * @return string
     */
    public function formatDateTime(\Twig_Environment $env, $date, $dateTimeFormat, $locale = null, $timezone = null)
    {
        $dateTimeFormat = $this->convertDateTimeToICUFormat($dateTimeFormat);

        return twig_localized_date_filter(
            $env,
            $date,
            "none",
            "none",
            $locale,
            $timezone,
            $dateTimeFormat
        );
    }

    /**
     * @param $dateTimeFormat
     * @return string libICU format for IntlDateFormatter::create()
     */
    protected function convertDateTimeToICUFormat($dateTimeFormat)
    {
        return str_replace(
            array(
                'm', // month MM
                'n',
                'd', // day DD
                'j', // day D
                'y', // year YY
                'Y', // year YYYY
            ),
            array(
                'MM', 'M', 'dd', 'd', 'yy', 'yyyy'
            ),
            $dateTimeFormat
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
