<?php

namespace Pim\Bundle\CatalogBundle\Builder;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductPriceInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

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
     * Add empty values for family and product-specific attributes for relevant scopes and locales
     *
     * It makes sure that if an attribute is localizable/scopable, then all values in the required locales/channels
     * exist. If the attribute is not scopable or localizable, makes sure that a single value exists.
     *
     * @param ProductInterface $product
     *
     * @return null
     */
    public function addMissingProductValues(ProductInterface $product);

    /**
     * Creates required value(s) to add the attribute to the product
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     *
     * @return null
     */
    public function addAttributeToProduct(ProductInterface $product, AttributeInterface $attribute);

    /**
     * Deletes values that link an attribute to a product
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     *
     * @return boolean
     */
    public function removeAttributeFromProduct(ProductInterface $product, AttributeInterface $attribute);

    /**
     * Add a product price with currency to the value. If the price already exists, it is returned.
     *
     * @param ProductValueInterface $value
     * @param string                $currency
     *
     * @return ProductPriceInterface
     */
    public function addPriceForCurrency(ProductValueInterface $value, $currency);

    /**
     * Add a product price with currency and data to the value. If the price already exists, its data is
     * updated and it is returned.
     *
     * @param ProductValueInterface $value
     * @param string                $currency
     * @param float|int             $data
     *
     * @return null|ProductPriceInterface
     */
    public function addPriceForCurrencyWithData(ProductValueInterface $value, $currency, $data);

    /**
     * Remove extra prices that are not in the currencies passed in arguments
     *
     * @param ProductValueInterface $value
     * @param array                 $currencies
     */
    public function removePricesNotInCurrency(ProductValueInterface $value, array $currencies);

    /**
     * Add missing prices to a product value
     *
     * @param ProductValueInterface $value
     *
     * TODO (JJ) rename addMissingPricesToProductValue to be constistent with other methods
     * TODO (JJ) normal it returns nothing ?
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
     */
    public function addProductValue(
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
     */
    public function createProductValue(AttributeInterface $attribute, $locale = null, $scope = null);
}
