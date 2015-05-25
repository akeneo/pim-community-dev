<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\Flat\Product\Converter\ProductFieldConverter;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Converter\ValueConverterRegistryInterface;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Merger\ColumnsMerger;
use Pim\Component\Connector\ArrayConverter\Flat\Product\OptionsResolverConverter;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Splitter\FieldSplitter;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;

/**
 * Product Flat Converter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductToStandardConverter implements StandardArrayConverterInterface
{
    /** @var OptionsResolverConverter */
    protected $optionsResolverConverter;

    /** @var ValueConverterRegistryInterface */
    protected $converterRegistry;

    /** @var ProductAttributeFieldExtractor */
    protected $fieldExtractor;

    /** @var ProductAssociationFieldResolver */
    protected $assocFieldResolver;

    /** @var FieldSplitter */
    protected $fieldSplitter;

    /** @var ProductFieldConverter */
    protected $productFieldConverter;

    /** @var ColumnsMerger */
    protected $columnsMerger;

    /**
     * @param ProductAttributeFieldExtractor  $fieldExtractor
     * @param OptionsResolverConverter        $optionsResolverConverter
     * @param ValueConverterRegistryInterface $converterRegistry
     * @param ProductAssociationFieldResolver $assocFieldResolver
     * @param FieldSplitter                   $fieldSplitter
     * @param ProductFieldConverter           $productFieldConverter
     * @param ColumnsMerger                   $columnsMerger
     */
    public function __construct(
        ProductAttributeFieldExtractor $fieldExtractor,
        OptionsResolverConverter $optionsResolverConverter,
        ValueConverterRegistryInterface $converterRegistry,
        ProductAssociationFieldResolver $assocFieldResolver,
        FieldSplitter $fieldSplitter,
        ProductFieldConverter $productFieldConverter,
        ColumnsMerger $columnsMerger
    ) {
        $this->optionsResolverConverter = $optionsResolverConverter;
        $this->converterRegistry        = $converterRegistry;
        $this->fieldExtractor           = $fieldExtractor;
        $this->assocFieldResolver       = $assocFieldResolver;
        $this->fieldSplitter            = $fieldSplitter;
        $this->productFieldConverter    = $productFieldConverter;
        $this->columnsMerger            = $columnsMerger;
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
        $mergedItems = $this->columnsMerger->merge($resolvedItem);

        $result = [];
        foreach ($mergedItems as $column => $value) {
            if ($this->productFieldConverter->supportsColumn($column)) {
                $value = $this->productFieldConverter->convert($column, $value);
            } else {
                $value = $this->convertValue($column, $value);
            }

            if (null !== $value) {
                $result = $this->addFieldToCollection($result, $value);
            }
        }

        return $result;
    }

    /**
     * @param string $column
     * @param string $value
     *
     * @return array
     */
    protected function convertValue($column, $value)
    {
        $attributeFieldInfo = $this->fieldExtractor->extractAttributeFieldNameInfos($column);

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
    protected function addFieldToCollection(array $collection, array $value)
    {
        $collection = array_merge_recursive($collection, $value);

        return $collection;
    }
}
