<?php

namespace Oro\Bundle\LocaleBundle\Model;

/**
 * @link http://userguide.icu-project.org/formatparse/datetime
 */
class Calendar
{
    const DOW_SUNDAY    = 1;
    const DOW_MONDAY    = 2;
    const DOW_TUESDAY   = 3;
    const DOW_WEDNESDAY = 4;
    const DOW_THURSDAY  = 5;
    const DOW_FRIDAY    = 6;
    const DOW_SATURDAY  = 7;

    const WIDTH_WIDE        = 'wide';        // Tuesday | September
    const WIDTH_ABBREVIATED = 'abbreviated'; // Tues    | Sept
    const WIDTH_SHORT       = 'short';       // Tu      | Sept
    const WIDTH_NARROW      = 'narrow';      // T       | S

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $language;

    /**
     * @param string|null $locale
     * @param string|null $language
     */
    public function __construct($locale = null, $language = null)
    {
        $this->locale = $locale;
        $this->language = $language;
    }

    /**
     * Gets current calendar locale
     *
     * @return string
     */
    public function getLocale()
    {
        if (null === $this->locale) {
            $this->locale = \Locale::getDefault();
        }
        return $this->locale;
    }

    /**
     * Sets current calendar locale
     *
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Gets current language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language ? : $this->getLocale();
    }

    /**
     * Sets current language
     *
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * Get instance of intl calendar object
     *
     * @return int
     */
    public function getFirstDayOfWeek()
    {
        $formatter = $this->getFormatter('cc', $this->getLocale());
        $sundayNumber = $formatter->format($this->createDateTime('Sunday, January 1, 2012'));

        // there are cases when return value is not a number, for example locales: ar_SA, hi_IN, kn_IN, etc.
        if (!is_numeric($sundayNumber)) {
            $sundayNumber = 1;
        }

        $sundayNumber = (int)$sundayNumber;
        return ($sundayNumber === 1) ? $sundayNumber : 7 - $sundayNumber + 2;
    }

    /**
     * Get list of month names, month with index 1 is January
     *
     * @param string $width Constant WIDTH_WIDE|WIDTH_ABBREVIATED|NARROW
     * @return array
     */
    public function getMonthNames($width = null)
    {
        switch ($width) {
            // Sept
            case self::WIDTH_ABBREVIATED:
            case self::WIDTH_SHORT:
                $pattern = 'LLL';
                break;
            // S
            case self::WIDTH_NARROW:
                $pattern = 'LLLLL';
                break;
            // September
            case self::WIDTH_WIDE:
            default:
                $pattern = 'LLLL';
                break;
        }
        $formatter = $this->getFormatter($pattern, $this->getLanguage());
        return array(
            1 => $formatter->format($this->createDateTime('2013-01-01')),
            $formatter->format($this->createDateTime('2013-02-01')),
            $formatter->format($this->createDateTime('2013-03-01')),
            $formatter->format($this->createDateTime('2013-04-01')),
            $formatter->format($this->createDateTime('2013-05-01')),
            $formatter->format($this->createDateTime('2013-06-01')),
            $formatter->format($this->createDateTime('2013-07-01')),
            $formatter->format($this->createDateTime('2013-08-01')),
            $formatter->format($this->createDateTime('2013-09-01')),
            $formatter->format($this->createDateTime('2013-10-01')),
            $formatter->format($this->createDateTime('2013-11-01')),
            $formatter->format($this->createDateTime('2013-12-01')),
        );
    }

    /**
     * Get list of day names
     *
     * @param string $width Constant WIDTH_WIDE|WIDTH_ABBREVIATED|WIDTH_SHORT|WIDTH_NARROW
     * @return array
     */
    public function getDayOfWeekNames($width = null)
    {
        switch ($width) {
            // Tues
            case self::WIDTH_ABBREVIATED:
                $pattern = 'ccc';
                break;
            // Tu
            case self::WIDTH_SHORT:
                $pattern = 'cccccc';
                break;
            // T
            case self::WIDTH_NARROW:
                $pattern = 'ccccc';
                break;
            // Tuesday
            case self::WIDTH_WIDE:
            default:
                $pattern = 'cccc';
                break;
        }

        $formatter = $this->getFormatter($pattern, $this->getLanguage());
        return array(
            self::DOW_SUNDAY    => $formatter->format($this->createDateTime('Sunday, January 1, 2012')),
            self::DOW_MONDAY    => $formatter->format($this->createDateTime('Monday, January 2, 2012')),
            self::DOW_TUESDAY   => $formatter->format($this->createDateTime('Tuesday, January 3, 2012')),
            self::DOW_WEDNESDAY => $formatter->format($this->createDateTime('Wednesday, January 4, 2012')),
            self::DOW_THURSDAY  => $formatter->format($this->createDateTime('Thursday, January 5, 2012')),
            self::DOW_FRIDAY    => $formatter->format($this->createDateTime('Friday, January 5, 2012')),
            self::DOW_SATURDAY  => $formatter->format($this->createDateTime('Saturday, January 5, 2012')),
        );
    }

    /**
     * Gets instance of intl date formatter by parameters
     *
     * @param string|null $pattern
     * @param string|null $locale
     * @return \IntlDateFormatter
     */
    protected function getFormatter($pattern = null, $locale = null)
    {
        return new \IntlDateFormatter(
            $locale ? : $this->getLocale(),
            null,
            null,
            'UTC',
            \IntlDateFormatter::GREGORIAN,
            $pattern
        );
    }

    /**
     * @param string $date
     * @return \DateTime
     */
    protected function createDateTime($date)
    {
        return new \DateTime($date, new \DateTimeZone('UTC'));
    }
}
