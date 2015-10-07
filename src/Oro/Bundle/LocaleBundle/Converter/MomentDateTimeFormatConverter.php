<?php

namespace Oro\Bundle\LocaleBundle\Converter;

class MomentDateTimeFormatConverter extends AbstractDateTimeFormatConverter
{
    const NAME = 'moment';

    /**
     * ICU format => moment.js data
     *
     * http://userguide.icu-project.org/formatparse/datetime
     * http://momentjs.com/docs/#/displaying/format/
     *
     * @var array
     */
    protected $formatMatch = array(
        'yyyy'   => 'YYYY',
        'yy'     => 'YY',
        'y'      => 'YYYY',
        'MMMM'   => 'MMMM',
        'MMM'    => 'MMM',
        'MM'     => 'MM',
        'M'      => 'M',
        'ww'     => 'ww',
        'w'      => 'w',
        'dd'     => 'DD',
        'd'      => 'D',
        'D'      => 'DDD',
        'EEEEEE' => 'dd',
        'EEEE'   => 'dddd',
        'EEE'    => 'ddd',
        'EE'     => 'ddd',
        'E'      => 'ddd',
        'eeeeee' => 'dd',
        'eeee'   => 'dddd',
        'eee'    => 'ddd',
        'ee'     => 'e',
        'e'      => 'e',
        'cccccc' => 'dd',
        'cccc'   => 'dddd',
        'ccc'    => 'ddd',
        'cc'     => 'E',
        'c'      => 'E',
        'a'      => 'A',
        'hh'     => 'hh',
        'h'      => 'h',
        'HH'     => 'HH',
        'H'      => 'H',
        'kk'     => 'HH',
        'k'      => 'H',
        'KK'     => 'hh',
        'K'      => 'h',
        'mm'     => 'mm',
        'm'      => 'm',
        'ss'     => 'ss',
        's'      => 's',
        'SSS'    => 'SSS',
        'SS'     => 'SS',
        'S'      => 'S',
        'ZZZZZ'  => 'Z',
        'ZZZZ'   => 'ZZ',
        'ZZZ'    => 'ZZ',
        'ZZ'     => 'ZZ',
        'Z'      => 'ZZ',
    );

    /**
     * {@inheritDoc}
     */
    protected function convertFormat($format)
    {
        return preg_replace('~[\'"](.*?)[\'"]~', '[$1]', parent::convertFormat($format));
    }
}
