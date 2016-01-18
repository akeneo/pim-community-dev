<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
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
     *
     * @deprecated will be remove in 1.5, please use parameters as %pim_catalog.entity.product.class%
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Find a product by id
     * Also ensure that it contains all required values
     *
     * @param int $id
     *
     * @return ProductInterface|null
     */
    public function find($id)
    {
        $product = $this->getProductRepository()->findOneByWithValues($id);

        return $product;
    }

    /**
     * Find a product by identifier
     *
     * @param string $identifier
     *
     * @return ProductInterface|null
     *
     * @deprecated will be removed in 1.5, please use ProductRepositoryInterface::findOneByIdentifier
     */
    public function findByIdentifier($identifier)
    {
        $product = $this->getProductRepository()->findOneByIdentifier($identifier);

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
     *
     * @deprecated Will be removed in 1.5, please use ProductBuilderInterface::removeAttributeFromProduct() and
     *             ProductSaver::save() instead.
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
     * @deprecated will be removed in 1.5, please use AttributeRepositoryInterface::getIdentifier();
     */
    public function getIdentifierAttribute()
    {
        return $this->attributeRepository->getIdentifier();
    }

    /**
     * Create a product
     *
     * @return ProductInterface
     *
     * @deprecated will be removed in 1.5, please use ProductBuilderInterface::createProduct();
     */
    public function createProduct()
    {
        return $this->builder->createProduct();
    }

    /**
     * Create a product value
     *
     * @return ProductValueInterface
     *
     * @deprecated will be removed in 1.5, please use ProductBuilderInterface::createProductValue();
     */
    public function createProductValue(AttributeInterface $attribute)
    {
        return $this->builder->createProductValue($attribute);
    }

    /**
     * Get product FQCN
     *
     * @return string
     *
     * @deprecated will be removed in 1.5, please use %pim_catalog.entity.product.class%
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
     * @deprecated will be removed in 1.5, please use %pim_catalog.entity.product_value.class%
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
     * @deprecated will be removed in 1.5, please use %pim_catalog.entity.attribute.class%
     */
    public function getAttributeName()
    {
        return $this->configuration['attribute_class'];
    }

    /**
     * @param ProductInterface $product
     *
     * @deprecated will be removed in 1.5, please use ProductBuilderInterface::addMissingAssociations
     */
    public function ensureAllAssociationTypes(ProductInterface $product)
    {
        $this->builder->addMissingAssociations($product);
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
     *
     * @deprecated will be removed in 1.5, please use AttributeOptionRepositoryInterface
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
     * @return bool
     *
     * @deprecated will be removed in 1.5, please use ProductRepositoryInterface::valueExists
     */
    public function valueExists(ProductValueInterface $value)
    {
        return $this->productRepository->valueExists($value);
    }
}
