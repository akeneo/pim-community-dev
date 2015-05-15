<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Bundle\CatalogBundle\Manager\AttributeValuesResolver;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;
use Pim\Bundle\TransformBundle\Builder\FieldNameBuilder;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Product Flat Converter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductToStandardConverter implements StandardArrayConverterInterface
{
    /** @var FieldNameBuilder */
    protected $fieldNameBuilder;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var CurrencyRepositoryInterface */
    protected $currencyRepository;

    /** @var AttributeValuesResolver */
    protected $valuesResolver;

    /** @var array */
    protected $optionalAttributeFields;

    /** @var array */
    protected $optionalAssociationFields;

    /**
     * @param FieldNameBuilder             $fieldNameBuilder
     * @param AttributeRepositoryInterface $attributeRepository
     * @param CurrencyRepositoryInterface  $currencyRepository
     * @param AttributeValuesResolver      $valuesResolver
     */
    public function __construct(
        FieldNameBuilder $fieldNameBuilder,
        AttributeRepositoryInterface $attributeRepository,
        CurrencyRepositoryInterface  $currencyRepository,
        AttributeValuesResolver $valuesResolver
    ) {
        $this->fieldNameBuilder = $fieldNameBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->currencyRepository = $currencyRepository;
        $this->valuesResolver = $valuesResolver;
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
        $optionResolver = $this->createOptionsResolver();
        $resolvedItem = $optionResolver->resolve($item);

        $result = [];
        foreach ($resolvedItem as $column => $value) {
            $value = $this->convertToStructuredField($column, $value);
            $result = $this->addFieldToCollection($result, $value);

            // TODO: filter empty values, for instance a simple select with "" should not be kept as an update
            // TODO: does not work with media
            // TODO: does not work with no groups
        }

        return $result;
    }

    /**
     * Convert a flat field to a structured one
     *
     * @param string $column The column name
     * @param string $value  The value in the cell
     *
     * @return array
     */
    protected function convertToStructuredField($column, $value)
    {
        $associationFields = $this->fieldNameBuilder->getAssociationFieldNames();

        if (in_array($column, $associationFields)) {
            $value = $this->splitCollection($value);
            list($associationTypeCode, $associatedWith) = $this->splitFieldName($column);

            return ['associations' => [$associationTypeCode => [$associatedWith => $value]]];
        } elseif (in_array($column, ['categories', 'groups'])) {
            return [$column => $this->splitCollection($value)];
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
     *
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
                $value = $this->splitCollection($value);

                $value = array_map(function ($priceValue) use ($fieldNameInfos) {
                    return $this->formatPrice($priceValue, $fieldNameInfos);
                }, $value);
                break;
            case 'pim_catalog_metric':
                $value = $this->formatMetric($value, $fieldNameInfos);
                break;
            case 'pim_catalog_multiselect':
                $value = $this->splitCollection($value);
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
                $value = $value === "" ? null : [
                    'filePath'         => $value,
                    'originalFilename' => basename($value)
                ];

                break;
        }

        return $value;
    }

    /**
     * Format price cell
     *
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
            list($value, $currency) = $this->splitUnitValue($value);
        }

        return ['data' => (float) $value, 'currency' => $currency];
    }

    /**
     * Format metric cell
     *
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
            list($value, $unit) = $this->splitUnitValue($value);
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

    /**
     * @return OptionsResolverInterface
     */
    protected function createOptionsResolver()
    {
        $resolver = new OptionsResolver();

        $required = ['family', 'enabled', 'categories', 'groups'];
        $defaults = ['enabled' => 1];
        $allowedTypes = [
            'family' => 'string',
            'enabled' => 'bool',
            'categories' => 'string',
            'groups' => 'string'
        ];
        $optional = array_merge($this->getOptionalAttributeFields(), $this->getOptionalAssociationFields());

        $resolver->setRequired($required);
        $resolver->setOptional($optional);
        $resolver->setDefaults($defaults);
        $resolver->setAllowedTypes($allowedTypes);
        $booleanNormalizer = function ($options, $value) {
            return (bool) $value;
        };
        $resolver->setNormalizers(['enabled' => $booleanNormalizer]);

        return $resolver;
    }

    /**
     * @return array
     *
     * TODO: extract in a FieldNameBuilder / refactor the existing one
     */
    protected function getOptionalAttributeFields()
    {
        if (empty($this->optionalAttributeFields)) {
            $attributes = $this->attributeRepository->findAll();
            $currencyCodes = $this->currencyRepository->getActivatedCurrencyCodes();
            $values = $this->valuesResolver->resolveEligibleValues($attributes);
            foreach ($values as $value) {
                if ($value['locale'] !== null && $value['scope'] !== null) {
                    $field = sprintf(
                        '%s-%s-%s',
                        $value['attribute'],
                        $value['locale'],
                        $value['scope']
                    );
                } elseif ($value['locale'] !== null) {
                    $field = sprintf(
                        '%s-%s',
                        $value['attribute'],
                        $value['locale']
                    );
                } elseif ($value['scope'] !== null) {
                    $field = sprintf(
                        '%s-%s',
                        $value['attribute'],
                        $value['scope']
                    );
                } else {
                    $field = $value['attribute'];
                }

                if ('pim_catalog_price_collection' === $value['type']) {
                    $this->optionalAttributeFields[] = $field;
                    foreach ($currencyCodes as $currencyCode) {
                        $currencyField = sprintf('%s-%s', $field, $currencyCode);
                        $this->optionalAttributeFields[] = $currencyField;
                    }
                } elseif ('pim_catalog_metric' === $value['type']) {
                    $this->optionalAttributeFields[] = $field;
                    $metricField = sprintf('%s-%s', $field, 'unit');
                    $this->optionalAttributeFields[] = $metricField;
                } else {
                    $this->optionalAttributeFields[] = $field;
                }
            }
        }

        return $this->optionalAttributeFields;
    }

    /**
     * @return array
     *
     * TODO: extract in a FieldNameBuilder / refactor the existing one
     */
    protected function getOptionalAssociationFields()
    {
        if (empty($this->optionalAssociationFields)) {
            $this->optionalAssociationFields = $this->fieldNameBuilder->getAssociationFieldNames();
        }

        return $this->optionalAssociationFields;
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
    protected function splitCollection($value)
    {
        return '' === $value ? [] : explode(FieldNameBuilder::ARRAY_SEPARATOR, $value);
    }

    /**
     * Split a field name:
     * 'description-en_US-mobile' => ['description', 'en_US', 'mobile']
     *
     * @param string $field Raw field name
     *
     * @return array
     */
    protected function splitFieldName($field)
    {
        return '' === $field ? [] : explode(FieldNameBuilder::FIELD_SEPARATOR, $field);
    }

    /**
     * Split a value with it's unit/currency:
     * '10 EUR'   => ['10', 'EUR']
     * '10 METER' => ['10', 'METER']
     *
     * @param string $value Raw value
     *
     * @return array
     */
    protected function splitUnitValue($value)
    {
        return '' === $value ? [] : explode(FieldNameBuilder::UNIT_SEPARATOR, $value);
    }
}
