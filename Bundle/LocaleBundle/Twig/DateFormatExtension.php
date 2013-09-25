<?php

namespace Oro\Bundle\LocaleBundle\Twig;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class DateFormatExtension extends \Twig_Extension
{
    const TIMEZONE_CONFIG_KEY    = 'oro_locale.timezone';
    const DATE_FORMAT_CONFIG_KEY = 'oro_locale.date_format';
    const TIME_FORMAT_CONFIG_KEY = 'oro_locale.time_format';

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
            'oro_config_timezone'              => new \Twig_Function_Method($this, 'getTimeZone'),
            'oro_config_moment_dateformat'     => new \Twig_Function_Method($this, 'getMomentDateFormat'),
            'oro_config_moment_datetimeformat' => new \Twig_Function_Method($this, 'getMomentDateTimeFormat'),
        );
    }

    /**
     *
     * @param \Twig_Environment $env
     * @param                   $date
     * @param                   $dateTimeFormat
     * @param null              $locale
     * @param                   $timezone
     *
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
     *
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
                'MM',
                'M',
                'dd',
                'd',
                'yy',
                'yyyy'
            ),
            $dateTimeFormat
        );
    }

    public function convertDateTimeToMomentJSFormat($format)
    {
        return str_replace(
            array(
                'd',
                'j',
                'm',
                'n',
                'M',
                'Y',
                'y',
                'G',
                'H',
                'g',
                'h',
                'i',
                's',
            ),
            array(
                'DD',
                'D',
                'MM',
                'M',
                'MMM',
                'YYYY',
                'YY',
                'H',
                'HH',
                'h',
                'hh',
                'mm',
                'ss',
            ),
            $format
        );
    }

    /**
     * Returns date
     *
     * @return string
     */
    public function getMomentDateTimeFormat()
    {
        $dateFormat = $this->cm->get(self::DATE_FORMAT_CONFIG_KEY);
        $timeFormat = $this->cm->get(self::TIME_FORMAT_CONFIG_KEY);

        return $this->convertDateTimeToMomentJSFormat(implode(' ', array($dateFormat, $timeFormat)));
    }

    /**
     * Returns date
     *
     * @return string
     */
    public function getMomentDateFormat()
    {
        $format = $this->cm->get(self::DATE_FORMAT_CONFIG_KEY);

        return $this->convertDateTimeToMomentJSFormat($format);
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
