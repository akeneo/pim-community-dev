<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

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

        $data = array_map(function ($priceValue) use ($attributeFieldInfo) {
            return $this->convertPrice($priceValue, $attributeFieldInfo['attribute']);
        }, $data);

        return [$attributeFieldInfo['attribute']->getCode() => [[
            'locale' => $attributeFieldInfo['locale_code'],
            'scope'  => $attributeFieldInfo['scope_code'],
            'data'   => $data,
        ]]];
    }

    /**
     * @param string             $value
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    protected function convertPrice($value, AttributeInterface $attribute)
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
                if (isset($tokens[0])) {
                    $price = $tokens[0];
                    $priceValue = !$attribute->isDecimalsAllowed() && preg_match('|^\d+$|', $price) ?
                        (int) $price : (string) $price;
                } else {
                    $priceValue = null;
                }

                $currency = isset($tokens[1]) ? $tokens[1] : null;
            }
        }

        return ['amount' => $priceValue, 'currency' => $currency];
    }
}
