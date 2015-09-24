<?php

namespace Pim\Component\Connector\ArrayConverter\Flat\Product;

/**
 * Split fields
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FieldSplitter
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
     * '10 EUR, 24 USD' => ['10 EUR', '24 USD']
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
     * Split a field name:
     * 'description-en_US-mobile' => ['description', 'en_US', 'mobile']
     *
     * @param string $field Raw field name
     *
     * @return array
     */
    public function splitFieldName($field)
    {
        return '' === $field ? [] : explode(AttributeColumnInfoExtractor::FIELD_SEPARATOR, $field);
    }
}
