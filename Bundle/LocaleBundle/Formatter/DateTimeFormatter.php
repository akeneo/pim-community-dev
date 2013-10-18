<?php

namespace Oro\Bundle\LocaleBundle\Formatter;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class DateTimeFormatter
{
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
     * @return string
     */
    public function format($date, $dateType = null, $timeType = null, $locale = null, $timeZone = null)
    {
        $formatter = $this->getFormatter($dateType, $timeType, $locale, $timeZone);
        return $this->doFormat($formatter, $this->getDateTime($date, $timeZone));
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
        $formatter = $this->getFormatter($dateType, \IntlDateFormatter::NONE, $locale, $timeZone);
        return $this->doFormat($formatter, $this->getDateTime($date, $timeZone));
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
        $formatter = $this->getFormatter(\IntlDateFormatter::NONE, $timeType, $locale, $timeZone);
        return $this->doFormat($formatter, $this->getDateTime($date, $timeZone));
    }

    /**
     * Formats date using ready formatter and date time object
     *
     * @param \IntlDateFormatter $formatter
     * @param \DateTime $date
     * @return string
     */
    protected function doFormat(\IntlDateFormatter $formatter, \DateTime $date)
    {
        return $formatter->format((int)$date->format('U'));
    }

    /**
     * Gets instance of intl date formatter by parameters
     *
     * @param string|int|null $dateType
     * @param string|int|null $timeType
     * @param string|null $locale
     * @param string|null $timeZone
     * @return \IntlDateFormatter
     */
    protected function getFormatter($dateType, $timeType, $locale, $timeZone)
    {
        if (!$locale) {
            $locale = $this->localeSettings->getLocale();
        }

        if (!$timeZone) {
            $timeZone = $this->localeSettings->getTimeZone();
        }

        return new \IntlDateFormatter(
            $locale,
            $this->parseDateType($dateType),
            $this->parseDateType($timeType),
            $timeZone,
            \IntlDateFormatter::GREGORIAN
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
     * @param \DateTimeZone|string|null $timeZone
     * @return \DateTime
     */
    protected function getDateTime($date, $timeZone = null)
    {
        if ($date instanceof \DateTime) {
            return $date;
        }

        if (!$timeZone) {
            $timeZone = $this->localeSettings->getTimeZone();
        }

        if (!$timeZone instanceof \DateTimeZone) {
            $timeZone = new \DateTimeZone($timeZone);
        }

        $defaultTimezone = date_default_timezone_get();

        date_default_timezone_set($timeZone->getName());

        if (is_numeric($date)) {
            $date = (int)$date;
        }

        if (is_string($date)) {
            $date = strtotime($date);
        }

        $result = new \DateTime();
        $result->setTimestamp($date);
        $result->setTimezone($timeZone);

        date_default_timezone_set($defaultTimezone);

        return $result;
    }
}
