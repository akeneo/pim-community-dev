<?php

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard\Product;

use Pim\Component\Connector\ArrayConverter\FieldSplitter as BaseFieldSplitter;

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
            // Replace commas between prices with semicolons (excluding commas between numbers)
            $matches = preg_replace('/([a-z]+),/ixm', '\1;', $value);

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
