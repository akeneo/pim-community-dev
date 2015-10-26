<?php

namespace Pim\Component\Localization\Formatter;

/**
 * The FormatterInterface formats values according to locales.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FormatterInterface
{
    /**
     * @param string $value
     *
     * @return string
     */
    public function format($value);
}
