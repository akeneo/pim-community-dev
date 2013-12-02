<?php

namespace Oro\Bundle\CalendarBundle\Provider;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class CalendarDateTimeConfigProvider
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
     * @param \DateTime|null $date
     * @return array
     */
    public function getDateRange(\DateTime $date = null)
    {
        $timezone = $this->localeSettings->getTimeZone();
        $timezoneObj = new \DateTimeZone($timezone);
        if (null === $date) {
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
    public function getTimezoneOffset(\DateTime $date = null)
    {
        $timezone = $this->localeSettings->getTimeZone();

        $timezoneObj = new \DateTimeZone($timezone);
        if ($date === null) {
            $date = new \DateTime('now', $timezoneObj);
        } else {
            $date->setTimezone($timezoneObj);
        }
        $timezoneOffset = $timezoneObj->getOffset($date) / 60;

        return $timezoneOffset;
    }
}
