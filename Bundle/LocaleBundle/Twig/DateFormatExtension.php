<?php

namespace Oro\Bundle\LocaleBundle\Twig;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class DateFormatExtension extends \Twig_Extension
{
    const CONFIG_TIMEZONE_KEY    = 'oro_locale.timezone';
    const CONFIG_DATE_FORMAT_KEY = 'oro_locale.date_format';
    const CONFIG_TIME_FORMAT_KEY = 'oro_locale.time_format';

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
            'locale_date'     => new \Twig_SimpleFilter(
                'locale_date',
                array($this, 'formatDate'),
                array('needs_environment' => true)
            ),
            'locale_datetime' => new \Twig_SimpleFilter(
                'locale_datetime',
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
    public function formatDateTime(
        \Twig_Environment $env,
        $date,
        $dateTimeFormat = null,
        $locale = null,
        $timezone = null
    ) {
        if (is_null($dateTimeFormat)) {
            $dateTimeFormat = $this->cm->get(self::CONFIG_DATE_FORMAT_KEY) .
                ' ' . $this->cm->get(self::CONFIG_TIME_FORMAT_KEY);
        }

        return $this->formatDate($env, $date, $dateTimeFormat, $locale, $timezone);
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
    public function formatDate(
        \Twig_Environment $env,
        $date,
        $dateTimeFormat = null,
        $locale = null,
        $timezone = null
    ) {
        if (is_null($dateTimeFormat)) {
            $dateTimeFormat = $this->cm->get(self::CONFIG_DATE_FORMAT_KEY);
        }

        $dateTimeFormat = $dateTimeFormat === false ? 'd/m/Y H:i:s' : $dateTimeFormat;
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
                'Y', // year YYYY,
                'F', // month name
                'H', // hour 24h with 0
                'G', // hour 24h without 0
                'h', // hour 12h with 0
                'g', // hour 12h without 0
                'i', // min
                's', // sec
                'a', // am/pm
                'A', // AM/PM
            ),
            array(
                'MM',
                'M',
                'dd',
                'd',
                'yy',
                'yyyy',
                'MMMM',
                'HH',
                'H',
                'hh',
                'h',
                'mm',
                'ss',
                'a',
                'a'
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
                'n',
                'M',
                'm',
                'Y',
                'y',
                'G',
                'H',
                'h',
                'g',
                'i',
                's',
            ),
            array(
                'DD',
                'D',
                'M',
                'MMM',
                'MM',
                'YYYY',
                'YY',
                'H',
                'HH',
                'hh',
                'h',
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
        $dateFormat = $this->cm->get(self::CONFIG_DATE_FORMAT_KEY);
        $timeFormat = $this->cm->get(self::CONFIG_TIME_FORMAT_KEY);

        return $this->convertDateTimeToMomentJSFormat(implode(' ', array($dateFormat, $timeFormat)));
    }

    /**
     * Returns date
     *
     * @return string
     */
    public function getMomentDateFormat()
    {
        $format = $this->cm->get(self::CONFIG_DATE_FORMAT_KEY);

        return $this->convertDateTimeToMomentJSFormat($format);
    }

    /**
     * Get config time zone
     *
     * @return string
     */
    public function getTimeZone()
    {
        $timezone = $this->cm->get(self::CONFIG_TIMEZONE_KEY);

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
