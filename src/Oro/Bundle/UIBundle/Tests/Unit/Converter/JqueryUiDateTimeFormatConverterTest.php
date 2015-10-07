<?php

namespace Oro\Bundle\UIBundle\Tests\Unit\Converter;

use Oro\Bundle\LocaleBundle\Tests\Unit\Converter\AbstractFormatConverterTestCase;
use Oro\Bundle\UIBundle\Converter\JqueryUiDateTimeFormatConverter;

class JqueryUiDateTimeFormatConverterTest extends AbstractFormatConverterTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function createFormatConverter()
    {
        return new JqueryUiDateTimeFormatConverter($this->formatter);
    }

    /**
     * {@inheritDoc}
     */
    public function getDateFormatDataProvider()
    {
        return array(
            'en default'      => array("M d, yy", null, self::LOCALE_EN),
            'en custom'       => array("MM d, yy", \IntlDateFormatter::LONG, self::LOCALE_EN),
            'ru default'      => array("dd.mm.yy", null, self::LOCALE_RU),
            'ru custom'       => array("d MM yy г.", \IntlDateFormatter::LONG, self::LOCALE_RU),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeFormatDataProvider()
    {
        return array(
            'en default'      => array("h:mm TT", null, self::LOCALE_EN),
            'en custom'       => array("h:mm:ss TT", \IntlDateFormatter::MEDIUM, self::LOCALE_EN),
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
            'en default' => array("M d, yy h:mm TT", null, null, self::LOCALE_EN),
            'en custom'  => array(
                "MM d, yy h:mm:ss TT",
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::MEDIUM,
                self::LOCALE_EN
            ),
            'ru default' => array("dd.mm.yy H:mm", null, null, self::LOCALE_RU),
            'ru custom'  => array(
                "d MM yy г. H:mm:ss",
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::MEDIUM,
                self::LOCALE_RU
            ),
        );
    }
}
