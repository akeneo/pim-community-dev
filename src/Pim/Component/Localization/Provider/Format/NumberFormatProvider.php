<?php

namespace Pim\Component\Localization\Provider\Format;

/**
 * Provides information about the number format for a locale
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberFormatProvider implements FormatProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFormat($locale)
    {
        $number = new \NumberFormatter($locale, \NumberFormatter::DECIMAL);

        return [
            'decimal_separator' => $number->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL)
        ];
    }
}
