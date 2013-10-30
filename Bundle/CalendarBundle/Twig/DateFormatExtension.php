<?php

namespace Oro\Bundle\CalendarBundle\Twig;

use Oro\Bundle\LocaleBundle\Twig\DateFormatExtension as LocaleDateFormatExtension;

class DateFormatExtension extends \Twig_Extension
{
    /** @var LocaleDateFormatExtension */
    protected $formatter;

    /**
     * @param LocaleDateFormatExtension $formatter
     */
    public function __construct(LocaleDateFormatExtension $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'calendar_date_range' => new \Twig_Function_Method(
                $this,
                'formatCalendarDateRange',
                array('needs_environment' => true)
            )
        );
    }

    /**
     * Returns a string represents a range between $startDate and $endDate, formatted according the given parameters
     * Examples:
     *      $endDate is not specified
     *          Thu Oct 17, 2013 - when $skipTime = true
     *          Thu Oct 17, 2013 5:30pm - when $skipTime = false
     *      $startDate equals to $endDate
     *          Thu Oct 17, 2013 - when $skipTime = true
     *          Thu Oct 17, 2013 5:30pm - when $skipTime = false
     *      $startDate and $endDate are the same day
     *          Thu Oct 17, 2013 - when $skipTime = true
     *          Thu Oct 17, 2013 5:00pm – 5:30pm - when $skipTime = false
     *      $startDate and $endDate are different days
     *          Thu Oct 17, 2013 5:00pm – Thu Oct 18, 2013 5:00pm - when $skipTime = false
     *          Thu Oct 17, 2013 – Thu Oct 18, 2013 - when $skipTime = true
     *
     * @param \Twig_Environment $env
     * @param \DateTime         $startDate
     * @param \DateTime|null    $endDate
     * @param bool              $skipTime
     * @param string|null       $dateTimeFormat
     * @param string|null       $dateFormat
     * @param string|null       $timeFormat
     * @param string|null       $locale
     * @param string|null       $timezone
     *
     * @return string
     */
    public function formatCalendarDateRange(
        \Twig_Environment $env,
        \DateTime $startDate,
        \DateTime $endDate = null,
        $skipTime = false,
        $dateTimeFormat = null,
        $dateFormat = null,
        $timeFormat = null,
        $locale = null,
        $timezone = null
    ) {
        // check if $endDate is not specified or $startDate equals to $endDate
        if (is_null($endDate) || $startDate == $endDate) {
            return $skipTime
                ? $this->formatter->formatDate($env, $startDate, $dateFormat, $locale, $timezone)
                : $this->formatter->formatDateTime($env, $startDate, $dateTimeFormat, $locale, $timezone);
        }

        // check if $startDate and $endDate are the same day
        if ($startDate->format('Ymd') == $endDate->format('Ymd')) {
            if ($skipTime) {
                return $this->formatter->formatDate($env, $startDate, $dateFormat, $locale, $timezone);
            }

            return sprintf(
                '%s %s - %s',
                $this->formatter->formatDate($env, $startDate, $dateFormat, $locale, $timezone),
                $this->formatter->formatTime($env, $startDate, $timeFormat, $locale, $timezone),
                $this->formatter->formatTime($env, $endDate, $timeFormat, $locale, $timezone)
            );
        }

        // $startDate and $endDate are different days
        if ($skipTime) {
            return sprintf(
                '%s - %s',
                $this->formatter->formatDate($env, $startDate, $dateFormat, $locale, $timezone),
                $this->formatter->formatDate($env, $endDate, $dateFormat, $locale, $timezone)
            );
        }

        return sprintf(
            '%s - %s',
            $this->formatter->formatDateTime($env, $startDate, $dateTimeFormat, $locale, $timezone),
            $this->formatter->formatDateTime($env, $endDate, $dateTimeFormat, $locale, $timezone)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_calendar';
    }
}
