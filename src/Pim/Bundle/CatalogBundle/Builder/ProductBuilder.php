<?php

namespace Pim\Bundle\CatalogBundle\Builder;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\ProductValue;
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

            $redundantValues = array_filter(
                $existingValues,
                function ($value) use ($requiredValues) {
                    return !in_array($value, $requiredValues);
                }
            );

            foreach ($missingValues as $value) {
                $this->addProductValue($product, $attribute, $value['locale'], $value['scope']);
            }

            foreach ($redundantValues as $value) {
                $this->removeProductValue($product, $attribute, $value['locale'], $value['scope']);
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
        $values = $this->objectManager->getRepository($this->getProductValueClass())
            ->findBy(array('entity' => $product, 'attribute' => $attribute,));

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
     * @return ProductValue
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
        if ($attribute->getScopable()) {
            $channels = $this->getChannels();
            if ($attribute->getTranslatable()) {
                foreach ($channels as $channel) {
                    foreach ($channel->getLocales() as $locale) {
                        $requiredValues[] = array('locale' => $locale->getCode(), 'scope' => $channel->getCode());
                    }
                }
            } else {
                foreach ($channels as $channel) {
                    $requiredValues[] = array('locale' => null, 'scope' => $channel->getCode());
                }
            }
        } elseif ($attribute->getTranslatable()) {
            $locales = $this->getLocales();
            foreach ($locales as $locale) {
                $requiredValues[] = array('locale' => $locale->getCode(), 'scope' => null);
            }
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
     * Remove a redundant value from the product
     *
     * @param ProductInterface $product
     * @param Attribute        $attribute
     * @param string           $locale
     * @param string           $scope
     *
     * @return null
     */
    protected function removeProductValue(ProductInterface $product, $attribute, $locale = null, $scope = null)
    {
        $values = $product->getValues();
        $values = $values->filter(
            function ($value) use ($attribute, $locale, $scope) {
                if ($value->getAttribute() === $attribute
                    && $value->getScope() === $scope
                    && $value->getLocale() === $locale) {
                    return true;
                }

                return false;
            }
        );
        foreach ($values as $value) {
            $product->removeValue($value);
            $value->setEntity(null);
        }
    }

    /**
     * Return available channels
     *
     * @return ArrayCollection
     */
    protected function getChannels()
    {
        return $this->objectManager->getRepository('PimCatalogBundle:Channel')->findAll();
    }

    /**
     * Return available locales
     *
     * @return ArrayCollection
     */
    protected function getLocales()
    {
        return $this->objectManager->getRepository('PimCatalogBundle:Locale')->getActivatedLocales();
    }
}
