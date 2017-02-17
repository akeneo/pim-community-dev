<?php

namespace Pim\Component\Catalog\Builder;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductPriceInterface;
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
     * Add a product price with currency and data to the value. If the price
     * already exists, its data is updated and it is returned.
     *
     * @param ProductValueInterface $value
     * @param string                $currency
     * @param float|int|null        $amount
     *
     * @return null|ProductPriceInterface
     *
     * @deprecated will be removed in 1.8
     */
    public function addPriceForCurrency(ProductValueInterface $value, $currency, $amount = null);

    /**
     * Add a product price with currency and data to the value. If the price already exists, its data is
     * updated and it is returned.
     *
     * @param ProductValueInterface $value
     * @param string                $currency
     * @param float|int             $amount
     *
     * @return null|ProductPriceInterface
     *
     * @deprecated Will be removed in 1.8.
     */
    public function addPriceForCurrencyWithData(ProductValueInterface $value, $currency, $amount);

    /**
     * Remove extra prices that are not in the currencies passed in arguments
     *
     * @param ProductValueInterface $value
     * @param array                 $currencies
     *
     * @deprecated will be removed in 1.8.
     */
    public function removePricesNotInCurrency(ProductValueInterface $value, array $currencies);

    /**
     * Add missing prices to a product value
     *
     * @param ProductValueInterface $value
     *
     * @return ProductValueInterface
     *
     * @deprecated will be removed in 1.8.
     */
    public function addMissingPrices(ProductValueInterface $value);

    /**
     * Add a missing value to the product
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $scope
     *
     * @return ProductValueInterface
     *
     * @deprecated will be removed in 1.8. Please use "addOrReplaceProductValue" instead.
     */
    public function addProductValue(
        ProductInterface $product,
        AttributeInterface $attribute,
        $locale = null,
        $scope = null
    );

    /**
     * Add or replace a product value.
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $scope
     *
     * @return ProductValueInterface
     */
    public function addOrReplaceProductValue(
        ProductInterface $product,
        AttributeInterface $attribute,
        $locale = null,
        $scope = null
    );

    /**
     * Create a productValue
     *
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $scope
     *
     * @return ProductValueInterface
     *
     * @deprecated will be removed in 1.8. Please use ProductValueFactory::create instead.
     */
    public function createProductValue(AttributeInterface $attribute, $locale = null, $scope = null);
}
