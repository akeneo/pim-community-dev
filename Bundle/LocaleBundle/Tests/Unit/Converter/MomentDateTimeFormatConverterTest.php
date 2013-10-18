<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Converter;

use Oro\Bundle\LocaleBundle\Converter\MomentDateTimeFormatConverter;

class MomentDateTimeFormatConverterTest extends AbstractFormatConverterTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function createFormatConverter()
    {
        return new MomentDateTimeFormatConverter($this->localeSettings);
    }

    /**
     * {@inheritDoc}
     */
    public function getDateFormatDataProvider()
    {
        return array(
            'default default' => array("MMM D, YYYY"),
            'default custom'  => array("MMMM D, YYYY", null, \IntlDateFormatter::LONG),
            'en default'      => array("MMM D, YYYY", self::LOCALE_EN),
            'en custom'       => array("MMMM D, YYYY", self::LOCALE_EN, \IntlDateFormatter::LONG),
            'ru default'      => array("DD.MM.YYYY", self::LOCALE_RU),
            'ru custom'       => array("D MMMM YYYY [г.]", self::LOCALE_RU, \IntlDateFormatter::LONG),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeFormatDataProvider()
    {
        return array(
            'default default' => array("h:mm A"),
            'default custom'  => array("h:mm:ss A", null, \IntlDateFormatter::MEDIUM),
            'en default'      => array("h:mm A", self::LOCALE_EN),
            'en custom'       => array("h:mm:ss A", self::LOCALE_EN, \IntlDateFormatter::MEDIUM),
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
            'default default' => array("MMM D, YYYY h:mm A"),
            'default custom'  => array(
                "MMMM D, YYYY h:mm:ss A",
                null,
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::MEDIUM
            ),
            'en default' => array("MMM D, YYYY h:mm A", self::LOCALE_EN),
            'en custom'  => array(
                "MMMM D, YYYY h:mm:ss A",
                self::LOCALE_EN,
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::MEDIUM
            ),
            'ru default' => array("DD.MM.YYYY H:mm", self::LOCALE_RU),
            'ru custom'  => array(
                "D MMMM YYYY [г.] H:mm:ss",
                self::LOCALE_RU,
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::MEDIUM
            ),
        );
    }
}
