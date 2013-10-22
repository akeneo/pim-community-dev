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
            'default default' => array("M d, yy"),
            'default custom'  => array("MM d, yy", null, \IntlDateFormatter::LONG),
            'en default'      => array("M d, yy", self::LOCALE_EN),
            'en custom'       => array("MM d, yy", self::LOCALE_EN, \IntlDateFormatter::LONG),
            'ru default'      => array("dd.mm.yy", self::LOCALE_RU),
            'ru custom'       => array("d MM yy г.", self::LOCALE_RU, \IntlDateFormatter::LONG),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeFormatDataProvider()
    {
        return array(
            'default default' => array("h:mm TT"),
            'default custom'  => array("h:mm:ss TT", null, \IntlDateFormatter::MEDIUM),
            'en default'      => array("h:mm TT", self::LOCALE_EN),
            'en custom'       => array("h:mm:ss TT", self::LOCALE_EN, \IntlDateFormatter::MEDIUM),
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
            'default default' => array("M d, yy h:mm TT"),
            'default custom'  => array(
                "MM d, yy h:mm:ss TT",
                null,
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::MEDIUM
            ),
            'en default' => array("M d, yy h:mm TT", self::LOCALE_EN),
            'en custom'  => array(
                "MM d, yy h:mm:ss TT",
                self::LOCALE_EN,
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::MEDIUM
            ),
            'ru default' => array("dd.mm.yy H:mm", self::LOCALE_RU),
            'ru custom'  => array(
                "d MM yy г. H:mm:ss",
                self::LOCALE_RU,
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::MEDIUM
            ),
        );
    }
}
