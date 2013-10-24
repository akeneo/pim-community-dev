<?php

namespace Oro\Bundle\LocaleBundle\Twig;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class CalendarExtension extends \Twig_Extension
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
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'oro_calendar_month_names',
                array($this, 'getMonthNames'),
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction(
                'oro_calendar_day_of_week_names',
                array($this, 'getDayOfWeekNames'),
                array('is_safe' => array('html'))
            ),
            new \Twig_SimpleFunction(
                'oro_calendar_first_day_of_week',
                array($this, 'getFirstDayOfWeek'),
                array('is_safe' => array('html'))
            ),
        );
    }

    /**
     * Gets list of months names using given width and locale.
     *
     * @param string $width wide|abbreviation|short|narrow
     * @param string|null $locale
     * @return string
     */
    public function getMonthNames($width = null, $locale = null)
    {
        return $this->localeSettings->getCalendar($locale)->getMonthNames($width);
    }

    /**
     * Gets list of week day names using given width and locale.
     *
     * @param string $width wide|abbreviation|short|narrow
     * @param string|null $locale
     * @return string
     */
    public function getDayOfWeekNames($width = null, $locale = null)
    {
        return $this->localeSettings->getCalendar($locale)->getDayOfWeekNames($width);
    }

    /**
     * Gets first day of week according to constants of Calendar.
     *
     * @param string|null $locale
     * @return string
     */
    public function getFirstDayOfWeek($locale = null)
    {
        return $this->localeSettings->getCalendar($locale)->getFirstDayOfWeek();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_locale_calendar';
    }
}
