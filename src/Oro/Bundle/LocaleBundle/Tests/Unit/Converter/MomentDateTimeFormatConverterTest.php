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
        return new MomentDateTimeFormatConverter($this->formatter);
    }

    /**
     * {@inheritDoc}
     */
    public function getDateFormatDataProvider()
    {
        return array(
            'en default' => array("MMM D, YYYY", null, self::LOCALE_EN),
            'en custom'  => array("MMMM D, YYYY", \IntlDateFormatter::LONG, self::LOCALE_EN),
            'ru default' => array("DD.MM.YYYY", null, self::LOCALE_RU),
            'ru custom'  => array("D MMMM YYYY [г.]", \IntlDateFormatter::LONG, self::LOCALE_RU),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeFormatDataProvider()
    {
        return array(
            'en default'      => array("h:mm A", null, self::LOCALE_EN),
            'en custom'       => array("h:mm:ss A", \IntlDateFormatter::MEDIUM, self::LOCALE_EN),
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
            'en default' => array("MMM D, YYYY h:mm A", null, null, self::LOCALE_EN),
            'en custom'  => array(
                "MMMM D, YYYY h:mm:ss A",
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::MEDIUM,
                self::LOCALE_EN
            ),
            'ru default' => array("DD.MM.YYYY H:mm", null, null, self::LOCALE_RU),
            'ru custom'  => array(
                "D MMMM YYYY [г.] H:mm:ss",
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::MEDIUM,
                self::LOCALE_RU
            ),
        );
    }
}
