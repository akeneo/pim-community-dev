<?php

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard;

use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AssociationColumnsResolver;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\AttributeColumnsResolver;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ColumnsMapper;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\ColumnsMerger;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\Product\FieldConverter;
use Pim\Component\Connector\Exception\DataArrayConversionException;
use Pim\Component\Connector\Exception\StructureArrayConversionException;

/**
 * Convert a Product from Flat to Standard structure.
 *
 * This conversion does not result in the standard format. The structure is respected but data are not.
 * Firstly, the data is not delocalized here.
 * Then numeric attributes (metric, price, number) which may contain decimals, are not converted to string but remain float,
 * to be compatible with XLSX files and localization.
 *
 * To get a real standardized from the flat format, please
 * see {@link \Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductDelocalized }
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Product implements ArrayConverterInterface
{
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

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ArrayConverterInterface */
    protected $productValueConverter;

    /**
     * @param AssociationColumnsResolver      $assocColumnsResolver
     * @param AttributeColumnsResolver        $attrColumnsResolver
     * @param FieldConverter                  $fieldConverter
     * @param ColumnsMerger                   $columnsMerger
     * @param ColumnsMapper                   $columnsMapper
     * @param FieldsRequirementChecker        $fieldChecker
     * @param AttributeRepositoryInterface    $attributeRepository
     * @param ArrayConverterInterface         $productValueConverter
     */
    public function __construct(
        AssociationColumnsResolver $assocColumnsResolver,
        AttributeColumnsResolver $attrColumnsResolver,
        FieldConverter $fieldConverter,
        ColumnsMerger $columnsMerger,
        ColumnsMapper $columnsMapper,
        FieldsRequirementChecker $fieldChecker,
        AttributeRepositoryInterface $attributeRepository,
        ArrayConverterInterface $productValueConverter
    ) {
        $this->assocColumnsResolver = $assocColumnsResolver;
        $this->attrColumnsResolver = $attrColumnsResolver;
        $this->fieldConverter = $fieldConverter;
        $this->columnsMerger = $columnsMerger;
        $this->columnsMapper = $columnsMapper;
        $this->fieldChecker = $fieldChecker;
        $this->optionalAssocFields = [];
        $this->attributeRepository = $attributeRepository;
        $this->productValueConverter = $productValueConverter;
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
     *      "identifier": "MySku",
     *      "enabled": true,
     *      "categories": ["tshirt", "men"],
     *      "values": {
     *          "sku": [{
     *              "locale": null,
     *              "scope":  null,
     *              "data":  "MySku",
     *          }],
     *          "name": [{
     *              "locale": "fr_FR",
     *              "scope":  null,
     *              "data":  "T-shirt super beau",
     *          }],
     *          "description": [
     *               {
     *                   "locale": "en_US",
     *                   "scope":  "mobile",
     *                   "data":   "My description"
     *               },
     *               {
     *                   "locale": "fr_FR",
     *                   "scope":  "mobile",
     *                   "data":   "Ma description mobile"
     *               },
     *               {
     *                   "locale": "en_US",
     *                   "scope":  "ecommerce",
     *                   "data":   "My description for the website"
     *               },
     *          ],
     *          "price": [
     *               {
     *                   "locale": null,
     *                   "scope":  ecommerce,
     *                   "data":   [
     *                       {"amount": 10, "currency": "EUR"},
     *                       {"amount": 24, "currency": "USD"},
     *                       {"amount": 20, "currency": "CHF"}
     *                   ]
     *               }
     *               {
     *                   "locale": null,
     *                   "scope":  mobile,
     *                   "data":   [
     *                       {"amount": 11, "currency": "EUR"},
     *                       {"amount": 25, "currency": "USD"},
     *                       {"amount": 21, "currency": "CHF"}
     *                   ]
     *               }
     *          ],
     *          "length": [{
     *              "locale": "en_US",
     *              "scope":  "mobile",
     *              "data":   {"amount": "10", "unit": "CENTIMETER"}
     *          }],
     *      },
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

        $mappedItem = $this->mapFields($item, $options);
        $filteredItem = $this->filterFields($mappedItem, $options['with_associations']);
        $this->validateItem($filteredItem);

        $mergedItem = $this->columnsMerger->merge($filteredItem);
        $convertedItem = $this->convertItem($mergedItem, $options);

        return $convertedItem;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function prepareOptions(array $options)
    {
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
     * @param bool  $withAssociations
     *
     * @return array
     */
    protected function filterFields(array $mappedItem, $withAssociations)
    {
        if (false === $withAssociations) {
            $isGroupAssPattern = '/^\w+'.AssociationColumnsResolver::GROUP_ASSOCIATION_SUFFIX.'$/';
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
        $convertedValues = [];

        foreach ($item as $column => $value) {
            if ($this->fieldConverter->supportsColumn($column)) {
                $value = $this->fieldConverter->convert($column, $value);
                $convertedItem = $this->mergeValueToItem($convertedItem, $value);
            } else {
                $convertedValues[$column] = $value;
            }
        }

        $convertedValues = $this->productValueConverter->convert($convertedValues);

        if (empty($convertedValues)) {
            throw new \LogicException('Cannot find any values. There should be at least one identifier attribute');
        }

        $convertedItem['values'] = $convertedValues;

        $identifierCode = $this->attributeRepository->getIdentifierCode();
        if (!isset($convertedItem['values'][$identifierCode])) {
            throw new \LogicException(sprintf('Unable to find the column "%s"', $identifierCode));
        }

        $convertedItem['identifier'] = $convertedItem['values'][$identifierCode][0]['data'];

        return $convertedItem;
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

    /**
     * @param array $item
     * @param bool  $withRequiredSku
     */
    protected function validateItem(array $item)
    {
        $requiredField = $this->attrColumnsResolver->resolveIdentifierField();
        $this->fieldChecker->checkFieldsPresence($item, [$requiredField]);
        $this->validateOptionalFields($item);
        $this->validateFieldValueTypes($item);
    }

    /**
     * @param array $item
     *
     * @throws StructureArrayConversionException
     */
    protected function validateOptionalFields(array $item)
    {
        $optionalFields = array_merge(
            ['family', 'enabled', 'categories', 'groups'],
            $this->attrColumnsResolver->resolveAttributeColumns(),
            $this->getOptionalAssociationFields()
        );

        // index $optionalFields by keys to improve performances
        $optionalFields = array_combine($optionalFields, $optionalFields);
        $unknownFields = [];

        foreach (array_keys($item) as $field) {
            if (!isset($optionalFields[$field])) {
                $unknownFields[] = $field;
            }
        }

        if (0 < count($unknownFields)) {
            $message = count($unknownFields) > 1 ? 'The fields "%s" do not exist' : 'The field "%s" does not exist';

            throw new StructureArrayConversionException(sprintf($message, implode(', ', $unknownFields)));
        }
    }

    /**
     * @param array $item
     *
     * @throws DataArrayConversionException
     */
    protected function validateFieldValueTypes(array $item)
    {
        $stringFields = ['family', 'categories', 'groups'];

        foreach ($item as $field => $value) {
            if (in_array($field, $stringFields) && !is_string($value)) {
                throw new DataArrayConversionException(
                    sprintf('The field "%s" should contain a string, "%s" provided', $field, $value)
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
