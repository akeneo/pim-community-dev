<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\ProductAssociation;
use Pim\Bundle\CatalogBundle\Entity\ProductValue;
use Pim\Bundle\CatalogBundle\Manager\CurrencyManager;
use Pim\Bundle\CatalogBundle\Calculator\CompletenessCalculator;

/**
 * Product manager
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductManager extends FlexibleManager
{
    /**
     * @var MediaManager $mediaManager
     */
    protected $mediaManager;

    /**
     * @var CurrencyManager
     */
    protected $currencyManager;

    /**
     * @var CompletenessCalculator
     */
    protected $completenessCalculator;

    /**
     * Constructor
     *
     * @param string                   $flexibleName           Entity name
     * @param array                    $flexibleConfig         Global flexible entities configuration array
     * @param ObjectManager            $storageManager         Storage manager
     * @param EventDispatcherInterface $eventDispatcher        Event dispatcher
     * @param AttributeTypeFactory     $attributeTypeFactory   Attribute type factory
     * @param MediaManager             $mediaManager           Media manager
     * @param CurrencyManager          $currencyManager        Currency manager
     * @param CompletenessCalculator   $completenessCalculator Completeness calculator
     */
    public function __construct(
        $flexibleName,
        $flexibleConfig,
        ObjectManager $storageManager,
        EventDispatcherInterface $eventDispatcher,
        AttributeTypeFactory $attributeTypeFactory,
        MediaManager $mediaManager,
        CurrencyManager $currencyManager,
        CompletenessCalculator $completenessCalculator
    ) {
        parent::__construct(
            $flexibleName,
            $flexibleConfig,
            $storageManager,
            $eventDispatcher,
            $attributeTypeFactory
        );

        $this->mediaManager           = $mediaManager;
        $this->currencyManager        = $currencyManager;
        $this->completenessCalculator = $completenessCalculator;
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
     * @param integer $id
     *
     * @return Product|null
     */
    public function find($id)
    {
        $product = $this->getFlexibleRepository()->findWithSortedAttribute($id);

        if ($product) {
            $this->addMissingProductValues($product);
        }

        return $product;
    }

    /**
     * Find products by id
     * Also ensure that they contain all required values
     *
     * @param integer[] $ids
     *
     * @return ProductInterface[]
     */
    public function findByIds(array $ids)
    {
        $products = $this->getFlexibleRepository()->findByIds($ids);

        foreach ($products as $product) {
            $this->addMissingProductValues($product);
        }

        return $products;
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
            $this->addMissingProductValues($product);
        }

        return $product;
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
     * Save a product
     *
     * @param ProductInterface $product
     * @param boolean          $calculateCompleteness
     *
     * @return null
     */
    public function save(ProductInterface $product, $calculateCompleteness = true)
    {
        $this->storageManager->persist($product);
        $this->storageManager->flush();
        if ($calculateCompleteness) {
            $this->storageManager->refresh($product);
            $this->completenessCalculator->calculateForAProduct($product);
            $this->storageManager->flush();
        }
    }

    /**
     * Return the identifier attribute
     *
     * @return ProductAttribute|null
     */
    public function getIdentifierAttribute()
    {
        return $this->getAttributeRepository()->findOneBy(array('attributeType' => 'pim_catalog_identifier'));
    }

    /**
     * Create a product (alias of createFlexible)
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductInterface
     */
    public function createProduct()
    {
        $product =  parent::createFlexible();

        return $product;
    }

    /**
     * Create a product value (alias of createFlexibleValue)
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductValueInterface
     */
    public function createProductValue()
    {
        return parent::createFlexibleValue();
    }

    /**
     * @param ProductInterface $product
     */
    public function handleMedia(ProductInterface $product)
    {
        foreach ($product->getValues() as $value) {
            if ($media = $value->getMedia()) {
                $filenamePrefix =  $media->getFile() ? $this->generateFilenamePrefix($product, $value) : null;
                $this->mediaManager->handle($media, $filenamePrefix);
            }
        }
    }

    /**
     * @param ProductInterface $product
     */
    public function ensureAllAssociations(ProductInterface $product)
    {
        $missingAssociations = $this->storageManager
            ->getRepository('PimCatalogBundle:Association')
            ->findMissingAssociations($product);

        if (!empty($missingAssociations)) {
            foreach ($missingAssociations as $association) {
                $productAssociation = new ProductAssociation();
                $productAssociation->setAssociation($association);
                $product->addProductAssociation($productAssociation);
            }
            $this->save($product, false);
        }
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
    protected function addMissingProductValues(ProductInterface $product)
    {
        $attributes = $product->getAttributes();

        if ($family = $product->getFamily()) {
            foreach ($family->getAttributes() as $attribute) {
                $attributes[] = $attribute;
            }
        }

        if (!is_array($attributes)) {
            return;
        }

        $attributes = array_unique($attributes);

        foreach ($attributes as $attribute) {
            $requiredValues = $this->getExpectedValues($attribute);
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

        $this->addMissingPrices($product);
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
     * Return available channels
     *
     * @return ArrayCollection
     */
    protected function getChannels()
    {
        return $this->storageManager->getRepository('PimCatalogBundle:Channel')->findAll();
    }

    /**
     * Return available locales
     *
     * @return ArrayCollection
     */
    protected function getLocales()
    {
        return $this->storageManager->getRepository('PimCatalogBundle:Locale')->getActivatedLocales();
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
