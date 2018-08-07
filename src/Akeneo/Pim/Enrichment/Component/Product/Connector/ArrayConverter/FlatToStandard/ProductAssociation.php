<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;

/**
 * Product association converter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociation implements ArrayConverterInterface
{
    /** @var ArrayConverterInterface */
    protected $productConverter;

    /**
     * @param ArrayConverterInterface $productConverter
     */
    public function __construct(ArrayConverterInterface $productConverter)
    {
        $this->productConverter = $productConverter;
    }

    /**
     * {@inheritdoc}
     *
     * Convert flat array to structured array by keeping only identifier and associations
     *
     * Before:
     * [
     *     'sku': 'MySku',
     *     'name-fr_FR': 'T-shirt super beau',
     *     'description-en_US-mobile': 'My description',
     *     'price': '10 EUR, 24 USD',
     *     'price-CHF': '20',
     *     'length': '10 CENTIMETER',
     *     'enabled': '1',
     *     'categories': 'tshirt,men'
     *     'XSELL-groups': 'akeneo_tshirt, oro_tshirt',
     *     'XSELL-product': 'AKN_TS, ORO_TSH'
     * ]
     *
     * After:
     * {
     *      "identifier": "MySku",
     *      "associations": {
     *          "XSELL": {
     *              "groups": ["akeneo_tshirt", "oro_tshirt"],
     *              "products": ["AKN_TS", "ORO_TSH"]
     *          }
     *      }
     * }
     */
    public function convert(array $item, array $options = [])
    {
        $convertedItem = $this->productConverter->convert($item, $options);
        $filteredItem = $this->filter($convertedItem);

        return $filteredItem;
    }

    /**
     * Filters the item to keep only association related fields
     *
     * @param array $item
     *
     * @return array
     */
    protected function filter(array $item)
    {
        $expectedFields = ['identifier', 'associations'];
        foreach (array_keys($item) as $fieldName) {
            if (!in_array($fieldName, $expectedFields)) {
                unset($item[$fieldName]);
            }
        }

        return $item;
    }
}
