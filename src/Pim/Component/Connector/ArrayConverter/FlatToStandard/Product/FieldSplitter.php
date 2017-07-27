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
            preg_match_all('/
                (?P<prices>
                    (-?[a-z0-9]+)  # int or blank (if there is no price defined)
                    (?:[^0-9]\d+)? # decimal separator and decimal
                    [a-z\s]+       # currency
                )/ix', $value, $matches);

            if (empty($matches['prices'])) {
                if (!is_array($value)) {
                    return [$value];
                }

                return $value;
            }

            $prices = $matches['prices'];
            array_walk($prices, function (&$price) {
                $price = trim($price);
            });
        }

        return $prices;
    }
}
