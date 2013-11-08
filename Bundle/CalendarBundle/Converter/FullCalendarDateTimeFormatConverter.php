<?php

namespace Oro\Bundle\CalendarBundle\Converter;

use Oro\Bundle\LocaleBundle\Converter\AbstractDateTimeFormatConverter;

class FullCalendarDateTimeFormatConverter extends AbstractDateTimeFormatConverter
{
    const NAME = 'fullcalendar';

    /**
     * ICU format => fullcalendar.js data
     *
     * http://userguide.icu-project.org/formatparse/datetime
     * http://arshaw.com/fullcalendar/docs/utilities/formatDate/
     *
     * @var array
     */
    protected $formatMatch = array(
        'yyyy'   => 'yyyy',
        'yy'     => 'yy',
        'y'      => 'yyyy',
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
}
