<?php

namespace Oro\Bundle\LocaleBundle\Converter;

interface DateTimeFormatConverterInterface
{
    /**
     * @param string|null $locale
     * @param int|null $dateFormat \IntlDateFormatter format constant
     * @return string
     */
    public function getDateFormat($locale = null, $dateFormat = null);

    /**
     * @param string|null $locale
     * @param int|null $timeFormat \IntlDateFormatter format constant
     * @return string
     */
    public function getTimeFormat($locale = null, $timeFormat = null);

    /**
     * @param string|null $locale
     * @param int|null $dateFormat \IntlDateFormatter format constant
     * @param int|null $timeFormat \IntlDateFormatter format constant
     * @return string
     */
    public function getDateTimeFormat($locale = null, $dateFormat = null, $timeFormat = null);
}
