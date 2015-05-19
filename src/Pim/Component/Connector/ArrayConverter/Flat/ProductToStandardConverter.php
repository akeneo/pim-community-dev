<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\Flat\Product\ConvertToStructuredField;
use Pim\Component\Connector\ArrayConverter\Flat\Product\OptionsResolverConverter;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;

/**
 * Product Flat Converter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO: rewrite this class, extract value conversion logic to make it extensible (tagged services) and add support
 * for reference data
 */
class ProductToStandardConverter implements StandardArrayConverterInterface
{
    // TODO: interface ?
    /** @var OptionsResolverConverter */
    protected $optionsResolverConverter;

    /** @var ConvertToStructuredField */
    protected $convertToStructuredField;

    /**
     * @param OptionsResolverConverter $optionsResolverConverter
     * @param ConvertToStructuredField $convertToStructuredField
     */
    public function __construct(
        OptionsResolverConverter $optionsResolverConverter,
        ConvertToStructuredField $convertToStructuredField
    ) {
        $this->optionsResolverConverter = $optionsResolverConverter;
        $this->convertToStructuredField = $convertToStructuredField;
    }

    /**
     * Convert flat array to structured array:
     *
     * Before:
     * [
     *     'description-en_US-mobile': 'My description',
     *     'name-fr_FR': 'T-shirt super beau',
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
     *      }],
     *      "enabled": true,
     *      "categories": ["tshirt", "men"],
     *      "associations": {
     *          "XSELL": {
     *              "groups": ["akeneo_tshirt", "oro_tshirt"],
     *              "product": ["AKN_TS", "ORO_TSH"]
     *          }
     *      }
     * }
     *
     * @param array $item Representing a flat product
     *
     * @return array structured $item
     */
    public function convert(array $item)
    {
        $resolvedItem = $this->optionsResolverConverter->resolveConverterOptions($item);

        $result = [];
        foreach ($resolvedItem as $column => $value) {
            $value = $this->convertToStructuredField->convert($column, $value);
            $result = $this->addFieldToCollection($result, $value);

            // TODO: filter empty values, for instance a simple select with "" should not be kept as an update
            // TODO: does not work with media
            // TODO: does not work with no groups
        }

        return $result;
    }

    /**
     * Method to make the array_merge_recursive more "smart" for price collections
     *
     * @param array $collection The collection in which we add the element
     * @param array $value      The structured value to add to the collection
     *
     * @return array
     */
    public function addFieldToCollection(array $collection, array $value)
    {
        $field = key($value);

        //Needed for prices collections in multiple columns
        if (isset($collection[$field]) &&
            isset($collection[$field][0]['data']) &&
            is_array($collection[$field][0]['data'])
        ) {
            $newFieldValue = reset($value[$field]);

            foreach ($collection[$field] as $key => $fieldValue) {
                if (array_key_exists('locale', $newFieldValue) &&
                    array_key_exists('scope', $newFieldValue) &&
                    $newFieldValue['locale'] === $fieldValue['locale'] &&
                    $newFieldValue['scope'] === $fieldValue['scope']
                ) {
                    $collection[$field][$key]['data'] = array_merge($fieldValue['data'], $newFieldValue['data']);
                }
            }
        } else {
            $collection = array_merge_recursive($collection, $value);
        }

        return $collection;
    }
}
