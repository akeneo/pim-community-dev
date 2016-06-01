<?php

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter;

use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldSplitter;

/**
 * Converts flat price into structured one.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceConverter extends AbstractValueConverter
{
    /**
     * @param FieldSplitter $fieldSplitter
     * @param array         $supportedFieldType
     */
    public function __construct(FieldSplitter $fieldSplitter, array $supportedFieldType)
    {
        parent::__construct($fieldSplitter);

        $this->supportedFieldType = $supportedFieldType;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $attributeFieldInfo, $value)
    {
        if ('' !== $value) {
            $data = $this->fieldSplitter->splitPrices($value);
        } else {
            $data = [];
        }

        $data = array_map(function ($priceValue) {
            return $this->convertPrice($priceValue);
        }, $data);

        return [$attributeFieldInfo['attribute']->getCode() => [[
            'locale' => $attributeFieldInfo['locale_code'],
            'scope'  => $attributeFieldInfo['scope_code'],
            'data'   => $data,
        ]]];
    }

    /**
     * @param string $value
     *
     * @return array
     */
    protected function convertPrice($value)
    {
        if ('' === $value) {
            $priceValue = null;
            $currency = null;
        } else {
            $tokens = $this->fieldSplitter->splitUnitValue($value);
            if (1 === count($tokens)) {
                $priceValue = null;
                $currency = $value;
            } else {
                $priceValue = isset($tokens[0]) ? $tokens[0] : null;
                $currency = isset($tokens[1]) ? $tokens[1] : null;
            }
        }

        return ['data' => $priceValue, 'currency' => $currency];
    }
}
