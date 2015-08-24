<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Event\ProductEvent;
use Pim\Bundle\CatalogBundle\Event\ProductEvents;
use Pim\Bundle\CatalogBundle\Event\ProductValueEvent;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\Association;
use Pim\Bundle\CatalogBundle\Model\AvailableAttributes;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\AssociationTypeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeOptionRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Product manager
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductManager implements ProductManagerInterface
{
    /** @var array */
    protected $configuration;

    /** @var SaverInterface */
    protected $productSaver;

    /** @var BulkSaverInterface */
    protected $productBulkSaver;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var MediaManager */
    protected $mediaManager;

    /** @var ProductBuilder */
    protected $builder;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var AssociationTypeRepositoryInterface */
    protected $assocTypeRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var AttributeOptionRepositoryInterface */
    protected $attOptionRepository;

    /**
     * Constructor
     *
     * @param array                              $configuration
     * @param ObjectManager                      $objectManager
     * @param SaverInterface                     $productSaver
     * @param BulkSaverInterface                 $productBulkSaver
     * @param EventDispatcherInterface           $eventDispatcher
     * @param MediaManager                       $mediaManager
     * @param ProductBuilder                     $builder
     * @param ProductRepositoryInterface         $productRepository
     * @param AssociationTypeRepositoryInterface $assocTypeRepository
     * @param AttributeRepositoryInterface       $attributeRepository
     * @param AttributeOptionRepositoryInterface $attOptionRepository
     */
    public function __construct(
        $configuration,
        ObjectManager $objectManager,
        SaverInterface $productSaver,
        BulkSaverInterface $productBulkSaver,
        EventDispatcherInterface $eventDispatcher,
        MediaManager $mediaManager,
        ProductBuilder $builder,
        ProductRepositoryInterface $productRepository,
        AssociationTypeRepositoryInterface $assocTypeRepository,
        AttributeRepositoryInterface $attributeRepository,
        AttributeOptionRepositoryInterface $attOptionRepository
    ) {
        $this->configuration = $configuration;
        $this->productSaver = $productSaver;
        $this->productBulkSaver = $productBulkSaver;
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->mediaManager = $mediaManager;
        $this->builder = $builder;
        $this->productRepository = $productRepository;
        $this->assocTypeRepository = $assocTypeRepository;
        $this->attributeRepository = $attributeRepository;
        $this->attOptionRepository = $attOptionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductRepository()
    {
        return $this->productRepository;
    }

    /**
     * Get product configuration
     *
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Find a product by id
     * Also ensure that it contains all required values
     *
     * @param integer $id
     *
     * @return ProductInterface|null
     */
    public function find($id)
    {
        $product = $this->getProductRepository()->findOneByWithValues($id);

        if ($product) {
            $this->builder->addMissingProductValues($product);
        }

        return $product;
    }

    /**
     * Find a product by identifier
     * Also ensure that it contains all required values
     *
     * @param string $identifier
     *
     * @return ProductInterface|null
     */
    public function findByIdentifier($identifier)
    {
        $product = $this->getProductRepository()->findOneByIdentifier($identifier);
        if ($product) {
            $this->builder->addMissingProductValues($product);
        }

        return $product;
    }

    /**
     * Creates required value(s) to add the attribute to the product
     *
     * @param ProductInterface    $product
     * @param AvailableAttributes $availableAttributes
     * @param array               $savingOptions
     */
    public function addAttributesToProduct(
        ProductInterface $product,
        AvailableAttributes $availableAttributes,
        array $savingOptions = []
    ) {
        foreach ($availableAttributes->getAttributes() as $attribute) {
            $this->builder->addAttributeToProduct($product, $attribute);
        }

        $options = array_merge(['recalculate' => false, 'schedule' => false], $savingOptions);
        $this->productSaver->save($product, $options);
    }

    /**
     * Deletes values that link an attribute to a product
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param array              $savingOptions
     */
    public function removeAttributeFromProduct(
        ProductInterface $product,
        AttributeInterface $attribute,
        array $savingOptions = []
    ) {
        foreach ($product->getValues() as $value) {
            if ($attribute === $value->getAttribute()) {
                $product->removeValue($value);
            }
        }

        $options = array_merge(['recalculate' => false, 'schedule' => false], $savingOptions);
        $this->productSaver->save($product, $options);
    }

    /**
     * Save a product
     *
     * @param ProductInterface $product The product to save
     * @param array            $options Saving options
     *
     * @deprecated will be removed in 1.4, use save()
     */
    public function saveProduct(ProductInterface $product, array $options = [])
    {
        $this->productSaver->save($product, $options);
    }

    /**
     * Save multiple products
     *
     * @param ProductInterface[] $products The products to save
     * @param array              $options  Saving options
     *
     * @deprecated will be removed in 1.4, use saveAll()
     */
    public function saveAllProducts(array $products, array $options = [])
    {
        $this->productBulkSaver->saveAll($products, $options);
    }

    /**
     * Return the identifier attribute
     *
     * @return AttributeInterface|null
     */
    public function getIdentifierAttribute()
    {
        return $this->attributeRepository->findOneBy(['attributeType' => 'pim_catalog_identifier']);
    }

    /**
     * Create a product
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductInterface
     */
    public function createProduct()
    {
        $class = $this->getProductName();

        $product = new $class();
        $event = new ProductEvent($this, $product);
        $this->eventDispatcher->dispatch(ProductEvents::CREATE, $event);

        return $product;
    }

    /**
     * Create a product value
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductValueInterface
     */
    public function createProductValue()
    {
        $class = $this->getProductValueName();
        $value = new $class();

        $event = new ProductValueEvent($this, $value);
        $this->eventDispatcher->dispatch(ProductEvents::CREATE_VALUE, $event);

        return $value;
    }

    /**
     * Get product FQCN
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->configuration['product_class'];
    }

    /**
     * Get product value FQCN
     *
     * @return string
     */
    public function getProductValueName()
    {
        return $this->configuration['product_value_class'];
    }

    /**
     * Get attribute FQCN
     *
     * @return string
     */
    public function getAttributeName()
    {
        return $this->configuration['attribute_class'];
    }

    /**
     * @param ProductInterface $product
     *
     * @return null
     *
     * @deprecated will be removed in 1.4, replaced by MediaManager::handleProductMedias
     */
    public function handleMedia(ProductInterface $product)
    {
        return $this->mediaManager->handleProductMedias($product);
    }

    /**
     * @param ProductInterface[] $products
     *
     * @return null
     *
     * @deprecated will be removed in 1.4, replaced by MediaManager::handleAllProductsMedias
     */
    public function handleAllMedia(array $products)
    {
        return $this->mediaManager->handleAllProductsMedias($products);
    }

    /**
     * @param ProductInterface $product
     */
    public function ensureAllAssociationTypes(ProductInterface $product)
    {
        $missingAssocTypes = $this->assocTypeRepository->findMissingAssociationTypes($product);

        if (!empty($missingAssocTypes)) {
            foreach ($missingAssocTypes as $associationType) {
                $association = new Association();
                $association->setAssociationType($associationType);
                $product->addAssociation($association);
            }
        }
    }

    /**
     * Remove products
     *
     * @param integer[] $ids
     *
     * @deprecated will be removed in 1.4, replaced by ProductRemover::removeAll
     */
    public function removeAll(array $ids)
    {
        $products = $this->getProductRepository()->findByIds($ids);
        foreach ($products as $product) {
            $this->remove($product, false);
        }
        $this->objectManager->flush();
    }

    /**
     * Remove a product
     *
     * @param ProductInterface $product
     * @param boolean          $flush
     *
     * @deprecated will be removed in 1.4, replaced by ProductRemover::remove
     */
    public function remove(ProductInterface $product, $flush = true)
    {
        $this->objectManager->remove($product);
        if (true === $flush) {
            $this->objectManager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeRepository()
    {
        return $this->attributeRepository;
    }

    /**
     * Return related repository
     *
     * @return ObjectRepository
     */
    public function getAttributeOptionRepository()
    {
        return $this->attOptionRepository;
    }

    /**
     * Get object manager
     *
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * Check if a product value with a specific value already exists
     *
     * @param ProductValueInterface $value
     *
     * @return boolean
     */
    public function valueExists(ProductValueInterface $value)
    {
        return $this->productRepository->valueExists($value);
    }
}
