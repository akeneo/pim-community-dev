<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnInfoExtractor;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\Exception\ArrayConversionException;

/**
 * Product draft changes converter.
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ProductDraftChanges implements ArrayConverterInterface
{
    /** @var ArrayConverterInterface */
    protected $productConverter;

    /** @var AttributeColumnInfoExtractor */
    protected $attributeExtractor;

    /**
     * @param ArrayConverterInterface $productConverter
     * @param AttributeColumnInfoExtractor    $attributeExtractor
     */
    public function __construct(
        ArrayConverterInterface $productConverter,
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
     *     'family': 'my-code'
     *     'categories': 'code1,code2'
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
                unset($item[$key]);
            }
        }

        return $item;
    }
}
