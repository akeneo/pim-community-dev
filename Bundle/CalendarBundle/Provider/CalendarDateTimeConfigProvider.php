<?php

namespace Oro\Bundle\CalendarBundle\Provider;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;

class CalendarDateTimeConfigProvider
{
    /**
     * @var ConfigManager
     */
    protected $cm;

    public function __construct(ConfigManager $cm)
    {
        $this->cm = $cm;
    }

    /**
     * @param \DateTime|null $date
     * @return array
     */
    public function getDateRange(\DateTime $date = null)
    {
        $timezone    = $this->cm->get('oro_locale.timezone');
        $timezoneObj = new \DateTimeZone($timezone);
        if ($date === null) {
            $date = new \DateTime('now', $timezoneObj);
        } else {
            $date->setTimezone($timezoneObj);
        }
        $firstDay  = $this->getFirstDayOfWeek();
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
        $dateFormat            = $this->cm->get('oro_locale.date_format');
        $timeFormat            = $this->cm->get('oro_locale.time_format');
        $timezone              = $this->cm->get('oro_locale.timezone');
        $dateFormatWithoutYear = preg_replace('/[ .\/-]?y[ .\/-]?/i', '', $dateFormat);

        // @todo: need to be refactored using Intl library
        $monthNames      = array(
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        );
        $monthNamesShort = array(
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec'
        );
        $dayNames        = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        $dayNamesShort   = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');

        $timezoneObj     = new \DateTimeZone($timezone);
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
            'firstDay'        => $this->getFirstDayOfWeek(),
            'monthNames'      => $monthNames,
            'monthNamesShort' => $monthNamesShort,
            'dayNames'        => $dayNames,
            'dayNamesShort'   => $dayNamesShort,
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
     * Gets the first day of the week
     *
     * @return int
     */
    protected function getFirstDayOfWeek()
    {
        $locale = $this->cm->get('oro_locale.locale');

        // @todo: need to be refactored using Intl library
        return 0;
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
