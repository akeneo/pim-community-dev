<?php

namespace Pim\Bundle\CatalogBundle\Builder;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductPriceInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\CurrencyRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;

/**
 * Product builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductBuilder implements ProductBuilderInterface
{
    /** @var string */
    protected $productClass;

    /** @var string */
    protected $productValueClass;

    /** @var string */
    protected $productPriceClass;

    /** @var ChannelRepository */
    protected $channelRepository;

    /** @var LocaleRepository */
    protected $localeRepository;

    /** @var CurrencyRepository */
    protected $currencyRepository;

    /**
     * Constructor
     *
     * @param ChannelRepositoryInterface  $channelRepository  Channel repository
     * @param LocaleRepositoryInterface   $localeRepository   Locale repository
     * @param CurrencyRepositoryInterface $currencyRepository Currency repository
     * @param array                       $classes            Product, product value and price classes
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        CurrencyRepositoryInterface $currencyRepository,
        array $classes
    ) {
        $this->channelRepository  = $channelRepository;
        $this->localeRepository   = $localeRepository;
        $this->currencyRepository = $currencyRepository;
        $this->productClass       = $classes['product'];
        $this->productValueClass  = $classes['product_value'];
        $this->productPriceClass  = $classes['product_price'];
    }

    /**
     * {@inheritdoc}
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

        $this->addMissingPricesToProduct($product);
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeToProduct(ProductInterface $product, AttributeInterface $attribute)
    {
        $requiredValues = $this->getExpectedValues(array($attribute));

        foreach ($requiredValues as $value) {
            $this->addProductValue($product, $attribute, $value['locale'], $value['scope']);
        }
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function addPriceForCurrency(ProductValueInterface $value, $currency)
    {
        if (!$this->hasPriceForCurrency($value, $currency)) {
            $value->addPrice(new $this->productPriceClass(null, $currency));
        }

        return $this->getPriceForCurrency($value, $currency);
    }

    /**
     * {@inheritdoc}
     */
    public function addPriceForCurrencyWithData(ProductValueInterface $value, $currency, $data)
    {
        $price = $this->addPriceForCurrency($value, $currency);
        $price->setData($data);

        return $price;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function addProductValue(
        ProductInterface $product,
        AttributeInterface $attribute,
        $locale = null,
        $scope = null
    ) {
        $value = $this->createProductValue($attribute, $locale, $scope);

        $product->addValue($value);

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function createProductValue(AttributeInterface $attribute, $locale = null, $scope = null)
    {
        $class = $this->getProductValueClass();

        $value = new $class();
        $value->setAttribute($attribute);
        if ($attribute->isLocalizable()) {
            if ($locale !== null) {
                $value->setLocale($locale);
            } else {
                throw new \InvalidArgumentException(
                    sprintf(
                        'A locale must be provided to create a value for the localizable attribute %s',
                        $attribute->getCode()
                    )
                );
            }
        }

        if ($attribute->isScopable()) {
            if ($scope !== null) {
                $value->setScope($scope);
            } else {
                throw new \InvalidArgumentException(
                    sprintf(
                        'A scope must be provided to create a value for the scopable attribute %s',
                        $attribute->getCode()
                    )
                );
            }
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function addMissingPrices(ProductValueInterface $value)
    {
        $activeCurrencyCodes = $this->currencyRepository->getActivatedCurrencyCodes();

        if ('pim_catalog_price_collection' === $value->getAttribute()->getAttributeType()) {
            $prices = $value->getPrices();

            foreach ($activeCurrencyCodes as $currencyCode) {
                if (null === $value->getPrice($currencyCode)) {
                    $this->addPriceForCurrency($value, $currencyCode);
                }
            }

            foreach ($prices as $price) {
                if (!in_array($price->getCurrency(), $activeCurrencyCodes)) {
                    $value->removePrice($price);
                }
            }
        }

        return $value;
    }

    /**
     * @param ProductValueInterface $value
     * @param string                $currency
     *
     * @return boolean
     */
    protected function hasPriceForCurrency(ProductValueInterface $value, $currency)
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
     * @return null|ProductPriceInterface
     */
    protected function getPriceForCurrency(ProductValueInterface $value, $currency)
    {
        foreach ($value->getPrices() as $price) {
            if ($currency === $price->getCurrency()) {
                return $price;
            }
        }

        return null;
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
     * Get product value class
     *
     * @return string
     */
    protected function getProductValueClass()
    {
        return $this->productValueClass;
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
                'locale'    => $value->getLocale(),
                'scope'     => $value->getScope()
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
        if ($attribute->isLocaleSpecific()) {
            $availableLocales = $attribute->getLocaleSpecificCodes();
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
    protected function addMissingPricesToProduct(ProductInterface $product)
    {
        foreach ($product->getValues() as $value) {
            $this->addMissingPrices($value);
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
        $locales = $this->localeRepository->getActivatedLocales();
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
        $channels = $this->channelRepository->findAll();
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
        $channels = $this->channelRepository->findAll();
        $scopeToLocaleRows = array();
        foreach ($channels as $channel) {
            foreach ($channel->getLocales() as $locale) {
                $scopeToLocaleRows[] = array(
                    'attribute' => $attribute->getCode(),
                    'locale'    => $locale->getCode(),
                    'scope'     => $channel->getCode()
                );
            }
        }

        return $scopeToLocaleRows;
    }
}
