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
        return new IntlDateTimeFormatConverter($this->localeSettings);
    }

    /**
     * {@inheritDoc}
     */
    public function getDateFormatDataProvider()
    {
        return array(
            'default default' => array("MMM d, y"),
            'default custom'  => array("MMMM d, y", null, \IntlDateFormatter::LONG),
            'en default'      => array("MMM d, y", self::LOCALE_EN),
            'en custom'       => array("MMMM d, y", self::LOCALE_EN, \IntlDateFormatter::LONG),
            'ru default'      => array("dd.MM.yyyy", self::LOCALE_RU),
            'ru custom'       => array("d MMMM y 'г.'", self::LOCALE_RU, \IntlDateFormatter::LONG),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeFormatDataProvider()
    {
        return array(
            'default default' => array("h:mm a"),
            'default custom'  => array("h:mm:ss a", null, \IntlDateFormatter::MEDIUM),
            'en default'      => array("h:mm a", self::LOCALE_EN),
            'en custom'       => array("h:mm:ss a", self::LOCALE_EN, \IntlDateFormatter::MEDIUM),
            'ru default'      => array("H:mm", self::LOCALE_RU),
            'ru custom'       => array("H:mm:ss", self::LOCALE_RU, \IntlDateFormatter::MEDIUM),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeFormatDataProvider()
    {
        return array(
            'default default' => array("MMM d, y h:mm a"),
            'default custom'  => array(
                "MMMM d, y h:mm:ss a",
                null,
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::MEDIUM
            ),
            'en default' => array("MMM d, y h:mm a", self::LOCALE_EN),
            'en custom'  => array(
                "MMMM d, y h:mm:ss a",
                self::LOCALE_EN,
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::MEDIUM
            ),
            'ru default' => array("dd.MM.yyyy H:mm", self::LOCALE_RU),
            'ru custom'  => array(
                "d MMMM y 'г.' H:mm:ss",
                self::LOCALE_RU,
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::MEDIUM
            ),
        );
    }
}
