<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Event\FilterProductEvent;
use Pim\Bundle\CatalogBundle\Event\FilterProductValueEvent;
use Pim\Bundle\CatalogBundle\CatalogEvents;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\Association;
use Pim\Bundle\CatalogBundle\Model\AvailableAttributes;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeOptionRepository;
use Pim\Bundle\CatalogBundle\Persistence\ProductPersister;

/**
 * Product manager
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductManager
{
    /** @var array */
    protected $configuration;

    /** @var ProductPersister */
    protected $persister;

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
     * @param ProductPersister           $persister
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
        ProductPersister $persister,
        EventDispatcherInterface $eventDispatcher,
        MediaManager $mediaManager,
        ProductBuilder $builder,
        ProductRepositoryInterface $productRepository,
        AssociationTypeRepository $assocTypeRepository,
        AttributeRepository $attributeRepository,
        AttributeOptionRepository $attOptionRepository
    ) {
        $this->configuration = $configuration;
        $this->persister = $persister;
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
     * @return Product|null
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
        $product = $this->getProductRepository()->findOneBy(
            [
                [
                    'attribute' => $this->getIdentifierAttribute(),
                    'value' => $identifier
                ]
            ]
        );

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
     *
     * @return null
     */
    public function addAttributesToProduct(ProductInterface $product, AvailableAttributes $availableAttributes)
    {
        foreach ($availableAttributes->getAttributes() as $attribute) {
            $this->builder->addAttributeToProduct($product, $attribute);
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
        $this->builder->removeAttributeFromProduct($product, $attribute);
    }

    /**
     * Save a product
     *
     * @param ProductInterface $product     The product to save
     * @param boolean          $recalculate Whether or not to directly recalculate the completeness
     * @param boolean          $flush       Whether or not to flush the entity manager
     * @param boolean          $schedule    Whether or not to schedule the product for completeness recalculation
     *
     * @return null
     *
     * @deprecated use saveProduct() instead. Will be removed in 1.3
     */
    public function save(ProductInterface $product, $recalculate = true, $flush = true, $schedule = true)
    {
        $options = [
            'recalculate' => $recalculate,
            'flush' => $flush,
            'schedule' => $schedule,
        ];

        return $this->saveProduct($product, $options);
    }

    /**
     * Save a product
     *
     * @param ProductInterface $product The product to save
     * @param array            $options Saving options
     *
     * @return null
     */
    public function saveProduct(ProductInterface $product, array $options)
    {
        $options = array_merge(
            [
                'recalculate' => true,
                'flush' => true,
                'schedule' => true,
            ],
            $options
        );

        return $this->persister->persist($product, $options);
    }

    /**
     * Save multiple products
     *
     * @param ProductInterface[] $products    The products to save
     * @param boolean            $recalculate Whether or not to directly recalculate the completeness
     * @param boolean            $flush       Whether or not to flush the entity manager
     * @param boolean            $schedule    Whether or not to schedule the product for completeness recalculation
     */
    public function saveAll(array $products, $recalculate = false, $flush = true, $schedule = true)
    {
        foreach ($products as $product) {
            $this->save($product, $recalculate, false, $schedule);
        }

        if ($flush) {
            $this->objectManager->flush();
        }
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
        $event = new FilterProductEvent($this, $product);
        $this->eventDispatcher->dispatch(CatalogEvents::CREATE_PRODUCT, $event);

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

        $event = new FilterProductValueEvent($this, $value);
        $this->eventDispatcher->dispatch(CatalogEvents::CREATE_PRODUCT_VALUE, $event);

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
     */
    public function handleMedia(ProductInterface $product)
    {
        foreach ($product->getValues() as $value) {
            if ($media = $value->getMedia()) {
                if ($id = $media->getCopyFrom()) {
                    $source = $this
                        ->objectManager
                        ->getRepository('Pim\Bundle\CatalogBundle\Model\Media')
                        ->find($id);

                    if (!$source) {
                        throw new \Exception(
                            sprintf('Could not find media with id %d', $id)
                        );
                    }

                    $this->mediaManager->duplicate($source, $media, $this->generateFilenamePrefix($product, $value));
                } else {
                    $filenamePrefix =  $media->getFile() ? $this->generateFilenamePrefix($product, $value) : null;
                    $this->mediaManager->handle($media, $filenamePrefix);
                }
            }
        }
    }

    /**
     * @param ProductInterface[] $products
     */
    public function handleAllMedia(array $products)
    {
        foreach ($products as $product) {
            if (!$product instanceof \Pim\Bundle\CatalogBundle\Model\ProductInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Expected instance of Pim\Bundle\CatalogBundle\Model\ProductInterface, got %s',
                        get_class($product)
                    )
                );
            }
            $this->handleMedia($product);
        }
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
     */
    public function removeAll(array $ids)
    {
        $products = $this->getProductRepository()->findByIds($ids);
        foreach ($products as $product) {
            $this->objectManager->remove($product);
        }
        $this->objectManager->flush();
    }

    /**
     * @param ProductInterface      $product
     * @param ProductValueInterface $value
     *
     * @return string
     */
    protected function generateFilenamePrefix(ProductInterface $product, ProductValueInterface $value)
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
