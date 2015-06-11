<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\Flat\Product\Resolver\AssociationFieldsResolver;
use Pim\Component\Connector\ArrayConverter\Flat\Product\Resolver\AttributeFieldsResolver;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;

/**
 * Product association converter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationStandardConverter implements StandardArrayConverterInterface
{
    /** @var ProductStandardConverter */
    protected $productConverter;

    /** @var AssociationFieldsResolver */
    protected $associationFieldResolver;

    /** @var AttributeFieldsResolver */
    protected $attributeFieldResolver;

    /**
     * @param ProductStandardConverter  $productConverter
     * @param AssociationFieldsResolver $associationFieldResolver
     * @param AttributeFieldsResolver   $attributeFieldResolver
     */
    public function __construct(
        ProductStandardConverter $productConverter,
        AssociationFieldsResolver $associationFieldResolver,
        AttributeFieldsResolver $attributeFieldResolver
    ) {
        $this->productConverter = $productConverter;
        $this->associationFieldResolver = $associationFieldResolver;
        $this->attributeFieldResolver = $attributeFieldResolver;
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
     *      "sku": [{
     *          "locale": null,
     *          "scope":  null,
     *          "data":  "MySku",
     *      }],
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
        $filteredItem = $this->filter($item);
        $convertedItem = $this->productConverter->convert($filteredItem, $options);

        return $convertedItem;
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
        $requiredFields = $this->associationFieldResolver->resolveAssociationFields();
        $requiredFields[] = $this->attributeFieldResolver->resolveIdentifierField();

        foreach (array_keys($item) as $fieldName) {
            if (!in_array($fieldName, $requiredFields)) {
                unset($item[$fieldName]);
            }
        }

        return $item;
    }
}
