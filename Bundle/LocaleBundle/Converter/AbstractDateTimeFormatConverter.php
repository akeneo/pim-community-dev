<?php

namespace Oro\Bundle\LocaleBundle\Converter;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

abstract class AbstractDateTimeFormatConverter implements DateTimeFormatConverterInterface
{
    /**
     * @var LocaleSettings
     */
    protected $localeSettings;

    /**
     * ICU format => Default data
     *
     * http://userguide.icu-project.org/formatparse/datetime
     *
     * @var array
     */
    protected $defaultFormatMatch = array(
        'GGGGG'  => '',
        'GGGG'   => '',
        'GGG'    => '',
        'GG'     => '',
        'G'      => '',
        'yyyy'   => '',
        'yy'     => '',
        'y'      => '',
        'Y'      => '',
        'u'      => '',
        'U'      => '',
        'QQQQ'   => '',
        'QQQ'    => '',
        'QQ'     => '',
        'Q'      => '',
        'qqqq'   => '',
        'qqq'    => '',
        'qq'     => '',
        'q'      => '',
        'MMMMM'  => '',
        'MMMM'   => '',
        'MMM'    => '',
        'MM'     => '',
        'M'      => '',
        'LLLLL'  => '',
        'LLLL'   => '',
        'LLL'    => '',
        'LL'     => '',
        'L'      => '',
        'ww'     => '',
        'w'      => '',
        'W'      => '',
        'dd'     => '',
        'd'      => '',
        'D'      => '',
        'F'      => '',
        'g'      => '',
        'EEEEEE' => '',
        'EEEEE'  => '',
        'EEEE'   => '',
        'EEE'    => '',
        'EE'     => '',
        'E'      => '',
        'eeeeee' => '',
        'eeeee'  => '',
        'eeee'   => '',
        'eee'    => '',
        'ee'     => '',
        'e'      => '',
        'cccccc' => '',
        'ccccc'  => '',
        'cccc'   => '',
        'ccc'    => '',
        'cc'     => '',
        'c'      => '',
        'a'      => '',
        'hh'     => '',
        'h'      => '',
        'HH'     => '',
        'H'      => '',
        'kk'     => '',
        'k'      => '',
        'KK'     => '',
        'K'      => '',
        'mm'     => '',
        'm'      => '',
        'ss'     => '',
        's'      => '',
        'SSSS'   => '',
        'SSS'    => '',
        'SS'     => '',
        'S'      => '',
        'A'      => '',
        'zzzz'   => '',
        'zzz'    => '',
        'zz'     => '',
        'z'      => '',
        'ZZZZZ'  => '',
        'ZZZZ'   => '',
        'ZZZ'    => '',
        'ZZ'     => '',
        'Z'      => '',
        'OOOO'   => '',
        'O'      => '',
        'vvvv'   => '',
        'v'      => '',
        'VVVV'   => '',
        'VVV'    => '',
        'VV'     => '',
        'V'      => '',
        'XXXXX'  => '',
        'XXXX'   => '',
        'XXX'    => '',
        'XX'     => '',
        'X'      => '',
        'xxxxx'  => '',
        'xxxx'   => '',
        'xxx'    => '',
        'xx'     => '',
        'x'      => '',
    );

    /**
     * Property should be overridden in descendant classes
     *
     * @var array
     */
    protected $formatMatch = array();

    /**
     * @param LocaleSettings $localeSettings
     */
    public function __construct(LocaleSettings $localeSettings)
    {
        $this->localeSettings = $localeSettings;
    }

    /**
     * {@inheritDoc}
     */
    public function getDateFormat($locale = null, $dateFormat = null)
    {
        return $this->getFormat($locale, $dateFormat, \IntlDateFormatter::NONE);
    }

    /**
     * {@inheritDoc}
     */
    public function getTimeFormat($locale = null, $timeFormat = null)
    {
        return $this->getFormat($locale, \IntlDateFormatter::NONE, $timeFormat);
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeFormat($locale = null, $dateFormat = null, $timeFormat = null)
    {
        return $this->getFormat($locale, $dateFormat, $timeFormat);
    }

    /**
     * @param string|null $locale
     * @param int|null $dateFormat One of the constant of IntlDateFormatter: NONE, FULL, LONG, MEDIUM, SHORT
     * @param int|null $timeFormat One of the constant of IntlDateFormatter: NONE, FULL, LONG, MEDIUM, SHORT
     * @return string
     */
    protected function getFormat($locale, $dateFormat, $timeFormat)
    {
        if (null === $dateFormat) {
            $dateFormat = \IntlDateFormatter::MEDIUM;
        }

        if (null === $timeFormat) {
            $timeFormat = \IntlDateFormatter::SHORT;
        }

        if (!$locale) {
            $locale = $this->localeSettings->getLocale();
        }

        $format = $this->localeSettings->getDatePattern($locale, $dateFormat, $timeFormat);

        return $this->convertFormat($format);
    }

    /**
     * @param string $format
     * @return string
     */
    protected function convertFormat($format)
    {
        $formatMatch = array_merge($this->defaultFormatMatch, $this->formatMatch);

        return strtr($format, $formatMatch);
    }
}
