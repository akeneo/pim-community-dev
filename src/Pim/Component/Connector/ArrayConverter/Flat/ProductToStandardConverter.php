<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\Flat\Product\Converter\ProductFieldConverter;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Converter\ValueConverterRegistryInterface;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Extractor\ProductAttributeFieldExtractor;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Mapper\ColumnsMapper;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Merger\ColumnsMerger;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Resolver\AssociationFieldsResolver;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Resolver\AttributeFieldsResolver;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Product Converter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO: rename!
 */
class ProductToStandardConverter implements StandardArrayConverterInterface
{
    /** @var ValueConverterRegistryInterface */
    protected $converterRegistry;

    /** @var ProductAttributeFieldExtractor */
    protected $attrFieldExtractor;

    /** @var AttributeFieldsResolver */
    protected $attrFieldsResolver;

    /** @var AssociationFieldsResolver */
    protected $assocFieldsResolver;

    /** @var ProductFieldConverter */
    protected $productFieldConverter;

    /** @var ColumnsMerger */
    protected $columnsMerger;

    /** @var ColumnsMapper */
    protected $columnsMapper;

    /** @var array */
    protected $optionalAssociationFields;

    /**
     * @param ProductAttributeFieldExtractor  $attrFieldExtractor
     * @param ValueConverterRegistryInterface $converterRegistry
     * @param AssociationFieldsResolver       $assocFieldsResolver
     * @param AttributeFieldsResolver         $attrFieldsResolver
     * @param ProductFieldConverter           $productFieldConverter
     * @param ColumnsMerger                   $columnsMerger
     * @param ColumnsMapper                   $columnsMapper
     */
    public function __construct(
        ProductAttributeFieldExtractor $attrFieldExtractor,
        ValueConverterRegistryInterface $converterRegistry,
        AssociationFieldsResolver $assocFieldsResolver,
        AttributeFieldsResolver $attrFieldsResolver,
        ProductFieldConverter $productFieldConverter,
        ColumnsMerger $columnsMerger,
        ColumnsMapper $columnsMapper
    ) {
        $this->attrFieldExtractor = $attrFieldExtractor;
        $this->converterRegistry = $converterRegistry;
        $this->assocFieldsResolver = $assocFieldsResolver;
        $this->attrFieldsResolver = $attrFieldsResolver;
        $this->productFieldConverter = $productFieldConverter;
        $this->columnsMerger = $columnsMerger;
        $this->columnsMapper = $columnsMapper;
        $this->optionalAssociationFields = [];
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
        $mappedItem = $item;
        if (isset($options['mapping'])) {
            $mappedItem = $this->columnsMapper->map($item, $options['mapping']);
        }

        $resolvedItem = $this->resolveConverterOptions($mappedItem, $options);
        $mergedItems = $this->columnsMerger->merge($resolvedItem);

        $result = [];
        foreach ($mergedItems as $column => $value) {
            if ($this->productFieldConverter->supportsColumn($column)) {
                $value = $this->productFieldConverter->convert($column, $value);
            } else {
                $value = $this->convertValue($column, $value);
            }

            if (null !== $value) {
                $result = $this->mergeValueToResult($result, $value);
            }
        }

        return $result;
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
        $attributeFieldInfo = $this->attrFieldExtractor->extractAttributeFieldNameInfos($column);

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
     * Method to make the array_merge_recursive more "smart" for price collections
     *
     * @param array $collection The collection in which we add the element
     * @param array $value      The structured value to add to the collection
     *
     * @return array
     */
    protected function mergeValueToResult(array $collection, array $value)
    {
        $collection = array_merge_recursive($collection, $value);

        return $collection;
    }

    /**
     * @param array $item
     * @param array $options
     *
     * @return array
     */
    protected function resolveConverterOptions(array $item, array $options = [])
    {
        $enabled = (isset($options['default_values']['enabled'])) ? $options['default_values']['enabled'] : true;
        $resolver = $this->createOptionsResolver();
        $resolver->setDefaults(['enabled' => $enabled]);
        $resolvedItem = $resolver->resolve($item);

        return $resolvedItem;
    }

    /**
     * @return OptionsResolverInterface
     */
    protected function createOptionsResolver()
    {
        $resolver = new OptionsResolver();

        $required = [];
        $allowedTypes = [
            'family'     => 'string',
            'enabled'    => 'bool',
            'categories' => 'string',
            'groups'     => 'string'
        ];
        $optional = array_merge(
            ['family', 'enabled', 'categories', 'groups'],
            $this->attrFieldsResolver->resolveAttributesFields(),
            $this->getOptionalAssociationFields()
        );

        $resolver->setRequired($required);
        $resolver->setOptional($optional);
        $resolver->setAllowedTypes($allowedTypes);
        $booleanNormalizer = function ($options, $value) {
            return (bool) $value;
        };
        $resolver->setNormalizers(['enabled' => $booleanNormalizer]);

        return $resolver;
    }

    /**
     * Returns associations fields (resolves once)
     *
     * @return array
     */
    protected function getOptionalAssociationFields()
    {
        if (empty($this->optionalAssociationFields)) {
            $this->optionalAssociationFields = $this->assocFieldsResolver->resolveAssociationFields();
        }

        return $this->optionalAssociationFields;
    }
}
