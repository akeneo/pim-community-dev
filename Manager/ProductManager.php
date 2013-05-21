<?php

namespace Pim\Bundle\ProductBundle\Manager;

use Pim\Bundle\ProductBundle\Entity\ProductPrice;

use Pim\Bundle\ConfigBundle\Manager\CurrencyManager;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\ProductBundle\Entity\Product;
use Pim\Bundle\ProductBundle\Entity\ProductValue;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Product manager
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductManager extends FlexibleManager
{
    protected $mediaManager;

    /**
     * {@inheritDoc}
     */
    public function __construct($flexibleName, $flexibleConfig, ObjectManager $storageManager, EventDispatcherInterface $eventDispatcher, AttributeTypeFactory $attributeTypeFactory, $mediaManager)
    {
        parent::__construct($flexibleName, $flexibleConfig, $storageManager, $eventDispatcher, $attributeTypeFactory);

        $this->mediaManager = $mediaManager;
    }

    /**
     * Save a product in two phases :
     *   1) Persist and flush the entity as usual
     *   2)
     *     2.1) Force the reloading of the object (to be sure all values are loaded)
     *     2.2) Add missing scope and locale values for each attribute
     *     2.3) Reflush to save these new values
     *
     * @param Product $product
     */
    public function save(Product $product)
    {
        $this->handleMedia($product);
        $this->storageManager->persist($product);
        $this->storageManager->flush();

        $this->storageManager->refresh($product);
        $this->addMissingAttributeValues($product);
        $this->storageManager->flush();
    }

    /**
     * Add empty values for product family and product-specific attributes for relevant scopes and locales
     *
     * It makes sure that if an attribute is translatable/scopable, then all values
     * in the required locales/channels exist. If the attribute is not scopable or
     * translatable, makes sure that a single value exists.
     *
     * @param Product $product
     *
     * @return null
     */
    private function addMissingAttributeValues(Product $product)
    {
        $channels  = $this->getChannels();
        $languages = $product->getActiveLanguages();
        $attributes = $product->getAttributes();

        if ($family = $product->getProductFamily()) {
            foreach ($family->getAttributes() as $attribute) {
                $attributes[] = $attribute;
            }
        }

        $attributes = array_unique($attributes, SORT_REGULAR);

        foreach ($attributes as $attribute) {
            $existingValues = array();
            $requiredValues = array();

            foreach ($product->getValues() as $value) {
                if ($value->getAttribute() === $attribute) {
                    $existingValues[] = $value->getScope() . ':' . $value->getLocale();
                }
            }

            if ($attribute->getScopable()) {
                foreach ($channels as $channel) {
                    foreach ($languages as $language) {
                        $requiredValues[] = $channel->getCode() . ':' . $language->getCode();
                    }
                }
            } elseif ($attribute->getTranslatable()) {
                foreach ($languages as $language) {
                    $requiredValues[] = ':' . $language->getCode();
                }
            } else {
                $requiredValues[] = ':';
            }

            $missingValues = array_diff($requiredValues, $existingValues);
            foreach ($missingValues as $value) {
                $value = explode(':', $value);
                $scope = $value[0] === '' ? null : $value[0];
                $locale = $value[1] === '' ? null : $value[1];
                $this->addProductValue($product, $attribute, $locale, $scope);
            }
        }
    }

    /**
     * Return available channels
     *
     * @return ArrayCollection
     */
    protected function getChannels()
    {
        return $this->storageManager->getRepository('PimConfigBundle:Channel')->findAll();
    }

    /**
     * Add missing prices (a price per currency)
     *
     * @param CurrencyManager $manager the currency manager
     * @param Product         $product the product
     */
    public function addMissingPrices(CurrencyManager $manager, Product $product)
    {
        foreach ($product->getValues() as $value) {
            if ($value->getAttribute()->getAttributeType() === 'pim_product_price_collection') {
                $activeCurrencies = $manager->getActiveCodes();
                $existingCurrencies = array();
                foreach ($value->getPrices() as $price) {
                    $existingCurrencies[]= $price->getCurrency();
                }
                $newCurrencies = array_diff($activeCurrencies, $existingCurrencies);
                foreach ($newCurrencies as $currency) {
                    $price = new ProductPrice();
                    $price->setCurrency($currency);
                    $value->addPrice($price);
                }
            }
        }
    }

    /**
     * Add a missing value to the product
     *
     * @param Product   $product
     * @param Attribute $attribute
     * @param string    $locale
     * @param string    $scope
     *
     * @return null
     */
    private function addProductValue(Product $product, $attribute, $locale = null, $scope = null)
    {
        $value = $this->createFlexibleValue();
        if ($locale) {
            $value->setLocale($locale);
        }
        $value->setScope($scope);
        $value->setAttribute($attribute);

        $product->addValue($value);
    }

    /**
     * @param Product $product
     */
    private function handleMedia(Product $product)
    {
        foreach ($product->getValues() as $value) {
            if (null !== $media = $value->getMedia()) {
                $this->mediaManager->handle(
                    $value->getMedia(),
                    null !== $media->getFile() ? $this->generateFilenamePrefix($product, $value) : null
                );
                if ($media->isRemoved() || null === $media->getFile()) {
                    $this->storageManager->remove($media);
                    $value->setMedia(null);
                }
            }
        }
    }

    /**
     * @param Product      $product
     * @param ProductValue $value
     *
     * @return string
     */
    private function generateFilenamePrefix(Product $product, ProductValue $value)
    {
        return sprintf(
            '%s-%s-%s-%s-%s',
            $product->getSku(),
            $value->getAttribute()->getCode(),
            $value->getLocale(),
            $value->getScope(),
            time()
        );
    }
}
