<?php

namespace Pim\Component\Localization\Provider;

/**
 * The DateFormatProviderInterface provides localized date formats.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DateFormatProviderInterface
{
    /**
     * Get a date format from a locale.
     *
     * @param string $locale
     *
     * @return string
     */
    public function getDateFormat($locale);
}
