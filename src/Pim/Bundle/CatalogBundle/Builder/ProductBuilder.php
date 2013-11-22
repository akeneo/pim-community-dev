<?php

namespace Pim\Bundle\CatalogBundle\Builder;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
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
     * @var CurrencyManager
     */
    protected $currencyManager;

    /**
     * @var array
     */
    protected $scopeRows = null;

    /**
     * @var array
     */
    protected $localeRows = null;

    /**
     * @var array
     */
    protected $scopeToLocaleRows = null;

    /**
     * Constructor
     *
     * @param string          $productClass    Entity name
     * @param ObjectManager   $objectManager   Storage manager
     * @param CurrencyManager $currencyManager Currency manager
     */
    public function __construct($productClass, ObjectManager $objectManager, CurrencyManager $currencyManager)
    {
        $this->productClass    = $productClass;
        $this->objectManager   = $objectManager;
        $this->currencyManager = $currencyManager;
    }

    /**
     * Add empty values for family and product-specific attributes for relevant scopes and locales
     *
     * It makes sure that if an attribute is translatable/scopable, then all values in the required locales/channels
     * exist. If the attribute is not scopable or translatable, makes sure that a single value exists.
     *
     * @param ProductInterface $product
     *
     * @return null
     */
    public function addMissingProductValues(ProductInterface $product)
    {
        $attributes = $this->getExpectedAttributes($product);

        foreach ($attributes as $attribute) {
            $requiredValues = $this->getExpectedValues($attribute);
            $existingValues = $this->getExistingValues($product, $attribute);

            $missingValues = array_filter(
                $requiredValues,
                function ($value) use ($existingValues) {
                    return !in_array($value, $existingValues);
                }
            );
            foreach ($missingValues as $value) {
                $this->addProductValue($product, $attribute, $value['locale'], $value['scope']);
            }
        }

        $this->addMissingPrices($product);
    }

    /**
     * Creates required value(s) to add the attribute to the product
     *
     * @param ProductInterface $product
     * @param ProductAttribute $attribute
     *
     * @return null
     */
    public function addAttributeToProduct(ProductInterface $product, ProductAttribute $attribute)
    {
        $requiredValues = $this->getExpectedValues($attribute);

        foreach ($requiredValues as $value) {
            $this->addProductValue($product, $attribute, $value['locale'], $value['scope']);
        }
    }

    /**
     * Deletes values that link an attribute to a product
     *
     * @param ProductInterface $product
     * @param ProductAttribute $attribute
     *
     * @return boolean
     */
    public function removeAttributeFromProduct(ProductInterface $product, ProductAttribute $attribute)
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
     * @return ProductAttribute[]
     */
    protected function getExpectedAttributes(ProductInterface $product)
    {
        $attributes = $product->getAttributes();
        if ($family = $product->getFamily()) {
            foreach ($family->getAttributes() as $attribute) {
                $attributes[] = $attribute;
            }
        }

        return array_unique($attributes);
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
        if (!$this->productValueClass) {
            $meta = $this->objectManager->getClassMetadata($this->productClass);
            $associations = $meta->getAssociationMappings();
            $this->productValueClass = $associations['values']['targetEntity'];
        }

        return $this->productValueClass;
    }

    /**
     * Add a missing value to the product
     *
     * @param ProductInterface $product
     * @param Attribute        $attribute
     * @param string           $locale
     * @param string           $scope
     *
     * @return null
     */
    protected function addProductValue(ProductInterface $product, $attribute, $locale = null, $scope = null)
    {
        $value = $this->createProductValue();
        if ($locale) {
            $value->setLocale($locale);
        }
        $value->setScope($scope);
        $value->setAttribute($attribute);

        $product->addValue($value);
    }

    /**
     * Returns an array of product values for the passed attribute
     *
     * @param ProductInterfac  $product
     * @param ProductAttribute $attribute
     *
     * @return array:array
     */
    protected function getExistingValues(ProductInterface $product, ProductAttribute $attribute)
    {
        $existingValues = array();
        foreach ($product->getValues() as $value) {
            if ($value->getAttribute() === $attribute) {
                $existingValues[] = array('locale' => $value->getLocale(), 'scope' => $value->getScope());
            }
        }

        return $existingValues;
    }

    /**
     * Returns an array of values that are expected to link product to an attribute depending on locale and scope
     * Each value is returned as an array with 'scope' and 'locale' keys
     *
     * @param ProductAttribute $attribute
     *
     * @return array:array
     */
    protected function getExpectedValues(ProductAttribute $attribute)
    {
        $requiredValues = array();
        if ($attribute->getScopable() and $attribute->getTranslatable()) {
            $requiredValues = $this->getScopeToLocaleRows();

        } elseif ($attribute->getScopable()) {
            $requiredValues = $this->getScopeRows();

        } elseif ($attribute->getTranslatable()) {
            $requiredValues = $this->getLocaleRows();

        } else {
            $requiredValues[] = array('locale' => null, 'scope' => null);
        }

        return $requiredValues;
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
     * @return array
     */
    protected function getLocaleRows()
    {
        if (!$this->localeRows) {
            $locales = $this->objectManager->getRepository('PimCatalogBundle:Locale')->getActivatedLocales();
            $this->localeRows = array();
            foreach ($locales as $locale) {
                $this->localeRows[]= array('locale' => $locale->getCode(), 'scope' => null);
            }
        }

        return $this->localeRows;
    }

    /**
     * Return rows for available channels
     *
     * @return array
     */
    protected function getScopeRows()
    {
        if (!$this->scopeRows) {
            $channels = $this->objectManager->getRepository('PimCatalogBundle:Channel')->findAll();
            $this->scopeRows = array();
            foreach ($channels as $channel) {
                $this->scopeRows[]= array('locale' => null, 'scope' => $channel->getCode());
            }
        }

        return $this->scopeRows;
    }

    /**
     * Return rows for available channels and theirs locales
     *
     * @return array
     */
    protected function getScopeToLocaleRows()
    {
        if (!$this->scopeToLocaleRows) {
            $channels = $this->objectManager->getRepository('PimCatalogBundle:Channel')->findAll();
            $this->scopeToLocaleRows = array();
            foreach ($channels as $channel) {
                foreach ($channel->getLocales() as $locale) {
                    $this->scopeToLocaleRows[]= array('locale' => $locale->getCode(), 'scope' => $channel->getCode());
                }
            }
        }

        return $this->scopeToLocaleRows;
    }
}
