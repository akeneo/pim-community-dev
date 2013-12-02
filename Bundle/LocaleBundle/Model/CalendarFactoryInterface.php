<?php

namespace Oro\Bundle\LocaleBundle\Model;

interface CalendarFactoryInterface
{
    /**
     * Get calendar instance
     *
     * @param string|null $locale
     * @param string|null $language
     * @return Calendar
     */
    public function getCalendar($locale = null, $language = null);
}
