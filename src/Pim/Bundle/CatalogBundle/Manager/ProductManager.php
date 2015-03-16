<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
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
class ProductManager
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
     * Return the identifier attribute
     *
     * @return AttributeInterface|null
     *
     * @deprecated will be remove in 1.5, please use AttributeRepositoryInterface::getIdentifierAttribute();
     */
    public function getIdentifierAttribute()
    {
        return $this->attributeRepository->getIdentifier();
    }

    /**
     * Create a product
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductInterface
     *
     * @deprecated will be remove in 1.5, please use ProductBuilderInterface::createProduct();
     */
    public function createProduct()
    {
        return $this->builder->createProduct();
    }

    /**
     * Get product FQCN
     *
     * @return string
     *
     * @deprecated will be remove in 1.5, please use %pim_catalog.entity.product.class%
     */
    public function getProductName()
    {
        return $this->configuration['product_class'];
    }

    /**
     * Get product value FQCN
     *
     * @return string
     *
     * @deprecated will be remove in 1.5, please use %pim_catalog.entity.product_value.class%
     */
    public function getProductValueName()
    {
        return $this->configuration['product_value_class'];
    }

    /**
     * Get attribute FQCN
     *
     * @return string
     *
     * @deprecated will be remove in 1.5, please use %pim_catalog.entity.attribute.class%
     */
    public function getAttributeName()
    {
        return $this->configuration['attribute_class'];
    }

    /**
     * @param ProductInterface $product
     *
     * @deprecated will be remove in 1.5, please use ProductBuilderInterface::addMissingAssociations
     */
    public function ensureAllAssociationTypes(ProductInterface $product)
    {
        $this->builder->addMissingAssociations($product);
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
