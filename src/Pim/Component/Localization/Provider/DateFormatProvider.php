<?php

namespace Pim\Component\Localization\Provider;

use Pim\Component\Localization\DateFormatConverter;

/**
 * The DateFormatProvider provides localized date formats usable by PHP date functions.
 * A set of date formats can be passed as argument in the instance construction.
 * If the date format does not belong to this set, it returns default date format provided by ICU library.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateFormatProvider implements DateFormatProviderInterface
{
    /** @var DateFormatConverter */
    protected $converter;

    /** @var array */
    protected $dateFormats;

    /**
     * @param DateFormatConverter $converter
     * @param array               $dateFormats
     */
    public function __construct(DateFormatConverter $converter, array $dateFormats)
    {
        $this->converter   = $converter;
        $this->dateFormats = $dateFormats;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateFormat($locale)
    {
        if (isset($this->dateFormats[$locale])) {
            return $this->dateFormats[$locale];
        }

        $formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE);
        $icuFormat = $formatter->getPattern();

        return $this->converter->convert($icuFormat);
    }
}
