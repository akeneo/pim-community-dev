<?php

namespace Oro\Bundle\CalendarBundle\Provider;

use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class CalendarDateTimeConfigProvider
{
    /**
     * @var DateTimeFormatter
     */
    protected $dateTimeFormatter;

    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    public function __construct(LocaleSettings $localeSettings, DateTimeFormatter $dateTimeFormatter)
    {
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->localeSettings = $localeSettings;
    }

    /**
     * @param \DateTime|null $date
     * @return array
     */
    public function getDateRange(\DateTime $date = null)
    {
        $timezone    = $this->localeSettings->getTimeZone();
        $timezoneObj = new \DateTimeZone($timezone);
        if ($date === null) {
            $date = new \DateTime('now', $timezoneObj);
        } else {
            $date->setTimezone($timezoneObj);
        }
        $firstDay  = $this->localeSettings->getCalendar()->getFirstDayOfWeek() - 1;
        $startDate = clone $date;
        $startDate->setDate($date->format('Y'), $date->format('n'), 1);
        $startDate->setTime(0, 0, 0);
        $startDate->sub(new \DateInterval('P' . ((int)$startDate->format('w') - $firstDay + 7) % 7 . 'D'));
        $endDate = clone $startDate;
        $endDate->add(new \DateInterval('P6W'));

        return array(
            'startDate' => $startDate,
            'endDate'   => $endDate,
        );
    }

    /**
     * @param \DateTime|null $date
     * @return array
     */
    public function getCalendarOptions(\DateTime $date = null)
    {
        $calendar = $this->localeSettings->getCalendar();
        $timezone = $this->localeSettings->getTimeZone();

        $dateFormat            = $this->dateTimeFormatter->getPattern('medium', 'none');
        $timeFormat            = $this->dateTimeFormatter->getPattern('none', 'short');
        $dateFormatWithoutYear = preg_replace('/[ .\/-]?y[ .\/-]?/i', '', $dateFormat);

        $timezoneObj = new \DateTimeZone($timezone);
        if ($date === null) {
            $date = new \DateTime('now', $timezoneObj);
        } else {
            $date->setTimezone($timezoneObj);
        }
        $timezoneOffset = $timezoneObj->getOffset($date) / 60;
        // prepare FullCalendar specific date/time formats
        $isDateFormatStartedWithDay = strpos($dateFormatWithoutYear, 'd') === 0
            || strpos($dateFormatWithoutYear, 'j') === 0;
        $fcWeekFormat               = $isDateFormatStartedWithDay
            ? 'd[ MMMM][ yyyy]{ \'&#8212;\' d MMMM yyyy}'
            : 'MMMM d[ yyyy]{ \'&#8212;\'[ MMMM] d yyyy}';
        $fcDateFormatWithoutYear    = $this->convertToFullCalendarDateFormat($dateFormatWithoutYear);
        $fcTimeFormat               = $this->convertToFullCalendarTimeFormat($timeFormat);

        return array(
            'date'            => $date->format('Y-m-d'),
            'timezoneOffset'  => $timezoneOffset,
            'firstDay'        => $calendar->getFirstDayOfWeek() - 1,
            'monthNames'      => array_values($calendar->getMonthNames('wide')),
            'monthNamesShort' => array_values($calendar->getMonthNames('abbreviated')),
            'dayNames'        => array_values($calendar->getDayOfWeekNames('wide')),
            'dayNamesShort'   => array_values($calendar->getDayOfWeekNames('abbreviated')),
            'titleFormat'     => array(
                'month' => 'MMMM yyyy',
                'week'  => $fcWeekFormat,
                'day'   => 'dddd, ' . $this->convertToFullCalendarDateFormat($dateFormat)
            ),
            'columnFormat'    => array(
                'month' => 'ddd',
                'week'  => 'ddd ' . $fcDateFormatWithoutYear,
                'day'   => 'dddd ' . $fcDateFormatWithoutYear
            ),
            'timeFormat'      => array(
                ''       => $fcTimeFormat,
                'agenda' => sprintf('%s{ - %s}', $fcTimeFormat, $fcTimeFormat)
            ),
            'axisFormat'      => $fcTimeFormat,
        );
    }

    /**
     * Format description
     * http://arshaw.com/fullcalendar/docs/utilities/formatDate/
     *
     * @param string $dateFormat
     *
     * @return string
     */
    protected function convertToFullCalendarDateFormat($dateFormat)
    {
        return str_replace(
            array(
                'd',
                'j',
                'l',
                'M',
                'F',
                'm',
                'n',
                'y',
                'Y',
            ),
            array(
                'dd', //day of month (two digit)
                'd', //day of month (no leading zero)
                'dddd', //day name long
                'MMM', //month name short
                'MMMM', //month name long
                'MM', //month of year (two digit)
                'M', //month of year (no leading zero)
                'yy', //year (two digit)
                'yyyy', //year (four digit)
            ),
            $dateFormat
        );
    }

    /**
     * Format description
     * http://arshaw.com/fullcalendar/docs/utilities/formatDate/
     *
     * @param string $timeFormat
     *
     * @return string
     */
    protected function convertToFullCalendarTimeFormat($timeFormat)
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
            $timeFormat
        );
    }
}
