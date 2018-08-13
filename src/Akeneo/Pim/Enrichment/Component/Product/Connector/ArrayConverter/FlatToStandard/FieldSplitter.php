<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\FieldSplitter as BaseFieldSplitter;

/**
 * Split fields
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FieldSplitter extends BaseFieldSplitter
{
    /**
     * Split a value with it's unit/currency:
     * '10 EUR'   => ['10', 'EUR']
     * '10 METER' => ['10', 'METER']
     *
     * @param string $value Raw value
     *
     * @return array
     */
    public function splitUnitValue($value)
    {
        return '' === $value ? [] : explode(AttributeColumnInfoExtractor::UNIT_SEPARATOR, $value);
    }

    /**
     * Split a collection in a flat value :
     *
     * 'boots, sandals' => ['boots', 'sandals']
     *
     * @param string $value Raw value
     *
     * @return array
     */
    public function splitCollection($value)
    {
        $tokens = [];
        if ('' !== $value) {
            $tokens = explode(AttributeColumnInfoExtractor::ARRAY_SEPARATOR, $value);
            array_walk($tokens, function (&$token) {
                $token = trim($token);
            });
        }

        return $tokens;
    }

    /**
     * Split a price collection in a flat value :
     *
     * '10 EUR, 24 USD' => ['10 EUR', '24 USD']
     *
     * @param string $value Raw value
     *
     * @return array
     */
    public function splitPrices($value)
    {
        $prices = [];
        if ('' !== $value) {
            // Strip quotation marks
            $cleanedValue = preg_replace('/["]/', '', $value);

            // Replace these types of commas with semicolon:
            // Commas after currency type: 'EUR, ...'
            // Commas between numbers and currency symbols: '123.00, $199...'
            // Dots used as separators: '123,100 EUR.199 USD'
            $matches = preg_replace('/
                (?:,(?<=[a-z],)
                |(?=,?\s?\p{Sc}),)
                |(?:.(?<=[a-z]\.))
             /ixm', '\1;', $cleanedValue);

            // Get an array of values by exploding semicolon delimited values
            $prices = explode(';', $matches);

            if (empty($matches)) {
                if (!is_array($value)) {
                    return [$value];
                }

                return $value;
            }


            array_walk($prices, function (&$price) {
                $price = trim($price);
            });
        }

        return $prices;
    }
}
