<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Converter;

use Oro\Bundle\LocaleBundle\Converter\IntlDateTimeFormatConverter;

class IntlDateTimeFormatConverterTest extends AbstractFormatConverterTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function createFormatConverter()
    {
        return new IntlDateTimeFormatConverter($this->formatter);
    }

    /**
     * {@inheritDoc}
     */
    public function getDateFormatDataProvider()
    {
        return array(
            'en default'      => array("MMM d, y", null, self::LOCALE_EN),
            'en custom'       => array("MMMM d, y", \IntlDateFormatter::LONG, self::LOCALE_EN),
            'ru default'      => array("dd.MM.yyyy", null, self::LOCALE_RU),
            'ru custom'       => array("d MMMM y 'г.'", \IntlDateFormatter::LONG, self::LOCALE_RU),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeFormatDataProvider()
    {
        return array(
            'en default'      => array("h:mm a", null, self::LOCALE_EN),
            'en custom'       => array("h:mm:ss a", \IntlDateFormatter::MEDIUM, self::LOCALE_EN),
            'ru default'      => array("H:mm", null, self::LOCALE_RU),
            'ru custom'       => array("H:mm:ss", \IntlDateFormatter::MEDIUM, self::LOCALE_RU),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeFormatDataProvider()
    {
        return array(
            'en default' => array("MMM d, y h:mm a", null, null, self::LOCALE_EN),
            'en custom'  => array(
                "MMMM d, y h:mm:ss a",
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::MEDIUM,
                self::LOCALE_EN
            ),
            'ru default' => array("dd.MM.yyyy H:mm", null, null, self::LOCALE_RU),
            'ru custom'  => array(
                "d MMMM y 'г.' H:mm:ss",
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::MEDIUM,
                self::LOCALE_RU
            ),
        );
    }
}
