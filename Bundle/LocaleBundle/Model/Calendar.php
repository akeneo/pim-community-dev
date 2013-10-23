<?php

namespace Oro\Bundle\LocaleBundle\Model;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

/**
 * @link http://userguide.icu-project.org/formatparse/datetime
 */
class Calendar
{
    const DOW_SUNDAY = 1;
    const DOW_MONDAY = 2;
    const DOW_TUESDAY = 3;
    const DOW_WEDNESDAY = 4;
    const DOW_THURSDAY = 5;
    const DOW_FRIDAY = 6;
    const DOW_SATURDAY = 7;

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
     * Get instance of intl calendar object
     *
     * @param string|null $locale
     * @return int
     */
    public function getFirstDayOfWeek($locale = null)
    {
        $formatter = $this->getFormatter($locale, 'cc');
        $sundayNumber = $formatter->format(new \DateTime('Sunday, January 1, 2012'));

        // there are cases when return value is not a number, for example locales: ar_SA, hi_IN, kn_IN, etc.
        if (!is_numeric($sundayNumber)) {
            $sundayNumber = 1;
        }

        $sundayNumber = (int)$sundayNumber;
        return ($sundayNumber === 1) ? $sundayNumber : 7 - $sundayNumber + 2;
    }

    /**
     * Get list of month names, month with index 1 - January
     *
     * @param string|null $locale
     * @return array
     */
    public function getMonthNames($locale = null)
    {
        $formatter = $this->getFormatter($locale, 'LLLL');

        return array(
            1 => $formatter->format(new \DateTime('2013-01-01')),
            $formatter->format(new \DateTime('2013-02-01')),
            $formatter->format(new \DateTime('2013-03-01')),
            $formatter->format(new \DateTime('2013-04-01')),
            $formatter->format(new \DateTime('2013-05-01')),
            $formatter->format(new \DateTime('2013-06-01')),
            $formatter->format(new \DateTime('2013-07-01')),
            $formatter->format(new \DateTime('2013-08-01')),
            $formatter->format(new \DateTime('2013-09-01')),
            $formatter->format(new \DateTime('2013-10-01')),
            $formatter->format(new \DateTime('2013-11-01')),
            $formatter->format(new \DateTime('2013-12-01')),
        );
    }

    /**
     * Get list of month short names, month with index 1 - January
     *
     * @param string|null $locale
     * @return array
     */
    public function getMonthShortNames($locale = null)
    {
        $formatter = $this->getFormatter($locale, 'LLL');

        return array(
            1 => $formatter->format(new \DateTime('2013-01-01')),
            $formatter->format(new \DateTime('2013-02-01')),
            $formatter->format(new \DateTime('2013-03-01')),
            $formatter->format(new \DateTime('2013-04-01')),
            $formatter->format(new \DateTime('2013-05-01')),
            $formatter->format(new \DateTime('2013-06-01')),
            $formatter->format(new \DateTime('2013-07-01')),
            $formatter->format(new \DateTime('2013-08-01')),
            $formatter->format(new \DateTime('2013-09-01')),
            $formatter->format(new \DateTime('2013-10-01')),
            $formatter->format(new \DateTime('2013-11-01')),
            $formatter->format(new \DateTime('2013-12-01')),
        );
    }

    /**
     * Get list of month short names, month with index 1 - January
     *
     * @param string|null $locale
     * @return array
     */
    public function getDayNames($locale = null)
    {
        $formatter = $this->getFormatter($locale, 'cccc');

        return array(
            self::DOW_SUNDAY    => $formatter->format(new \DateTime('Sunday, January 1, 2012')),
            self::DOW_MONDAY    => $formatter->format(new \DateTime('Monday, January 2, 2012')),
            self::DOW_TUESDAY   => $formatter->format(new \DateTime('Tuesday, January 3, 2012')),
            self::DOW_WEDNESDAY => $formatter->format(new \DateTime('Wednesday, January 4, 2012')),
            self::DOW_THURSDAY  => $formatter->format(new \DateTime('Thursday, January 5, 2012')),
            self::DOW_FRIDAY    => $formatter->format(new \DateTime('Friday, January 5, 2012')),
            self::DOW_SATURDAY  => $formatter->format(new \DateTime('Saturday, January 5, 2012')),
        );
    }

    /**
     * Get list of month short names, month with index 1 - January
     *
     * @param string|null $locale
     * @return array
     */
    public function getDayShortNames($locale = null)
    {
        $formatter = $this->getFormatter($locale, 'cccccc');

        return array(
            self::DOW_SUNDAY    => $formatter->format(new \DateTime('Sunday, January 1, 2012')),
            self::DOW_MONDAY    => $formatter->format(new \DateTime('Monday, January 2, 2012')),
            self::DOW_TUESDAY   => $formatter->format(new \DateTime('Tuesday, January 3, 2012')),
            self::DOW_WEDNESDAY => $formatter->format(new \DateTime('Wednesday, January 4, 2012')),
            self::DOW_THURSDAY  => $formatter->format(new \DateTime('Thursday, January 5, 2012')),
            self::DOW_FRIDAY    => $formatter->format(new \DateTime('Friday, January 5, 2012')),
            self::DOW_SATURDAY  => $formatter->format(new \DateTime('Saturday, January 5, 2012')),
        );
    }

    /**
     * Gets instance of intl date formatter by parameters
     *
     * @param string|null $locale
     * @param string|null $pattern
     * @return \IntlDateFormatter
     */
    protected function getFormatter($locale = null, $pattern = null)
    {
        return new \IntlDateFormatter(
            $locale ? : $this->localeSettings->getLocale(),
            null,
            null,
            null,
            \IntlDateFormatter::GREGORIAN,
            $pattern
        );
    }
}
