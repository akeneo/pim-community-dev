<?php

namespace Oro\Bundle\LocaleBundle\Twig;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class DateFormatExtension extends \Twig_Extension
{
    const CONFIG_TIMEZONE_KEY    = 'oro_locale.timezone';
    const CONFIG_DATE_FORMAT_KEY = 'oro_locale.date_format';
    const CONFIG_TIME_FORMAT_KEY = 'oro_locale.time_format';
    const CONFIG_LOCALE_KEY      = 'oro_locale.locale';

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
            'locale_time'     => new \Twig_SimpleFilter(
                'locale_time',
                array($this, 'formatTime'),
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
            'oro_config_jquery_dateformat'     => new \Twig_Function_Method($this, 'getJqueryDateFormat'),
            'oro_config_jquery_timeformat'     => new \Twig_Function_Method($this, 'getJqueryTimeFormat'),
        );
    }

    /**
     * Returns a string represents the given $date as a date/time, formatted according the given parameters
     *
     * @param \Twig_Environment $env
     * @param \DateTime|string  $date
     * @param string|null       $dateTimeFormat
     * @param string|null       $locale
     * @param string|null       $timezone
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
        $this->applyDefaultDateTimeFormat($dateTimeFormat);
        $this->applyDefaultTimezone($timezone);
        $this->applyDefaultLocale($locale);

        $dateFormat = $this->convertDateTimeToICUFormat($dateTimeFormat);

        return twig_localized_date_filter(
            $env,
            $date,
            "none",
            "none",
            $locale,
            $timezone,
            $dateFormat
        );
    }

    /**
     * Returns a string represents the given $date as a date, formatted according the given parameters
     *
     * @param \Twig_Environment $env
     * @param \DateTime|string  $date
     * @param string|null       $dateFormat
     * @param string|null       $locale
     * @param string|null       $timezone
     *
     * @return string
     */
    public function formatDate(
        \Twig_Environment $env,
        $date,
        $dateFormat = null,
        $locale = null,
        $timezone = null
    ) {
        $this->applyDefaultDateTimeFormat($dateFormat, true);
        $this->applyDefaultTimezone($timezone);
        $this->applyDefaultLocale($locale);

        $dateFormat = $this->convertDateTimeToICUFormat($dateFormat);

        return twig_localized_date_filter(
            $env,
            $date,
            "none",
            "none",
            $locale,
            $timezone,
            $dateFormat
        );
    }

    /**
     * Returns a string represents the given $date as a time, formatted according the given parameters
     *
     * @param \Twig_Environment $env
     * @param \DateTime|string  $date
     * @param string|null       $timeFormat
     * @param string|null       $locale
     * @param string|null       $timezone
     *
     * @return string
     */
    public function formatTime(
        \Twig_Environment $env,
        $date,
        $timeFormat = null,
        $locale = null,
        $timezone = null
    ) {
        $this->applyDefaultTimeFormat($timeFormat);
        $this->applyDefaultTimezone($timezone);
        $this->applyDefaultLocale($locale);

        $timeFormat = $this->convertDateTimeToICUFormat($timeFormat);

        return twig_localized_date_filter(
            $env,
            $date,
            "none",
            "none",
            $locale,
            $timezone,
            $timeFormat
        );
    }

    /**
     * Applies default value for $dateTimeFormat
     * in case when this parameter was not passed from a template
     *
     * @param string $dateTimeFormat
     * @param bool   $dateOnly skip time
     */
    protected function applyDefaultDateTimeFormat(&$dateTimeFormat, $dateOnly = false)
    {
        if (is_null($dateTimeFormat)) {
            $dateTimeFormat = $this->cm->get(self::CONFIG_DATE_FORMAT_KEY)
                . ($dateOnly ? '' : ' ' . $this->cm->get(self::CONFIG_TIME_FORMAT_KEY));
        }
    }

    /**
     * Applies default value for $timeFormat
     * in case when this parameter was not passed from a template
     *
     * @param string $timeFormat
     */
    protected function applyDefaultTimeFormat(&$timeFormat)
    {
        if (is_null($timeFormat)) {
            $timeFormat = $this->cm->get(self::CONFIG_TIME_FORMAT_KEY);
        }
    }

    /**
     * Applies default value for $timezone
     * in case when this parameter was not passed from a template
     *
     * @param string|null $timezone
     */
    protected function applyDefaultTimezone(&$timezone)
    {
        if (is_null($timezone)) {
            $timezone = $this->cm->get(self::CONFIG_TIMEZONE_KEY);
        }
    }

    /**
     * Applies default value for $locale
     * in case when this parameter was not passed from a template
     *
     * @param string|null $locale
     */
    protected function applyDefaultLocale(&$locale)
    {
        if (is_null($locale)) {
            $locale = $this->cm->get(self::CONFIG_LOCALE_KEY);
        }
    }

    /**
     * Returns datetime format needed for momentJS
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
     * Returns date format needed for momentJS
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
     * Returns date format needed for jqueryUI datepicker
     *
     * @return string
     */
    public function getJqueryDateFormat()
    {
        $format = $this->cm->get(self::CONFIG_DATE_FORMAT_KEY);

        return $this->convertDateTimeToJqueryDateFormat($format);
    }

    /**
     * Returns date format needed for jqueryUI datetimepicker
     *
     * @return string
     */
    public function getJqueryTimeFormat()
    {
        $format = $this->cm->get(self::CONFIG_TIME_FORMAT_KEY);

        return $this->convertDateTimeToJqueryTimeFormat($format);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_locale';
    }

    /**
     * Format description
     * http://www.icu-project.org/apiref/icu4c/classSimpleDateFormat.html#details
     *
     * @param $dateTimeFormat
     *
     * @return string libICU format for IntlDateFormatter::create()
     */
    protected function convertDateTimeToICUFormat($dateTimeFormat)
    {
        return str_replace(
            array(
                'M', // month MM
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
                'MMM',
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

    /**
     * Format description
     * http://momentjs.com/docs/#/parsing/string-format/
     *
     * @param string $format
     *
     * @return string
     */
    protected function convertDateTimeToMomentJSFormat($format)
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
     * Format description
     * http://api.jqueryui.com/datepicker/
     *
     * @param string $format
     *
     * @return string
     */
    protected function convertDateTimeToJqueryDateFormat($format)
    {
        return str_replace(
            array(
                'd',
                'j',
                'z',
                'l',
                'm',
                'n',
                'F',
                'Y',
            ),
            array(
                'dd', //day of month (two digit)
                'd', //day of month (no leading zero)
                'o', //day of the year (no leading zeros)
                'DD', //day name long
                'mm', //month of year (two digit)
                'm', //month of year (no leading zero)
                'MM', //month name long
                'yy', //year (four digit)
            ),
            $format
        );
    }

    /**
     * Format description
     * http://trentrichardson.com/examples/timepicker/#tp-formatting
     *
     * @param string $format
     *
     * @return string
     */
    protected function convertDateTimeToJqueryTimeFormat($format)
    {
        return str_replace(
            array(
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
                'HH',
                'H',
                'hh',
                'h',
                'mm',
                'ss',
                'tt',
                'TT',
            ),
            $format
        );
    }
}
