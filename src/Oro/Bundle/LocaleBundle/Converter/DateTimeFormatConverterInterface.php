<?php

namespace Oro\Bundle\LocaleBundle\Converter;

interface DateTimeFormatConverterInterface
{
    /**
     * @param int|string|null $dateFormat \IntlDateFormatter format constant it's string name
     * @param string|null $locale
     * @return string
     */
    public function getDateFormat($dateFormat = null, $locale = null);

    /**
     * @param int|string|null $timeFormat \IntlDateFormatter format constant or it's string name
     * @param string|null $locale
     * @return string
     */
    public function getTimeFormat($timeFormat = null, $locale = null);

    /**
     * @param int|string|null $dateFormat \IntlDateFormatter format constant it's string name
     * @param int|string|null $timeFormat \IntlDateFormatter format constant it's string name
     * @param string|null $locale
     * @return string
     */
    public function getDateTimeFormat($dateFormat = null, $timeFormat = null, $locale = null);
}
