<?php

namespace Oro\Bundle\LocaleBundle\Formatter;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class DateTimeFormatter
{
    const DEFAULT_DATE_TYPE = \IntlDateFormatter::MEDIUM;
    const DEFAULT_TIME_TYPE = \IntlDateFormatter::SHORT;

    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    /**
     * @param LocaleSettings $localeSettings
     */
    public function __construct(LocaleSettings $localeSettings)
    {
        $this->localeSettings = $localeSettings;
    }

    /**
     * Formats date time
     *
     * @param \DateTime|string|int $date
     * @param string|int|null $dateType
     * @param string|int|null $timeType
     * @param string|null $locale
     * @param string|null $timeZone
     * @param string|null $pattern
     * @return string
     */
    public function format($date, $dateType = null, $timeType = null, $locale = null, $timeZone = null, $pattern = null)
    {
        if (!$timeZone) {
            $timeZone = $this->localeSettings->getTimeZone();
        }
        $date = $this->getDateTime($date);
        $formatter = $this->getFormatter($dateType, $timeType, $locale, $timeZone, $pattern);
        return $formatter->format((int)$date->format('U'));
    }

    /**
     * Formats date without time
     *
     * @param \DateTime|string|int $date
     * @param string|int|null $dateType
     * @param string|null $locale
     * @param string|null $timeZone
     * @return string
     */
    public function formatDate($date, $dateType = null, $locale = null, $timeZone = null)
    {
        return $this->format($date, $dateType, \IntlDateFormatter::NONE, $locale, $timeZone);
    }

    /**
     * Formats time without date
     *
     * @param \DateTime|string|int $date
     * @param string|int|null $timeType
     * @param string|null $locale
     * @param string|null $timeZone
     * @return string
     */
    public function formatTime($date, $timeType = null, $locale = null, $timeZone = null)
    {
        return $this->format($date, \IntlDateFormatter::NONE, $timeType, $locale, $timeZone);
    }

    /**
     * Get the pattern used for the IntlDateFormatter
     *
     * @param int|string $dateType Constant of IntlDateFormatter (NONE, FULL, LONG, MEDIUM, SHORT) or it's string name
     * @param int|string $timeType Constant IntlDateFormatter (NONE, FULL, LONG, MEDIUM, SHORT) or it's string name
     * @param string|null $locale
     * @return string
     */
    public function getPattern($dateType, $timeType, $locale = null)
    {
        if (!$locale) {
            $locale = $this->localeSettings->getLocale();
        }

        if (null === $dateType) {
            $dateType = static::DEFAULT_DATE_TYPE;
        }

        if (null === $timeType) {
            $timeType = static::DEFAULT_TIME_TYPE;
        }

        $dateType = $this->parseDateType($dateType);
        $timeType = $this->parseDateType($timeType);

        $localeFormatter = new \IntlDateFormatter($locale, $dateType, $timeType, null, \IntlDateFormatter::GREGORIAN);
        return $localeFormatter->getPattern();
    }

    /**
     * Gets instance of intl date formatter by parameters
     *
     * @param string|int|null $dateType
     * @param string|int|null $timeType
     * @param string|null $locale
     * @param string|null $timeZone
     * @param string|null $pattern
     * @return \IntlDateFormatter
     */
    protected function getFormatter($dateType, $timeType, $locale, $timeZone, $pattern)
    {
        if (!$pattern) {
            $pattern = $this->getPattern($dateType, $timeType, $locale);
        }
        return new \IntlDateFormatter(
            $this->localeSettings->getLanguage(),
            null,
            null,
            $timeZone,
            \IntlDateFormatter::GREGORIAN,
            $pattern
        );
    }

    /**
     * Try to parse date type. If null return \IntlDateFormatter::FULL type, if string try to eval
     * constant with this name.
     *
     * @param int|string|null $dateType A constant of \IntlDateFormatter type, a string name of type or null
     * @return int
     * @throws \InvalidArgumentException
     */
    protected function parseDateType($dateType)
    {
        if (null === $dateType) {
            $dateType = \IntlDateFormatter::MEDIUM;
        } elseif (!is_int($dateType) && is_string($dateType)) {
            $dateConstant = 'IntlDateFormatter::' . strtoupper($dateType);
            if (defined($dateConstant)) {
                $dateType = constant($dateConstant);
            } else {
                throw new \InvalidArgumentException("IntlDateFormatter has no type '$dateType'");
            }
        }

        $allowedTypes = array(
            \IntlDateFormatter::FULL, \IntlDateFormatter::LONG,
            \IntlDateFormatter::MEDIUM, \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE
        );

        if (!in_array((int)$dateType, $allowedTypes)) {
            throw new \InvalidArgumentException("IntlDateFormatter type '$dateType' is not supported");
        }

        return (int)$dateType;
    }

    /**
     * Returns DateTime by $data and $timezone
     *
     * @param \DateTime|string|int $date
     * @return \DateTime
     */
    protected function getDateTime($date)
    {
        if ($date instanceof \DateTime) {
            return $date;
        }

        $defaultTimezone = date_default_timezone_get();

        date_default_timezone_set('UTC');

        if (is_numeric($date)) {
            $date = (int)$date;
        }

        if (is_string($date)) {
            $date = strtotime($date);
        }

        $result = new \DateTime();
        $result->setTimestamp($date);

        date_default_timezone_set($defaultTimezone);

        return $result;
    }
}
