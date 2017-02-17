<?php

namespace Pim\Component\Catalog\Builder;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * Product builder interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductBuilderInterface
{
    /**
     * Create product with its identifier value,
     *  - sets the identifier data if provided
     *  - sets family if provided
     *
     * @param string $identifier
     * @param string $familyCode
     *
     * @return ProductInterface
     */
    public function createProduct($identifier = null, $familyCode = null);

    /**
     * Add empty values for family and product-specific attributes for relevant scopes and locales
     *
     * It makes sure that if an attribute is localizable/scopable, then all values in the required locales/channels
     * exist. If the attribute is not scopable or localizable, makes sure that a single value exists.
     *
     * @param ProductInterface   $product
     * @param ChannelInterface[] $channels
     * @param LocaleInterface[]  $locales
     *
     * @return ProductBuilderInterface
     */
    public function addMissingProductValues(ProductInterface $product, array $channels = null, array $locales = null);

    /**
     * Add empty associations for each association types when they don't exist yet
     *
     * @param ProductInterface $product
     *
     * @return ProductBuilderInterface
     */
    public function addMissingAssociations(ProductInterface $product);

    /**
     * Creates required value(s) to add the attribute to the product
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     */
    public function addAttributeToProduct(ProductInterface $product, AttributeInterface $attribute);

    /**
     * Add or replace a product value.
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $scope
     * @param mixed              $data
     *
     * @return ProductValueInterface
     */
    public function addOrReplaceProductValue(
        ProductInterface $product,
        AttributeInterface $attribute,
        $locale,
        $scope,
        $data
    );
}
