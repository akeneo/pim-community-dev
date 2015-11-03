<?php

namespace Pim\Component\Localization\Provider;

/**
 * The FormatProviderInterface provides localized formats.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FormatProviderInterface
{
    /**
     * Get a format from a locale.
     *
     * @param string $locale
     *
     * @return mixed
     */
    public function getFormat($locale);
}
