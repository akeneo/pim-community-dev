<?php

namespace Oro\Bundle\LocaleBundle\Converter;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class MomentDateTimeFormatConverter extends AbstractDateTimeFormatConverter
{
    const NAME = 'moment';

/*
s - seconds
ss - seconds, 2 digits
m - minutes
mm - minutes, 2 digits
h - hours, 12-hour format
hh - hours, 12-hour format, 2 digits
H - hours, 24-hour format
HH - hours, 24-hour format, 2 digits
d - date number
dd - date number, 2 digits
ddd - date name, short
dddd - date name, full
M - month number
MM - month number, 2 digits
MMM - month name, short
MMMM - month name, full
yy - year, 2 digits
yyyy - year, 4 digits
t - 'a' or 'p'
tt - 'am' or 'pm'
T - 'A' or 'P'
TT - 'AM' or 'PM'
u - ISO8601 format
S - 'st', 'nd', 'rd', 'th' for the date
W - the ISO8601 week number
*/

    /**
     * ICU format => moment.js data
     *
     * http://userguide.icu-project.org/formatparse/datetime
     * http://momentjs.com/docs/#/displaying/format/
     *
     * @var array
     */
    protected $formatMatch = array(
        'yyyy'   => 'yyyy',
        'yy'     => 'yy',
        'y'      => 'YYYY',
        'MMMM'   => 'MMMM',
        'MMM'    => 'MMM',
        'MM'     => 'MM',
        'M'      => 'M',
        'ww'     => 'W',
        'EEEEEE' => 'ddd',
        'EEEE'   => 'dddd',
        'EEE'    => 'ddd',
        'EE'     => 'ddd',
        'E'      => 'ddd',
        'eeeeee' => 'ddd',
        'eeee'   => 'dddd',
        'eee'    => 'ddd',
        'ee'     => 'ddd',
        'e'      => 'ddd',
        'cccccc' => 'dd',
        'cccc'   => 'dddd',
        'ccc'    => 'dd',
        'cc'     => 'dd',
        'c'      => 'dd',
        'a'      => 'TT',
        'HH'     => 'HH',
        'H'      => 'H',
        'hh'     => 'hh',
        'h'      => 'h',
        'ss'     => 'ss',
        's'      => 's',
        'mm'     => 'mm',
        'm'      => 'm',
        'dd'     => 'dd',
        'd'      => 'd',
    );

    /**
     * {@inheritDoc}
     */
    protected function convertFormat($format)
    {
        return parent::convertFormat($format);
        //return preg_replace('~[\'"](.*?)[\'"]~', '[$1]', parent::convertFormat($format));
    }
}
