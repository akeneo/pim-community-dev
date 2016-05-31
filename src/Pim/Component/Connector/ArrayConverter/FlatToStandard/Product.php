<?php

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AssociationColumnsResolver;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnInfoExtractor;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ColumnsMapper;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ColumnsMerger;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldConverter;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ValueConverter\ValueConverterRegistryInterface;
use Pim\Component\Connector\Exception\ArrayConversionException;

/**
 * Product Converter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Product implements ArrayConverterInterface
{
    /** @var ValueConverterRegistryInterface */
    protected $converterRegistry;

    /** @var AttributeColumnInfoExtractor */
    protected $attrFieldExtractor;

    /** @var AttributeColumnsResolver */
    protected $attrColumnsResolver;

    /** @var AssociationColumnsResolver */
    protected $assocColumnsResolver;

    /** @var FieldConverter */
    protected $fieldConverter;

    /** @var ColumnsMerger */
    protected $columnsMerger;

    /** @var ColumnsMapper */
    protected $columnsMapper;

    /** @var FieldsRequirementChecker */
    protected $fieldChecker;

    /** @var array */
    protected $optionalAssocFields;

    /**
     * @param AttributeColumnInfoExtractor    $attrFieldExtractor
     * @param ValueConverterRegistryInterface $converterRegistry
     * @param AssociationColumnsResolver      $assocColumnsResolver
     * @param AttributeColumnsResolver        $attrColumnsResolver
     * @param FieldConverter                  $fieldConverter
     * @param ColumnsMerger                   $columnsMerger
     * @param ColumnsMapper                   $columnsMapper
     * @param FieldsRequirementChecker        $fieldChecker
     */
    public function __construct(
        AttributeColumnInfoExtractor $attrFieldExtractor,
        ValueConverterRegistryInterface $converterRegistry,
        AssociationColumnsResolver $assocColumnsResolver,
        AttributeColumnsResolver $attrColumnsResolver,
        FieldConverter $fieldConverter,
        ColumnsMerger $columnsMerger,
        ColumnsMapper $columnsMapper,
        FieldsRequirementChecker $fieldChecker
    ) {
        $this->attrFieldExtractor   = $attrFieldExtractor;
        $this->converterRegistry    = $converterRegistry;
        $this->assocColumnsResolver = $assocColumnsResolver;
        $this->attrColumnsResolver  = $attrColumnsResolver;
        $this->fieldConverter       = $fieldConverter;
        $this->columnsMerger        = $columnsMerger;
        $this->columnsMapper        = $columnsMapper;
        $this->fieldChecker         = $fieldChecker;
        $this->optionalAssocFields  = [];
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
     *      }],
     *      "enabled": true,
     *      "categories": ["tshirt", "men"],
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
        $options = $this->prepareOptions($options);

        $mappedItem   = $this->mapFields($item, $options);
        $mappedItem   = $this->defineDefaultValues($mappedItem, $options['default_values']);
        $filteredItem = $this->filterFields($mappedItem, $options['with_associations']);
        $this->validateItem($filteredItem, $options['with_required_identifier']);

        $mergedItem    = $this->columnsMerger->merge($filteredItem);
        $convertedItem = $this->convertItem($mergedItem);

        return $convertedItem;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function prepareOptions(array $options)
    {
        $options['with_required_identifier'] = isset($options['with_required_identifier']) ?
            $options['with_required_identifier'] :
            true;
        $options['with_associations'] = isset($options['with_associations']) ? $options['with_associations'] : true;
        $options['default_values'] = isset($options['default_values']) ? $options['default_values'] : [];

        return $options;
    }

    /**
     * @param array $item
     * @param array $options
     *
     * @return array
     */
    protected function mapFields(array $item, array $options)
    {
        if (isset($options['mapping'])) {
            $item = $this->columnsMapper->map($item, $options['mapping']);
        }

        return $item;
    }

    /**
     * @param array $mappedItem
     * @param array $defaultValues
     *
     * @return array
     */
    protected function defineDefaultValues(array $mappedItem, array $defaultValues)
    {
        $enabled = (isset($defaultValues['enabled'])) ? (bool) $defaultValues['enabled'] : true;
        $mappedItem['enabled'] = isset($mappedItem['enabled']) ? (bool) $mappedItem['enabled'] : $enabled;

        return $mappedItem;
    }

    /**
     * @param array $mappedItem
     * @param bool  $withAssociations
     *
     * @return array
     */
    protected function filterFields(array $mappedItem, $withAssociations)
    {
        if (false === $withAssociations) {
            $isGroupAssPattern   = '/^\w+'.AssociationColumnsResolver::GROUP_ASSOCIATION_SUFFIX.'$/';
            $isProductAssPattern = '/^\w+'.AssociationColumnsResolver::PRODUCT_ASSOCIATION_SUFFIX.'$/';
            foreach (array_keys($mappedItem) as $field) {
                $isGroup = (1 === preg_match($isGroupAssPattern, $field));
                $isProduct = (1 === preg_match($isProductAssPattern, $field));
                if ($isGroup || $isProduct) {
                    unset($mappedItem[$field]);
                }
            }
        }

        return $mappedItem;
    }

    /**
     * @param array $item
     *
     * @return array
     */
    protected function convertItem(array $item)
    {
        $convertedItem = [];
        foreach ($item as $column => $value) {
            if ($this->fieldConverter->supportsColumn($column)) {
                $value = $this->fieldConverter->convert($column, $value);
            } else {
                $value = $this->convertValue($column, $value);
            }

            if (null !== $value) {
                $convertedItem = $this->mergeValueToResult($convertedItem, $value);
            }
        }

        return $convertedItem;
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
            $converter = $this->converterRegistry->getConverter($attributeFieldInfo['attribute']->getAttributeType());

            if (null === $converter) {
                throw new \LogicException(
                    sprintf(
                        'No converters found for attribute type "%s"',
                        $attributeFieldInfo['attribute']->getAttributeType()
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
     * Merge the structured value inside the passed collection
     *
     * @param array $collection The collection in which we add the element
     * @param array $value      The structured value to add to the collection
     *
     * @return array
     */
    protected function mergeValueToResult(array $collection, array $value)
    {
        foreach ($value as $code => $data) {
            if (array_key_exists($code, $collection)) {
                $collection[$code] = array_merge_recursive($collection[$code], $data);
            } else {
                $collection[$code] = $data;
            }
        }

        return $collection;
    }

    /**
     * @param array $item
     * @param bool  $withRequiredSku
     *
     * @throws ArrayConversionException
     */
    protected function validateItem(array $item, $withRequiredSku)
    {
        $requiredFields = $withRequiredSku ? [$this->attrColumnsResolver->resolveIdentifierField()] : [];
        $this->fieldChecker->checkFieldsPresence($item, $requiredFields);
        $this->validateOptionalFields($item);
        $this->validateFieldValueTypes($item);
    }

    /**
     * @param array $item
     *
     * @throws ArrayConversionException
     */
    protected function validateOptionalFields(array $item)
    {
        $optionalFields = array_merge(
            ['family', 'enabled', 'categories', 'groups'],
            $this->attrColumnsResolver->resolveAttributeColumns(),
            $this->getOptionalAssociationFields()
        );

        $unknownFields = [];
        foreach (array_keys($item) as $field) {
            if (!in_array($field, $optionalFields)) {
                $unknownFields[] = $field;
            }
        }

        if (0 < count($unknownFields)) {
            $message = count($unknownFields) > 1 ? 'The fields "%s" do not exist' : 'The field "%s" does not exist';

            throw new ArrayConversionException(sprintf($message, implode(', ', $unknownFields)));
        }
    }

    /**
     * @param array $item
     *
     * @throws ArrayConversionException
     */
    protected function validateFieldValueTypes(array $item)
    {
        $stringFields = ['family', 'categories', 'groups'];
        $booleanFields = ['enabled'];

        foreach ($item as $field => $value) {
            if (in_array($field, $stringFields) && !is_string($value)) {
                throw new ArrayConversionException(
                    sprintf('The field "%s" should contain a string, "%s" provided', $field, $value)
                );
            } elseif (in_array($field, $booleanFields) && !is_bool($value)) {
                throw new ArrayConversionException(
                    sprintf('The field "%s" should contain a boolean, "%s" provided', $field, $value)
                );
            }
        }
    }

    /**
     * Returns associations fields (resolves once)
     *
     * @return array
     */
    protected function getOptionalAssociationFields()
    {
        if (empty($this->optionalAssocFields)) {
            $this->optionalAssocFields = $this->assocColumnsResolver->resolveAssociationColumns();
        }

        return $this->optionalAssocFields;
    }
}
