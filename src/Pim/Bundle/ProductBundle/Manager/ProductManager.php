<?php

namespace Pim\Bundle\ProductBundle\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\ProductBundle\Model\ProductInterface;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\ProductBundle\Entity\ProductValue;
use Pim\Bundle\ConfigBundle\Manager\CurrencyManager;

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
     * Creates required value(s) to add the attribute to the product
     *
     * @param ProductInterface $product
     * @param ProductAttribute $attribute
     */
    public function addAttributeToProduct(ProductInterface $product, ProductAttribute $attribute)
    {
        $requiredValues = $this->computeRequiredValues($product, $attribute);

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
        $values = $this->getFlexibleValueRepository()->findBy(
            array(
                'entity'    => $product,
                'attribute' => $attribute,
            )
        );

        foreach ($values as $value) {
            $this->storageManager->remove($value);
        }

        $this->storageManager->flush();
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
     *
     * @throws LogicException When a the product is assigned to a root category
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
                if ($category->getParent() == null) {
                    throw new \LogicException("A product cannot be assigned to a root category");
                }
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
        $attributes = $product->getAttributes();

        if ($family = $product->getFamily()) {
            foreach ($family->getAttributes() as $attribute) {
                $attributes[] = $attribute;
            }
        }

        $attributes = array_unique($attributes);

        foreach ($attributes as $attribute) {
            $requiredValues = $this->computeRequiredValues($product, $attribute);
            $existingValues = array();

            foreach ($product->getValues() as $value) {
                if ($value->getAttribute() === $attribute) {
                    $existingValues[] = array('locale' => $value->getLocale(), 'scope' => $value->getScope());
                }
            }

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
    }

    /**
     * Returns an array of values that are required to link product to an attribute
     * Each value is returned as an array with 'scope' and 'locale' keys
     *
     * @param ProductInterface $product
     * @param ProductAttribute $attribute
     *
     * @return array:array
     */
    protected function computeRequiredValues(ProductInterface $product, ProductAttribute $attribute)
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
            foreach ($product->getLocales() as $locale) {
                $requiredValues[] = array('locale' => $locale->getCode(), 'scope' => null);
            }
        } else {
            $requiredValues[] = array('locale' => null, 'scope' => null);
        }

        return $requiredValues;
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
            if ($media = $value->getMedia()) {
                $this->mediaManager->handle(
                    $value->getMedia(),
                    $media->getFile() ? $this->generateFilenamePrefix($product, $value) : null
                );
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
            $product->getIdentifier(),
            $value->getAttribute()->getCode(),
            $value->getLocale(),
            $value->getScope(),
            time()
        );
    }
}
