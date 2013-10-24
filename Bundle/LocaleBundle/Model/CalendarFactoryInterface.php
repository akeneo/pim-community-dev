<?php

namespace Oro\Bundle\LocaleBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;

interface CalendarFactoryInterface
{
    /**
     * Get calendar instance
     *
     * @param string $locale
     * @return Calendar
     */
    public function getCalendar($locale = null);
}
