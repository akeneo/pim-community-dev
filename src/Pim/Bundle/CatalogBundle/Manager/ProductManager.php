<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\NoResultException;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\ProductAssociation;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;

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
     * @var CompletenessManager
     */
    protected $completenessManager;

    /**
     * @var ProductBuilder
     */
    protected $builder;

    /**
     * Constructor
     *
     * @param string                   $flexibleName         Entity name
     * @param array                    $flexibleConfig       Global flexible entities configuration array
     * @param ObjectManager            $storageManager       Storage manager
     * @param EventDispatcherInterface $eventDispatcher      Event dispatcher
     * @param AttributeTypeFactory     $attributeTypeFactory Attribute type factory
     * @param MediaManager             $mediaManager         Media manager
     * @param CompletenessManager      $completenessManager  Completeness manager
     * @param ProductBuilder           $builder              Product builder
     */
    public function __construct(
        $flexibleName,
        $flexibleConfig,
        ObjectManager $storageManager,
        EventDispatcherInterface $eventDispatcher,
        AttributeTypeFactory $attributeTypeFactory,
        MediaManager $mediaManager,
        CompletenessManager $completenessManager,
        ProductBuilder $builder
    ) {
        parent::__construct(
            $flexibleName,
            $flexibleConfig,
            $storageManager,
            $eventDispatcher,
            $attributeTypeFactory
        );

        $this->mediaManager         = $mediaManager;
        $this->completenessManager  = $completenessManager;
        $this->builder              = $builder;
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
        $products = $this->getFlexibleRepository()->findByIds($ids);

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
        $code = $this->getIdentifierAttribute()->getCode();

        $products = $this->getFlexibleRepository()->findByWithAttributes(array(), array($code => $identifier));
        $product = reset($products);

        if ($product) {
            $this->builder->addMissingProductValues($product);
        }

        return $product;
    }

    /**
     * Returns a product for the import process
     *
     * @param array            $attributes
     * @param ProductAttribute $identifierAttribute
     * @param string           $code
     *
     * @return ProductInterface
     */
    public function getImportProduct($attributes, $identifierAttribute, $code)
    {
        $class = $this->getFlexibleRepository()->getClassName();
        $em = $this->getStorageManager();
        try {
            $id = $em->createQuery(
                'SELECT p.id FROM ' . $class . ' p '.
                'INNER JOIN p.values v '  .
                'WHERE v.attribute=:identifier_attribute ' .
                'AND v.' . $identifierAttribute->getBackendType() .'=:code'
            )
                ->setParameter('identifier_attribute', $identifierAttribute)
                ->setParameter('code', $code)
                ->getSingleScalarResult();
        } catch (NoResultException $ex) {
            return null;
        }

        return $em->createQuery(
            'SELECT p, v, f, o, pr ' .
            'FROM ' . $class . ' p ' .
            'LEFT JOIN p.family f ' .
            'LEFT JOIN p.values v WITH v.attribute IN (:attributes) ' .
            'LEFT JOIN v.options o ' .
            'LEFT JOIN v.prices pr ' .
            'WHERE p.id=:id'
        )
            ->setParameter('attributes', array_values($attributes))
            ->setParameter('id', $id)
            ->getSingleResult();
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
        $this->builder->addAttributeToProduct($product, $attribute);
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
        $this->builder->removeAttributeFromProduct($product, $attribute);
    }

    /**
     * Save a product
     *
     * @param ProductInterface $product     The product to save
     * @param boolean          $recalculate Whether or not to directly recalculate the completeness
     * @param boolean          $flush       Whether or not to flush the entity manager
     */
    public function save(ProductInterface $product, $recalculate = true, $flush = true)
    {
        $this->storageManager->persist($product);

        if ($flush) {
            $this->storageManager->flush();
        }
        $this->completenessManager->schedule($product);

        if ($recalculate) {
            $this->completenessManager->createProductCompletenesses($product);
        }
    }

    /**
     * Save multiple products
     *
     * @param ProductInterface[] $products    The products to save
     * @param boolean            $recalculate Wether or not to directly recalculate the completeness
     * @param boolean            $flush       Wether or not to flush the entity manager
     */
    public function saveAll(array $products, $recalculate = false, $flush = true)
    {
        foreach ($products as $product) {
            $this->save($product, $recalculate, false);
        }

        if ($flush) {
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
            $this->storageManager->flush();
        }
    }

    /**
     * Remove products
     *
     * @param integer[] $ids
     */
    public function removeAll(array $ids)
    {
        $products = $this->getFlexibleRepository()->findByIds($ids);
        foreach ($products as $product) {
            $this->storageManager->remove($product);
        }
        $this->storageManager->flush();
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
}
