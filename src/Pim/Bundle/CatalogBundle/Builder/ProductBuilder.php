<?php

namespace Pim\Bundle\CatalogBundle\Builder;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\LocaleManager;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;

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
     * @param string          $productClass      Product class name
     * @param string          $productValueClass Product value class name
     * @param ObjectManager   $objectManager     Storage manager
     * @param ChannelManager  $channelManager    Channel Manager
     * @param LocaleManager   $localeManager     Locale Manager
     * @param CurrencyManager $currencyManager   Currency manager
     */
    public function __construct(
        $productClass,
        $productValueClass,
        ObjectManager $objectManager,
        ChannelManager $channelManager,
        LocaleManager $localeManager,
        CurrencyManager $currencyManager
    ) {
        $this->productClass      = $productClass;
        $this->productValueClass = $productValueClass;
        $this->objectManager     = $objectManager;
        $this->channelManager    = $channelManager;
        $this->localeManager     = $localeManager;
        $this->currencyManager   = $currencyManager;
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
     * @param ProductInterface  $product
     * @param AbstractAttribute $attribute
     *
     * @return null
     */
    public function addAttributeToProduct(ProductInterface $product, AbstractAttribute $attribute)
    {
        $requiredValues = $this->getExpectedValues(array($attribute));

        foreach ($requiredValues as $value) {
            $this->addProductValue($product, $attribute, $value['locale'], $value['scope']);
        }
    }

    /**
     * Deletes values that link an attribute to a product
     *
     * @param ProductInterface  $product
     * @param AbstractAttribute $attribute
     *
     * @return boolean
     */
    public function removeAttributeFromProduct(ProductInterface $product, AbstractAttribute $attribute)
    {
        $values = $this->objectManager
            ->getRepository($this->getProductValueClass())
            ->findBy(array('entity' => $product, 'attribute' => $attribute));

        foreach ($values as $value) {
            $this->objectManager->remove($value);
        }

        $this->objectManager->flush();
    }

    /**
     * Get expected attributes for the product
     *
     * @param ProductInterface $product
     *
     * @return AbstractAttribute[]
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
     * @param ProductInterface  $product
     * @param AbstractAttribute $attribute
     * @param string            $locale
     * @param string            $scope
     *
     * @return null
     */
    protected function addProductValue(
        ProductInterface $product,
        AbstractAttribute $attribute,
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
    }

    /**
     * Returns an array of product values identifiers
     *
     * @param ProductInterface  $product
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
     * @param AbstractAttribute[] $attributes
     *
     * @return array:array
     */
    protected function getExpectedValues(array $attributes)
    {
        $values = array();
        foreach ($attributes as $attribute) {
            $requiredValues = array();
            if ($attribute->isScopable() and $attribute->isLocalizable()) {
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
     * @param AbstractAttribute $attribute
     * @param array             $values
     *
     * @return array
     */
    protected function filterExpectedValues(AbstractAttribute $attribute, array $values)
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
        foreach ($product->getValues() as $value) {
            if ($value->getAttribute()->getAttributeType() === 'pim_catalog_price_collection') {
                $activeCurrencies = $this->currencyManager->getActiveCodes();
                $value->addMissingPrices($activeCurrencies);
                $value->removeDisabledPrices($activeCurrencies);
            }
        }
    }

    /**
     * Return rows for available locales
     *
     * @param AbstractAttribute $attribute
     *
     * @return array
     */
    protected function getLocaleRows(AbstractAttribute $attribute)
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
     * @param AbstractAttribute $attribute
     *
     * @return array
     */
    protected function getScopeRows(AbstractAttribute $attribute)
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
     * @param AbstractAttribute $attribute
     *
     * @return array
     */
    protected function getScopeToLocaleRows(AbstractAttribute $attribute)
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
