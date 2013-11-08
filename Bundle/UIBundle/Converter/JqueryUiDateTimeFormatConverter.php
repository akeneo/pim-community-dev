<?php

namespace Oro\Bundle\UIBundle\Converter;

use Oro\Bundle\LocaleBundle\Converter\AbstractDateTimeFormatConverter;

class JqueryUiDateTimeFormatConverter extends AbstractDateTimeFormatConverter
{
    const NAME = 'jquery_ui';

    /**
     * ICU format => JQueryUI format
     *
     * http://userguide.icu-project.org/formatparse/datetime
     * http://api.jqueryui.com/datepicker/#utility-formatDate
     * http://trentrichardson.com/examples/timepicker/#tp-formatting
     *
     * @var array
     */
    protected $formatMatch = array(
        'yyyy'  => 'yy', // long year
        'yy'    => 'y',  // short year
        'y'     => 'yy', // long year
        'Y'     => 'yy', // year of "Week of Year"
        'MMMMM' => 'M',  // short month name
        'MMMM'  => 'MM', // long month name
        'MMM'   => 'M',  // short month name,
        'MM'    => 'mm', // month of year (two digit)
        'M'     => 'm',  // month of year (no leading zero)
        'LLLLL' => 'M',  // short month name
        'LLLL'  => 'MM', // long month name
        'LLL'   => 'M',  // short month name,
        'LL'    => 'mm', // month of year (two digit)
        'L'     => 'm',  // month of year (no leading zero)
        'dd'    => 'dd', // day of month (two digit)
        'd'     => 'd',  // day of month (no leading zero)
        'D'     => 'o',  // day of the year (no leading zeros)
        'a'     => 'TT', // am or pm for AM/PM
        'hh'    => 'hh', // hour with leading 0 (12 hour)
        'h'     => 'h',  // hour with no leading 0 (12 hour)
        'KK'    => 'hh', // hour with leading 0 (12 hour)
        'K'     => 'h',  // hour with no leading 0 (12 hour)
        'HH'    => 'HH', // hour with leading 0 (24 hour)
        'H'     => 'H',  // hour with no leading 0 (24 hour)
        'kk'    => 'HH', // hour with leading 0 (24 hour)
        'k'     => 'H',  // hour with no leading 0 (24 hour)
        'mm'    => 'mm', // minute with leading 0
        'm'     => 'm',  // minute with no leading 0
        'ss'    => 'ss', // second with leading 0
        's'     => 's',  // second with no leading 0
        'SSSS'  => 'l',  // milliseconds always with leading 0
        'SSS'   => 'l',  // milliseconds always with leading 0,
        'ZZZ'   => 'Z',  // timezone in Iso 8601 format (+04:45)
        'ZZ'    => 'Z',  // timezone in Iso 8601 format (+04:45)
        'Z'     => 'Z',  // timezone in Iso 8601 format (+04:45)
        'VVVV'  => 'z',  // timezone as defined by timezoneList
        'VVV'   => 'z',  // timezone as defined by timezoneList
        'VV'    => 'z',  // timezone as defined by timezoneList
        'V'     => 'z',  // timezone as defined by timezoneList
    );

    /**
     * {@inheritDoc}
     */
    protected function convertFormat($format)
    {
        return str_replace(array('"', '\''), '', parent::convertFormat($format));
    }
}
