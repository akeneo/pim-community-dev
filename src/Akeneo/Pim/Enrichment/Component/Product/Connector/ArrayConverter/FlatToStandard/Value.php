<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter\ValueConverterRegistryInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;

/**
 * Convert Product value from Flat to Standard structure.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Value implements ArrayConverterInterface
{
    /** @var ValueConverterRegistryInterface */
    protected $converterRegistry;

    /** @var AttributeColumnInfoExtractor */
    protected $attrFieldExtractor;

    /** @var ColumnsMerger */
    protected $columnsMerger;

    /**
     * @param AttributeColumnInfoExtractor    $attrFieldExtractor
     * @param ValueConverterRegistryInterface $converterRegistry
     * @param ColumnsMerger                   $columnsMerger
     */
    public function __construct(
        AttributeColumnInfoExtractor $attrFieldExtractor,
        ValueConverterRegistryInterface $converterRegistry,
        ColumnsMerger $columnsMerger
    ) {
        $this->attrFieldExtractor = $attrFieldExtractor;
        $this->converterRegistry = $converterRegistry;
        $this->columnsMerger = $columnsMerger;
    }

    /**
     * {@inheritdoc}
     *
     * Convert flat array to structured array:
     *
     * Before:
     * [
     *     'sku': 'MySku',
     *     'name-fr_FR': 'T-shirt super beau',
     *     'description-en_US-mobile': 'My description',
     *     'description-fr_FR-mobile': 'Ma description mobile',
     *     'description-en_US-ecommerce': 'My description for the website',
     *     'price': '10 EUR, 24 USD',
     *     'price-CHF': '20',
     *     'length': '10 CENTIMETER',
     * ]
     *
     * After:
     * {
     *      "sku": [{
     *          "locale": null,
     *          "scope":  null,
     *          "data":  "MySku",
     *      }],
     *      "name": [{
     *          "locale": "fr_FR",
     *          "scope":  null,
     *          "data":  "T-shirt super beau",
     *      }],
     *      "description": [
     *           {
     *               "locale": "en_US",
     *               "scope":  "mobile",
     *               "data":   "My description"
     *           },
     *           {
     *               "locale": "fr_FR",
     *               "scope":  "mobile",
     *               "data":   "Ma description mobile"
     *           },
     *           {
     *               "locale": "en_US",
     *               "scope":  "ecommerce",
     *               "data":   "My description for the website"
     *           },
     *      ],
     *      "price": [
     *           {
     *               "locale": null,
     *               "scope":  ecommerce,
     *               "data":   [
     *                   {"data": 10, "currency": "EUR"},
     *                   {"data": 24, "currency": "USD"},
     *                   {"data": 20, "currency": "CHF"}
     *               ]
     *           }
     *           {
     *               "locale": null,
     *               "scope":  mobile,
     *               "data":   [
     *                   {"data": 11, "currency": "EUR"},
     *                   {"data": 25, "currency": "USD"},
     *                   {"data": 21, "currency": "CHF"}
     *               ]
     *           }
     *      ],
     *      "length": [{
     *          "locale": "en_US",
     *          "scope":  "mobile",
     *          "data":   {"data": "10", "unit": "CENTIMETER"}
     *      }]
     * }
     */
    public function convert(array $values, array $options = [])
    {
        $mergedValues = $this->columnsMerger->merge($values);
        $convertedValues = [];

        foreach ($mergedValues as $column => $value) {
            $value = $this->convertValue($column, $value);
            $convertedValues = $this->mergeValueToItem($convertedValues, $value);
        }

        return $convertedValues;
    }

    /**
     * @param string $column
     * @param string $value
     *
     * @throws \LogicException
     *
     * @return array
     */
    protected function convertValue($column, $value)
    {
        $attributeFieldInfo = $this->attrFieldExtractor->extractColumnInfo($column);

        if (null !== $attributeFieldInfo && isset($attributeFieldInfo['attribute'])) {
            $converter = $this->converterRegistry->getConverter($attributeFieldInfo['attribute']->getType());

            if (null === $converter) {
                throw new \LogicException(
                    sprintf(
                        'No converters found for attribute type "%s"',
                        $attributeFieldInfo['attribute']->getType()
                    )
                );
            }

            return $converter->convert($attributeFieldInfo, $value);
        }

        throw new \LogicException(
            sprintf('Unable to convert the given column "%s"', $column)
        );
    }

    /**
     * Merge the structured value inside the passed item
     *
     * @param array $item   The item in which we add the element
     * @param array $value  The structured value to add to the item
     *
     * @return array
     */
    protected function mergeValueToItem(array $item, array $value)
    {
        if (empty($value)) {
            return $item;
        }

        foreach ($value as $code => $data) {
            if (array_key_exists($code, $item)) {
                $item[$code] = array_merge_recursive($item[$code], $data);
            } else {
                $item[$code] = $data;
            }
        }

        return $item;
    }
}
