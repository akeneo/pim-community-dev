<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit\Converter;

use Oro\Bundle\CalendarBundle\Converter\FullCalendarDateTimeFormatConverter;
use Oro\Bundle\LocaleBundle\Tests\Unit\Converter\AbstractFormatConverterTestCase;

class FullCalendarDateTimeFormatConverterTest extends AbstractFormatConverterTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function createFormatConverter()
    {
        return new FullCalendarDateTimeFormatConverter($this->formatter);
    }

    /**
     * {@inheritDoc}
     */
    public function getDateFormatDataProvider()
    {
        return array(
            'en default' => array("MMM d, yyyy", null, self::LOCALE_EN),
            'en custom'  => array("MMMM d, yyyy", \IntlDateFormatter::LONG, self::LOCALE_EN),
            'ru default' => array("dd.MM.yyyy", null, self::LOCALE_RU),
            'ru custom'  => array("d MMMM yyyy 'г.'", \IntlDateFormatter::LONG, self::LOCALE_RU),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeFormatDataProvider()
    {
        return array(
            'en default' => array("h:mm TT", null, self::LOCALE_EN),
            'en custom' => array("h:mm:ss TT", \IntlDateFormatter::MEDIUM, self::LOCALE_EN),
            'ru default' => array("H:mm", null, self::LOCALE_RU),
            'ru custom' => array("H:mm:ss", \IntlDateFormatter::MEDIUM, self::LOCALE_RU),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeFormatDataProvider()
    {
        return array(
            'en default' => array("MMM d, yyyy h:mm TT", null, null, self::LOCALE_EN),
            'en custom'  => array(
                "MMMM d, yyyy h:mm:ss TT",
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::MEDIUM,
                self::LOCALE_EN
            ),
            'ru default' => array("dd.MM.yyyy H:mm", null, null, self::LOCALE_RU),
            'ru custom'  => array(
                "d MMMM yyyy 'г.' H:mm:ss",
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::MEDIUM,
                self::LOCALE_RU
            ),
        );
    }
}
