<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\FlexibleEntityBundle\Event\FilterFlexibleEvent;
use Pim\Bundle\FlexibleEntityBundle\FlexibleEntityEvents;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\Association;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;
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
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ProductBuilder
     */
    protected $builder;

    /**
     * @var EntityManager Used for purely entity stuff
     */
    protected $entityManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $repository;

    /**
     * Constructor
     *
     * @param string                     $flexibleName        Entity name
     * @param array                      $flexibleConfig      Global flexible entities configuration array
     * @param ObjectManager              $objectManager       Storage manager for product
     * @param EntityManager              $entityManager       Entity manager for other entitites
     * @param EventDispatcherInterface   $eventDispatcher     Event dispatcher
     * @param MediaManager               $mediaManager        Media manager
     * @param CompletenessManager        $completenessManager Completeness manager
     * @param ProductBuilder             $builder             Product builder
     * @param ProductRepositoryInterface $repo
     */
    public function __construct(
        $flexibleName,
        $flexibleConfig,
        ObjectManager $objectManager,
        EntityManager $entityManager,
        EventDispatcherInterface $eventDispatcher,
        MediaManager $mediaManager,
        CompletenessManager $completenessManager,
        ProductBuilder $builder,
        ProductRepositoryInterface $repo
    ) {
        parent::__construct(
            $flexibleName,
            $flexibleConfig,
            $objectManager,
            $eventDispatcher
        );

        $this->entityManager       = $entityManager;
        $this->mediaManager        = $mediaManager;
        $this->completenessManager = $completenessManager;
        $this->builder             = $builder;
        $this->repository          = $repo;
    }

    /**
     * @return ProductRepositoryInterface
     */
    public function getFlexibleRepository()
    {
        return $this->repository;
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
     * Creates required value(s) to add the attribute to the product
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     *
     * @return null
     */
    public function addAttributeToProduct(ProductInterface $product, AttributeInterface $attribute)
    {
        $this->builder->addAttributeToProduct($product, $attribute);
    }

    /**
     * Deletes values that link an attribute to a product
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     *
     * @return boolean
     */
    public function removeAttributeFromProduct(ProductInterface $product, AttributeInterface $attribute)
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
        $this->objectManager->persist($product);

        if ($flush) {
            $this->objectManager->flush();
        }
        $this->completenessManager->schedule($product);

        if ($recalculate) {
            $this->completenessManager->generateProductCompletenesses($product);
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
            $this->objectManager->flush();
        }
    }

    /**
     * Return the identifier attribute
     *
     * @return AttributeInterface|null
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
        $product = $this->createFlexible();

        return $product;
    }

    /**
     * Create a product value (alias of createFlexibleValue)
     *
     * @return \Pim\Bundle\CatalogBundle\Model\ProductValueInterface
     */
    public function createProductValue()
    {
        return $this->createFlexibleValue();
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
        $missingAssocTypes = $this->entityManager
            ->getRepository('PimCatalogBundle:AssociationType')
            ->findMissingAssociationTypes($product);

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
        $products = $this->getFlexibleRepository()->findByIds($ids);
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
     * FIXME_MONGO: Use an AttributeManager instead of using the same
     * objectManager than the one used by the FlexibleEntity
     *
     * All methods overload below are linked to that issue
     */
    /**
     * Return related repository
     *
     * @return ObjectRepository
     */
    public function getAttributeRepository()
    {
        return $this->entityManager->getRepository($this->getAttributeName());
    }

    /**
     * Return related repository
     *
     * @return ObjectRepository
     */
    public function getAttributeOptionRepository()
    {
        return $this->entityManager->getRepository($this->getAttributeOptionName());
    }

    /**
     * Get the entity manager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function createFlexible()
    {
        $class = $this->getFlexibleName();
        $attributeClass = $this->getAttributeName();
        $valueClass = $this->getFlexibleValueName();

        $flexible = new $class();
        $flexible->setLocale($this->getLocale());
        $flexible->setScope($this->getScope());

        $codeToAttributeData = $this->getEntityManager()->getRepository($attributeClass)->getCodeToAttributes($class);
        $flexible->setAllAttributes($codeToAttributeData);
        $flexible->setValueClass($valueClass);

        $event = new FilterFlexibleEvent($this, $flexible);
        $this->eventDispatcher->dispatch(FlexibleEntityEvents::CREATE_FLEXIBLE, $event);

        return $flexible;
    }

    /**
     * Count products linked to a node.
     * You can define if you just want to get the property of the actual node
     * or with its children with the direct parameter
     * The third parameter allow to include the actual node or not
     *
     * @param CategoryInterface $category   the requested category node
     * @param boolean           $inChildren true to include children in count
     * @param boolean           $inProvided true to include the provided none to count product
     *
     * @return integer
     */
    public function getProductsCountInCategory(CategoryInterface $category, $inChildren = false, $inProvided = true)
    {
        $categoryRepository = $this->getEntityManager()->getRepository(get_class($category));

        $categoryQb = null;
        if ($inChildren) {
            $categoryQb = $categoryRepository->getAllChildrenQueryBuilder($category, $inProvided);
        }

        return $this->getFlexibleRepository()->getProductsCountInCategory($category, $categoryQb);
    }

    /**
     * Get product ids linked to a category or its children.
     * You can define if you just want to get the property of the actual node or with its children with the direct
     * parameter
     *
     * @param CategoryInterface $category   the requested node
     * @param boolean           $inChildren true to take children not into account
     *
     * @return array
     */
    public function getProductIdsInCategory(CategoryInterface $category, $inChildren = false)
    {
        $categoryRepository = $this->getEntityManager()->getRepository(get_class($category));

        $categoryQb = null;
        if ($inChildren) {
            $categoryQb = $categoryRepository->getAllChildrenQueryBuilder($category, true);
        }

        return $this->getFlexibleRepository()->getProductIdsInCategory($category, $categoryQb);
    }
}
