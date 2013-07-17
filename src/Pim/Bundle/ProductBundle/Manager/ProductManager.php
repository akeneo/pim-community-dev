<?php

namespace Pim\Bundle\ProductBundle\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\ProductBundle\Entity\ProductPrice;
use Pim\Bundle\ConfigBundle\Manager\CurrencyManager;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\ProductBundle\Model\ProductInterface;
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
    public function __construct(
        $flexibleName,
        $flexibleConfig,
        ObjectManager $storageManager,
        EventDispatcherInterface $eventDispatcher,
        AttributeTypeFactory $attributeTypeFactory,
        $mediaManager
    ) {
        parent::__construct($flexibleName, $flexibleConfig, $storageManager, $eventDispatcher, $attributeTypeFactory);

        $this->mediaManager = $mediaManager;
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($code)
    {
        parent::setLocale($code);

        $this->getFlexibleRepository()->setLocale($code);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setScope($code)
    {
        parent::setScope($code);

        $this->getFlexibleRepository()->setScope($code);

        return $this;
    }

    /**
     * Find a product by id
     * Also ensure that it contains all required values
     *
     * @param int $id
     *
     * @return Product|null
     */
    public function find($id)
    {
        $product = $this->getFlexibleRepository()->findWithSortedAttribute($id);

        if ($product) {
            $this->ensureRequiredAttributeValues($product);
        }

        return $product;
    }

    /**
     * Find a product by identifier
     * Also ensure that it contains all required values
     *
     * @param string $identifier
     *
     * @return Product|null
     */
    public function findByIdentifier($identifier)
    {
        $code = $this->getIdentifierAttribute()->getCode();

        $products = $this->getFlexibleRepository()->findByWithAttributes(array(), array($code => $identifier));
        $product = reset($products);

        if ($product) {
            $this->ensureRequiredAttributeValues($product);
        }

        return $product;
    }

    /**
     * Save a product in two phases :
     *   1) Persist and flush the entity as usual and associate it to the provided categories
     *      associated with the provided tree
     *   2)
     *     2.1) Force the reloading of the object (to be sure all values are loaded)
     *     2.2) Add missing and remove redundant scope and locale values for each attribute
     *     2.3) Reflush to save these new values
     *
     * @param ProductInterface $product
     * @param ArrayCollection  $categories
     * @param array            $onlyTree
     */
    public function save(ProductInterface $product, ArrayCollection $categories = null, array $onlyTree = null)
    {
        $this->handleMedia($product);

        if ($categories != null) {
            $this->setCategories($product, $categories, $onlyTree);
        }

        $this->storageManager->persist($product);
        $this->storageManager->flush();

        $this->storageManager->refresh($product);
        $this->ensureRequiredAttributeValues($product);
        $this->storageManager->flush();
    }

    /**
     * Add missing prices (a price per currency)
     *
     * @param CurrencyManager  $manager the currency manager
     * @param ProductInterface $product the product
     */
    public function addMissingPrices(CurrencyManager $manager, ProductInterface $product)
    {
        foreach ($product->getValues() as $value) {
            if ($value->getAttribute()->getAttributeType() === 'pim_product_price_collection') {
                $activeCurrencies = $manager->getActiveCodes();
                $value->addMissingPrices($activeCurrencies);
                $value->removeDisabledPrices($activeCurrencies);
            }
        }
    }

    /**
     * Set the list of categories for a product. The categories not beloging
     * to the array params are removed from product.
     * The onlyTrees parameter allow to limit the scope of the removing or setting
     * of categories to specific trees
     *
     * @param ProductInterface $product
     * @param ArrayCollection  $categories
     * @param array            $onlyTrees
     */
    public function setCategories(
        ProductInterface $product,
        ArrayCollection $categories = null,
        array $onlyTrees = null
    ) {
        // Remove current categories
        $currentCategories = $product->getCategories();
        foreach ($currentCategories as $currentCategory) {
            if ($onlyTrees != null &&
               in_array($currentCategory->getRoot(), $onlyTrees)) {
                $currentCategory->removeProduct($product);
            }
        }

        // Add new categories
        foreach ($categories as $category) {
            if ($onlyTrees != null &&
               in_array($category->getRoot(), $onlyTrees)) {
                $category->addProduct($product);
            }
        }
    }

    /**
     * Return the identifier attribute
     * @return ProductAttribute|null
     */
    public function getIdentifierAttribute()
    {
        return $this->getAttributeRepository()->findOneBy(array('attributeType' => 'pim_product_identifier'));
    }

    /**
     * Add empty values for product family and product-specific attributes for relevant scopes and locales
     *
     * It makes sure that if an attribute is translatable/scopable, then all values
     * in the required locales/channels exist. If the attribute is not scopable or
     * translatable, makes sure that a single value exists.
     *
     * @param ProductInterface $product
     *
     * @return null
     */
    protected function ensureRequiredAttributeValues(ProductInterface $product)
    {
        $channels  = $this->getChannels();
        $locales = $product->getLocales();
        $attributes = $product->getAttributes();

        if ($family = $product->getFamily()) {
            foreach ($family->getAttributes() as $attribute) {
                $attributes[] = $attribute;
            }
        }

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
                    foreach ($locales as $locale) {
                        $requiredValues[] = $channel->getCode() . ':' . $locale->getCode();
                    }
                }
            } elseif ($attribute->getTranslatable()) {
                foreach ($locales as $locale) {
                    $requiredValues[] = ':' . $locale->getCode();
                }
            } else {
                $requiredValues[] = ':';
            }

            $missingValues = array_diff($requiredValues, $existingValues);

            $redundantValues = array_diff($existingValues, $requiredValues);

            foreach ($missingValues as $value) {
                $value = explode(':', $value);
                $scope = $value[0] === '' ? null : $value[0];
                $locale = $value[1] === '' ? null : $value[1];
                $this->addProductValue($product, $attribute, $locale, $scope);
            }

            foreach ($redundantValues as $value) {
                $value = explode(':', $value);
                $scope = $value[0] === '' ? null : $value[0];
                $locale = $value[1] === '' ? null : $value[1];
                $this->removeProductValue($product, $attribute, $locale, $scope);
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
        $value = $this->createFlexibleValue();
        if ($locale) {
            $value->setLocale($locale);
        }
        $value->setScope($scope);
        $value->setAttribute($attribute);

        $product->addValue($value);
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
     * @param ProductInterface $product
     */
    protected function handleMedia(ProductInterface $product)
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
     * @param ProductInterface $product
     * @param ProductValue     $value
     *
     * @return string
     */
    protected function generateFilenamePrefix(ProductInterface $product, ProductValue $value)
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
