<?php

namespace Pim\Component\Connector\ArrayConverter\Flat\Product\Converter;

use Pim\Component\Connector\ArrayConverter\Flat\Product\Splitter\FieldSplitter;

/**
 * Converts flat price into structured one
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceConverter extends AbstractConverter
{
    /**
     * @param FieldSplitter $fieldSplitter
     * @param array         $supportedFieldType
     */
    public function __construct(
        FieldSplitter $fieldSplitter,
        array $supportedFieldType
    ) {
        parent::__construct($fieldSplitter);
        $this->supportedFieldType = $supportedFieldType;
    }

    /**
     * {@inheritdoc}
     */
    public function convert($fieldNameInfo, $value)
    {
        if ('' === $value) {
            return null;
        }

        $data = $this->fieldSplitter->splitCollection($value);

        $data = array_map(function ($priceValue) use ($fieldNameInfo) {
            return $this->convertPrice($fieldNameInfo, $priceValue);
        }, $data);

        return [$fieldNameInfo['attribute']->getCode() => [[
            'locale' => $fieldNameInfo['locale_code'],
            'scope'  => $fieldNameInfo['scope_code'],
            'data'   => $data,
        ]]];
    }

    /**
     * @param string $priceValue
     * @param array  $fieldNameInfo
     *
     * @return array
     */
    protected function convertPrice(array $fieldNameInfo, $priceValue)
    {
        //Due to the multiple column for price collections
        if (isset($fieldNameInfo['price_currency'])) {
            $currency = $fieldNameInfo['price_currency'];
        } else {
            list($priceValue, $currency) = $this->fieldSplitter->splitUnitValue($priceValue);
        }

        return ['data' => (float) $priceValue, 'currency' => $currency];
    }
}
