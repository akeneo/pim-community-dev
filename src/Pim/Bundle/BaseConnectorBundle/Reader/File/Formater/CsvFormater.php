<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\File\Formater;

use Pim\Bundle\TransformBundle\Builder\FieldNameBuilder;

/**
 * Csv formater
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO: too "global" naming, content is product tinted, CSVToStandardConverter?
 */
class CsvFormater
{
    /** @var FieldNameBuilder */
    protected $fieldNameBuilder;

    /**
     * Constructor
     *
     * @param FieldNameBuilder $fieldNameBuilder
     */
    public function __construct(FieldNameBuilder $fieldNameBuilder)
    {
        $this->fieldNameBuilder = $fieldNameBuilder;
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
     * @param array $product Representing a flat product
     *
     * @return array structured product
     */
    public function convertToStructured(array $product)
    {
        $result = [];
        foreach ($product as $column => $value) {
            $value  = $this->convertToStructuredField($column, $value);
            $result = $this->addFieldToCollection($result, $value);

            // TODO: filter empty values, for instance a simple select with "" should not be kept as an update
            // TODO: does not work with media
            // TODO: does not work with no groups
        }

        return $result;
    }

    /**
     * Convert a flat field to a structured one
     * @param string $column The column name
     * @param string $value  The value in the cell
     *
     * @return array
     */
    protected function convertToStructuredField($column, $value)
    {
        $associationFields = $this->fieldNameBuilder->getAssociationFieldNames();

        if (in_array($column, $associationFields)) {
            $value = FieldNameBuilder::splitCollection($value);
            list($associationTypeCode, $associatedWith) = FieldNameBuilder::splitFieldName($column);

            return ['associations' => [$associationTypeCode => [$associatedWith => $value]]];
        } elseif (in_array($column, ['categories', 'groups'])) {
            return [$column => FieldNameBuilder::splitCollection($value)];
        } elseif ('enabled' === $column) {
            return [$column => (bool) $value];
        } elseif ('family' === $column) {
            return [$column => $value];
        } else {
            return $this->formatValue($column, $value);
        }

        return [];
    }

    /**
     * Format a value cell
     * @param string $column The column name
     * @param string $value  The value in the cell
     *
     * @return structured value
     */
    protected function formatValue($column, $value)
    {
        $fieldNameInfos = $this->fieldNameBuilder->extractAttributeFieldNameInfos($column);

        $data = $this->formatValueData($value, $fieldNameInfos);

        return [$fieldNameInfos['attribute']->getCode() => [[
            'locale' => $fieldNameInfos['locale_code'],
            'scope'  => $fieldNameInfos['scope_code'],
            'data'   => $data,
        ]]];
    }

    /**
     * Format the value data of a cell into a structured format
     *
     * prices:      '10 EUR, 24 USD'   => [{'data': '10', 'currency': 'EUR'}, {'data': '24', 'currency': 'USD'}]
     * metric:      '10 METER'         => {'data': 10, 'unit': 'METER'}
     * multiselect: 'red, blue, black' => ['red', 'blue', 'black']
     *
     * @param string $value          The value content
     * @param array  $fieldNameInfos The field informations
     *
     * @return array
     */
    protected function formatValueData($value, $fieldNameInfos)
    {
        switch ($fieldNameInfos['attribute']->getAttributeType()) {
            case 'pim_catalog_price_collection':
                $value = FieldNameBuilder::splitCollection($value);

                $value = array_map(function ($priceValue) use ($fieldNameInfos) {
                    return $this->formatPrice($priceValue, $fieldNameInfos);
                }, $value);
                break;
            case 'pim_catalog_metric':
                $value = $this->formatMetric($value, $fieldNameInfos);
                break;
            case 'pim_catalog_multiselect':
                $value = FieldNameBuilder::splitCollection($value);
                break;
            case 'pim_catalog_simpleselect':
                $value = $value === "" ? null : $value;
                break;
            case 'pim_catalog_boolean':
                $value = (bool) $value;
                break;
            case 'pim_catalog_number':
                $value = (float) $value;
                break;
            case 'pim_catalog_image':
            case 'pim_catalog_file':
                $value = [
                    'filePath'         => $value,
                    'originalFilename' => basename($value)
                ];

                break;
        }

        return $value;
    }

    /**
     * Format price cell
     * @param string $value          The value content
     * @param array  $fieldNameInfos The field informations
     *
     * @return array
     */
    protected function formatPrice($value, $fieldNameInfos)
    {
        if ('' === $value) {
            return [];
        }

        //Due to the multiple column for price collections
        if (isset($fieldNameInfos['price_currency'])) {
            $currency = $fieldNameInfos['price_currency'];
        } else {
            list($value, $currency) = FieldNameBuilder::splitUnitValue($value);
        }

        return ['data' => (float) $value, 'currency' => $currency];
    }

    /**
     * Format metric cell
     * @param string $value          The value content
     * @param array  $fieldNameInfos The field informations
     *
     * @return array
     */
    protected function formatMetric($value, $fieldNameInfos)
    {
        if ('' === $value) {
            return ['data' => null, 'unit' => null];
        }

        //Due to the multi column format for metrics
        if (isset($fieldNameInfos['metric_unit'])) {
            $unit = $fieldNameInfos['metric_unit'];
        } else {
            list($value, $unit) = FieldNameBuilder::splitUnitValue($value);
        }

        return ['data' => (float) $value, 'unit' => $unit];
    }

    /**
     * Method to make the array_merge_recursive more "smart" for price collections
     *
     * @param array $collection The collection in which we add the element
     * @param array $value      The structured value to add to the collection
     *
     * @return array
     */
    protected function addFieldToCollection($collection, $value)
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
