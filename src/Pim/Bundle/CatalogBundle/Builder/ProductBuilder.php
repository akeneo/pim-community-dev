<?php

namespace Pim\Bundle\CatalogBundle\Builder;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Product builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductBuilder
{
    /**
     * @var string
     */
    protected $productClass;

    /**
     * @var string
     */
    protected $productValueClass;

    /**
     * @var string
     */
    protected $productPriceClass;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var LocaleManager
     */
    protected $localeManager;

    /**
     * @var CurrencyManager
     */
    protected $currencyManager;

    /**
     * Constructor
     *
     * @param ObjectManager   $objectManager   Storage manager
     * @param ChannelManager  $channelManager  Channel Manager
     * @param LocaleManager   $localeManager   Locale Manager
     * @param CurrencyManager $currencyManager Currency manager
     * @param array           $classes         Product, product value and price classes
     */
    public function __construct(
        ObjectManager $objectManager,
        ChannelManager $channelManager,
        LocaleManager $localeManager,
        CurrencyManager $currencyManager,
        array $classes
    ) {
        $this->objectManager     = $objectManager;
        $this->channelManager    = $channelManager;
        $this->localeManager     = $localeManager;
        $this->currencyManager   = $currencyManager;
        $this->productClass      = $classes['product'];
        $this->productValueClass = $classes['product_value'];
        $this->productPriceClass = $classes['product_price'];
    }

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
    public function addMissingProductValues(ProductInterface $product)
    {
        $attributes     = $this->getExpectedAttributes($product);
        $requiredValues = $this->getExpectedValues($attributes);
        $existingValues = $this->getExistingValues($product);

        $missingValues = array_filter(
            $requiredValues,
            function ($value) use ($existingValues) {
                return !in_array($value, $existingValues);
            }
        );

        foreach ($missingValues as $value) {
            $this->addProductValue($product, $attributes[$value['attribute']], $value['locale'], $value['scope']);
        }

        $this->addMissingPrices($product);
    }

    /**
     * Creates required value(s) to add the attribute to the product
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     *
     * @return null
     */
    public function addAttributeToProduct(ProductInterface $product, AttributeInterface $attribute)
    {
        $requiredValues = $this->getExpectedValues(array($attribute));

        foreach ($requiredValues as $value) {
            $this->addProductValue($product, $attribute, $value['locale'], $value['scope']);
        }
    }

    /**
     * Deletes values that link an attribute to a product
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     *
     * @return boolean
     */
    public function removeAttributeFromProduct(ProductInterface $product, AttributeInterface $attribute)
    {
        foreach ($product->getValues() as $value) {
            if ($attribute === $value->getAttribute()) {
                $product->removeValue($value);
            }
        }

        $this->objectManager->flush($product);
    }

    /**
     * Add a product price with currency to the value
     *
     * @param ProductValueInterface $value
     * @param string                $currency
     *
     * @return null|ProductPrice
     */
    public function addPriceForCurrency(ProductValueInterface $value, $currency)
    {
        if (!$this->hasPriceForCurrency($value, $currency)) {
            $value->addPrice(new $this->productPriceClass(null, $currency));
        }

        return $this->getPriceForCurrency($value, $currency);
    }

    /**
     * Remove extra prices that are not in the currencies passed in arguments
     *
     * @param ProductValueInterface $value
     * @param array                 $currencies
     */
    public function removePricesNotInCurrency(ProductValueInterface $value, array $currencies)
    {
        foreach ($value->getPrices() as $price) {
            if (!in_array($price->getCurrency(), $currencies)) {
                $value->removePrice($price);
            }
        }
    }

    /**
     * @param ProductValueInterface $value
     * @param string                $currency
     *
     * @return boolean
     */
    private function hasPriceForCurrency(ProductValueInterface $value, $currency)
    {
        foreach ($value->getPrices() as $price) {
            if ($currency === $price->getCurrency()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ProductValueInterface $value
     * @param string                $currency
     *
     * @return null|ProductPrice
     */
    private function getPriceForCurrency(ProductValueInterface $value, $currency)
    {
        foreach ($value->getPrices() as $price) {
            if ($currency === $price->getCurrency()) {
                return $price;
            }
        }
    }

    /**
     * Get expected attributes for the product
     *
     * @param ProductInterface $product
     *
     * @return AttributeInterface[]
     */
    protected function getExpectedAttributes(ProductInterface $product)
    {
        $attributes = array();
        $productAttributes = $product->getAttributes();
        foreach ($productAttributes as $attribute) {
            $attributes[$attribute->getCode()] = $attribute;
        }

        if ($family = $product->getFamily()) {
            foreach ($family->getAttributes() as $attribute) {
                $attributes[$attribute->getCode()] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * Create a product value
     *
     * @return ProductValueInterface
     */
    protected function createProductValue()
    {
        $class = $this->getProductValueClass();

        return new $class();
    }

    /**
     * Get product value class
     *
     * @return string
     */
    protected function getProductValueClass()
    {
        return $this->productValueClass;
    }

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
    ) {
        $value = $this->createProductValue();
        if ($locale) {
            $value->setLocale($locale);
        }
        $value->setScope($scope);
        $value->setAttribute($attribute);

        $product->addValue($value);

        return $value;
    }

    /**
     * Returns an array of product values identifiers
     *
     * @param ProductInterface $product
     *
     * @return array:array
     */
    protected function getExistingValues(ProductInterface $product)
    {
        $existingValues = array();
        $values = $product->getValues();
        foreach ($values as $value) {
            $existingValues[] = array(
                'attribute' => $value->getAttribute()->getCode(),
                'locale' => $value->getLocale(),
                'scope' => $value->getScope()
            );
        }

        return $existingValues;
    }

    /**
     * Returns an array of values that are expected to link product to an attribute depending on locale and scope
     * Each value is returned as an array with 'scope' and 'locale' keys
     *
     * @param AttributeInterface[] $attributes
     *
     * @return array:array
     */
    protected function getExpectedValues(array $attributes)
    {
        $values = array();
        foreach ($attributes as $attribute) {
            $requiredValues = array();
            if ($attribute->isScopable() && $attribute->isLocalizable()) {
                $requiredValues = $this->getScopeToLocaleRows($attribute);
            } elseif ($attribute->isScopable()) {
                $requiredValues = $this->getScopeRows($attribute);
            } elseif ($attribute->isLocalizable()) {
                $requiredValues = $this->getLocaleRows($attribute);
            } else {
                $requiredValues[] = array('attribute' => $attribute->getCode(), 'locale' => null, 'scope' => null);
            }
            $values = array_merge($values, $this->filterExpectedValues($attribute, $requiredValues));
        }

        return $values;
    }

    /**
     * Filter expected values based on the locales available for the provided attribute
     *
     * @param AttributeInterface $attribute
     * @param array              $values
     *
     * @return array
     */
    protected function filterExpectedValues(AttributeInterface $attribute, array $values)
    {
        if ($attribute->getAvailableLocales()) {
            $availableLocales = $attribute->getAvailableLocales()->map(
                function ($locale) {
                    return $locale->getCode();
                }
            )->toArray();
            foreach ($values as $index => $value) {
                if ($value['locale'] && !in_array($value['locale'], $availableLocales)) {
                    unset($values[$index]);
                }
            }
        }

        return $values;
    }

    /**
     * Add missing prices (a price per currency)
     *
     * @param ProductInterface $product the product
     *
     * @return null
     */
    protected function addMissingPrices(ProductInterface $product)
    {
        $activeCurrencies = $this->currencyManager->getActiveCodes();

        foreach ($product->getValues() as $value) {
            if ($value->getAttribute()->getAttributeType() === 'pim_catalog_price_collection') {
                $prices = $value->getPrices();

                foreach ($activeCurrencies as $activeCurrency) {
                    $hasPrice = $prices->filter(
                        function ($price) use ($activeCurrency) {
                            return $activeCurrency === $price->getCurrency();
                        }
                    )->count() > 0;

                    if (!$hasPrice) {
                        $this->addPriceForCurrency($value, $activeCurrency);
                    }
                }

                foreach ($prices as $price) {
                    if (!in_array($price->getCurrency(), $activeCurrencies)) {
                        $value->removePrice($price);
                    }
                }
            }
        }
    }

    /**
     * Return rows for available locales
     *
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    protected function getLocaleRows(AttributeInterface $attribute)
    {
        $locales = $this->localeManager->getActiveLocales();
        $localeRows = array();
        foreach ($locales as $locale) {
            $localeRows[] = array(
                'attribute' => $attribute->getCode(), 'locale' => $locale->getCode(), 'scope' => null
            );
        }

        return $localeRows;
    }

    /**
     * Return rows for available channels
     *
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    protected function getScopeRows(AttributeInterface $attribute)
    {
        $channels = $this->channelManager->getChannels();
        $scopeRows = array();
        foreach ($channels as $channel) {
            $scopeRows[] = array(
                'attribute' => $attribute->getCode(), 'locale' => null, 'scope' => $channel->getCode()
            );
        }

        return $scopeRows;
    }

    /**
     * Return rows for available channels and theirs locales
     *
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    protected function getScopeToLocaleRows(AttributeInterface $attribute)
    {
        $channels = $this->channelManager->getChannels();
        $scopeToLocaleRows = array();
        foreach ($channels as $channel) {
            foreach ($channel->getLocales() as $locale) {
                $scopeToLocaleRows[] = array(
                    'attribute' => $attribute->getCode(),
                    'locale' => $locale->getCode(),
                    'scope' => $channel->getCode()
                );
            }
        }

        return $scopeToLocaleRows;
    }
}
