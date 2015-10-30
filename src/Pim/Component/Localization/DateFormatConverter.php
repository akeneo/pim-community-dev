<?php

namespace Pim\Component\Localization;

/**
 * The DateFormatConverter provides services to convert ICU date formats in a format usable by PHP dates functions.
 *
 * @see http://php.net/manual/fr/function.date.php
 * @see http://userguide.icu-project.org/formatparse/datetime
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateFormatConverter
{
    /** @var array */
    protected $formatMatch = [
        'yyyy'   => 'Y',
        'yy'     => 'y',
        'y'      => 'Y',
        'MMMM'   => 'F',
        'MMM'    => 'M',
        'MM'     => 'm',
        'M'      => 'n',
        'LLLL'   => 'F',
        'LLL'    => 'M',
        'LL'     => 'm',
        'L'      => 'n',
        'ww'     => 'W',
        'w'      => 'W',
        'dd'     => 'd',
        'd'      => 'j',
        'D'      => 'z',
        'EEEE'   => 'l',
        'EEE'    => 'D',
        'EE'     => 'D',
        'E'      => 'D',
        'eeee'   => 'l',
        'eee'    => 'D',
        'ee'     => 'N',
        'e'      => 'N',
        'cccc'   => 'l',
        'ccc'    => 'D',
        'cc'     => 'N',
        'c'      => 'N',
        'a'      => 'a',
        'hh'     => 'h',
        'h'      => 'g',
        'HH'     => 'H',
        'H'      => 'G',
        'mm'     => 'i',
        'ss'     => 's',
        'zzz'    => 'e',
        'zz'     => 'e',
        'z'      => 'e',
        'ZZZ'    => 'O',
        'ZZ'     => 'O',
        'Z'      => 'O',
        'vvvv'   => '',
        'v'      => 'T',
        'VV'     => 'e',
        'xxx'    => 'P',
    ];

    /**
     * Convert an ICU date format to format usable by PHP dates functions.
     *
     * @param string $format
     *
     * @return string
     */
    public function convert($format)
    {
        return strtr($format, $this->formatMatch);
    }
}
