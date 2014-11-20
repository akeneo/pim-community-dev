<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Resource\Model\SaverInterface;
use Pim\Component\Resource\Model\BulkSaverInterface;
use Pim\Component\Resource\Model\RemoverInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Event\ProductEvent;
use Pim\Bundle\CatalogBundle\Event\ProductEvents;
use Pim\Bundle\CatalogBundle\Event\ProductValueEvent;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\Association;
use Pim\Bundle\CatalogBundle\Model\AvailableAttributes;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Product manager
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductManager implements SaverInterface, BulkSaverInterface, RemoverInterface
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

    /** @var AssociationTypeRepository */
    protected $assocTypeRepository;

    /** @var AttributeRepository */
    protected $attributeRepository;

    /** @var AttributeOptionRepository */
    protected $attOptionRepository;

    /**
     * Constructor
     *
     * @param array                      $configuration
     * @param ObjectManager              $objectManager
     * @param SaverInterface             $productSaver
     * @param BulkSaverInterface         $productBulkSaver
     * @param EventDispatcherInterface   $eventDispatcher
     * @param MediaManager               $mediaManager
     * @param ProductBuilder             $builder
     * @param ProductRepositoryInterface $productRepository
     * @param AssociationTypeRepository  $assocTypeRepository
     * @param AttributeRepository        $attributeRepository
     * @param AttributeOptionRepository  $attOptionRepository
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
        AssociationTypeRepository $assocTypeRepository,
        AttributeRepository $attributeRepository,
        AttributeOptionRepository $attOptionRepository
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
     * @return ProductRepositoryInterface
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
     * Find products by id
     * Also ensure that they contain all required values
     *
     * @param integer[] $ids
     *
     * @return ProductInterface[]
     */
    public function findByIds(array $ids)
    {
        $products = $this->getProductRepository()->findByIds($ids);

        foreach ($products as $product) {
            $this->builder->addMissingProductValues($product);
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
        $this->save($product, $options);
    }

    /**
     * Deletes values that link an attribute to a product
     *
     * @param ProductInterface  $product
     * @param AbstractAttribute $attribute
     * @param array             $savingOptions
     */
    public function removeAttributeFromProduct(
        ProductInterface $product,
        AbstractAttribute $attribute,
        array $savingOptions = []
    ) {
        foreach ($product->getValues() as $value) {
            if ($attribute === $value->getAttribute()) {
                $product->removeValue($value);
            }
        }

        $options = array_merge(['recalculate' => false, 'schedule' => false], $savingOptions);
        $this->save($product, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = [])
    {
        $this->productSaver->save($object, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $objects, array $options = [])
    {
        $this->productBulkSaver->saveAll($objects, $options);
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
        $this->save($product, $options);
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
        $this->saveAll($products, $options);
    }

    /**
     * Return the identifier attribute
     *
     * @return AbstractAttribute|null
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
     * @deprecated will be removed in 1.4, replaced by MediaManager::handleProductMedias
     */
    public function handleMedia(ProductInterface $product)
    {
        return $this->mediaManager->handleProductMedias($product);
    }

    /**
     * @param ProductInterface[] $products
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
     * {@inheritdoc}
     */
    public function remove($object, array $options = [])
    {
        if (!$object instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a Pim\Bundle\CatalogBundle\Model\ProductInterface, "%s" provided',
                    ClassUtils::getClass($object)
                )
            );
        }

        $options = array_merge(['flush' => true], $options);
        $this->eventDispatcher->dispatch(ProductEvents::PRE_REMOVE, new GenericEvent($object));
        $this->objectManager->remove($object);
        $this->eventDispatcher->dispatch(ProductEvents::POST_REMOVE, new GenericEvent($object));

        if (true === $options['flush']) {
            $this->objectManager->flush();
        }
    }

    /**
     * Return related repository
     *
     * @return ObjectRepository
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
