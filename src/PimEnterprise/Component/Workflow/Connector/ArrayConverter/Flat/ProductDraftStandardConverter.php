<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\Flat\Product\AttributeColumnInfoExtractor;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\Connector\Exception\ArrayConversionException;

/**
 * Product draft converter.
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductDraftStandardConverter implements StandardArrayConverterInterface
{
    /** @var StandardArrayConverterInterface */
    protected $productConverter;

    /** @var AttributeColumnInfoExtractor */
    protected $attributeExtractor;

    /**
     * @param StandardArrayConverterInterface $productConverter
     * @param AttributeColumnInfoExtractor    $attributeExtractor
     */
    public function __construct(
        StandardArrayConverterInterface $productConverter,
        AttributeColumnInfoExtractor $attributeExtractor
    ) {
        $this->productConverter = $productConverter;
        $this->attributeExtractor = $attributeExtractor;
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
     *     'length': '10 CENTIMETER'
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
     *      "description": [{
     *          "locale": "en_US",
     *          "scope":  "mobile",
     *          "data":  "My description",
     *      }],
     *      "length": [{
     *          "locale": "en_US",
     *          "scope":  "mobile",
     *          "data":   {"data": "10", "unit": "CENTIMETER"}
     *      }]
     * }
     */
    public function convert(array $item, array $options = [])
    {
        $filteredItem = $this->filter($item);

        return $this->productConverter->convert($filteredItem, $options);
    }

    /**
     * Filters the item to check if there is something else than attributes.
     *
     * @param array $item
     *
     * @throws ArrayConversionException
     *
     * @return array
     */
    protected function filter(array $item)
    {
        foreach ($item as $key => $value) {
            $attributeInfo = $this->attributeExtractor->extractColumnInfo($key);

            if (null === $attributeInfo) {
                throw new ArrayConversionException(
                    sprintf('Field "%s" is not allowed. Only attributes are allowed in a product draft', $key)
                );
            }
        }

        return $item;
    }
}
