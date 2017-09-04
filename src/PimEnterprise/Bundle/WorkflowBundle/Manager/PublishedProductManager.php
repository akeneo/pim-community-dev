<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Manager;

use Akeneo\Component\StorageUtils\Detacher\BulkObjectDetacherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use PimEnterprise\Component\Workflow\Event\PublishedProductEvent;
use PimEnterprise\Component\Workflow\Event\PublishedProductEvents;
use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;
use PimEnterprise\Component\Workflow\Publisher\PublisherInterface;
use PimEnterprise\Component\Workflow\Publisher\UnpublisherInterface;
use PimEnterprise\Component\Workflow\Repository\PublishedProductRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Published product manager
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PublishedProductManager
{
    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var PublishedProductRepositoryInterface*/
    protected $repository;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var PublisherInterface */
    protected $publisher;

    /** @var  UnpublisherInterface */
    protected $unpublisher;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var BulkObjectDetacherInterface */
    protected $objectDetacher;

    /**
     * @param ProductRepositoryInterface          $productRepository   the product repository
     * @param PublishedProductRepositoryInterface $repository          the published repository
     * @param AttributeRepositoryInterface        $attributeRepository the attribute repository
     * @param EventDispatcherInterface            $eventDispatcher     the event dispatcher
     * @param PublisherInterface                  $publisher           the product publisher
     * @param UnpublisherInterface                $unpublisher         the product unpublisher
     * @param ObjectManager                       $objectManager       the object manager
     * @param BulkObjectDetacherInterface         $objectDetacher      the object detacher
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        PublishedProductRepositoryInterface $repository,
        AttributeRepositoryInterface $attributeRepository,
        EventDispatcherInterface $eventDispatcher,
        PublisherInterface $publisher,
        UnpublisherInterface $unpublisher,
        ObjectManager $objectManager,
        BulkObjectDetacherInterface $objectDetacher = null
    ) {
        $this->productRepository = $productRepository;
        $this->repository = $repository;
        $this->attributeRepository = $attributeRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->publisher = $publisher;
        $this->unpublisher = $unpublisher;
        $this->objectManager = $objectManager;
        $this->objectDetacher      = $objectDetacher;
    }

    /**
     * Find the published product
     *
     * @param mixed $publishedId
     *
     * @return PublishedProductInterface
     */
    public function findPublishedProductById($publishedId)
    {
        return $this->repository->findOneById($publishedId);
    }

    /**
     * Find the published product by its original product
     *
     * @param mixed $productId
     *
     * @return PublishedProductInterface
     */
    public function findPublishedProductByOriginalId($productId)
    {
        return $this->repository->findOneByOriginalProductId($productId);
    }

    /**
     * Find the published product by its original id
     *
     * @param ProductInterface $product
     *
     * @return PublishedProductInterface
     */
    public function findPublishedProductByOriginal(ProductInterface $product)
    {
        return $this->repository->findOneByOriginalProduct($product);
    }

    /**
     * Find the working copy, the original product
     *
     * @param mixed $productId
     *
     * @return ProductInterface
     */
    public function findOriginalProduct($productId)
    {
        return $this->productRepository->findOneByWithValues($productId);
    }

    /**
     * Find a published product by its identifier
     *
     * @param string $identifier
     *
     * @return PublishedProductInterface
     */
    public function findByIdentifier($identifier)
    {
        return $this->repository->findOneByIdentifier($identifier);
    }

    /**
     * Publish a product
     *
     * @param ProductInterface $product
     * @param array            $publishOptions
     *
     * @return PublishedProductInterface
     */
    public function publish(ProductInterface $product, array $publishOptions = [])
    {
        $this->dispatchEvent(PublishedProductEvents::PRE_PUBLISH, $product);

        $published = $this->findPublishedProductByOriginal($product);
        if ($published) {
            $this->unpublisher->unpublish($published);
            $this->getObjectManager()->remove($published);
            $this->getObjectManager()->flush();
        }

        $published = $this->publisher->publish($product, $publishOptions);
        $this->getObjectManager()->persist($published);

        $publishOptions = array_merge(['flush' => true], $publishOptions);

        if (true === $publishOptions['flush']) {
            $this->getObjectManager()->flush();
            $this->dispatchEvent(PublishedProductEvents::POST_PUBLISH, $product, $published);
        }

        return $published;
    }

    /**
     * Un publish a product
     *
     * @param PublishedProductInterface $published
     * @param array                     $publishOptions
     */
    public function unpublish(PublishedProductInterface $published, array $publishOptions = [])
    {
        $product = $published->getOriginalProduct();
        $this->dispatchEvent(PublishedProductEvents::PRE_UNPUBLISH, $product, $published);
        $this->unpublisher->unpublish($published);
        $this->getObjectManager()->remove($published);

        $publishOptions = array_merge(['flush' => true], $publishOptions);

        if (true === $publishOptions['flush']) {
            $this->getObjectManager()->flush();
            $this->dispatchEvent(PublishedProductEvents::POST_UNPUBLISH, $product);
        }
    }

    /**
     * Bulk publish products
     *
     * @param array $products
     */
    public function publishAll(array $products)
    {
        $publishedContent = [];
        foreach ($products as $product) {
            $published = $this->publish($product, ['with_associations' => false, 'flush' => false]);
            $publishedContent[] = [
                'published' => $published,
                'product'   => $product,
            ];
        }

        $this->getObjectManager()->flush();

        foreach ($publishedContent as $content) {
            $this->dispatchEvent(PublishedProductEvents::POST_PUBLISH, $content['product'], $content['published']);
        }

        $this->publishAssociations($products);
    }

    /**
     * Bulk unpublish products
     *
     * @param array $publishedProducts
     */
    public function unpublishAll(array $publishedProducts)
    {
        foreach ($publishedProducts as $published) {
            $this->unpublish($published, ['flush' => false]);
        }

        $this->getObjectManager()->flush();
    }

    /**
     * Publish all associations where products appears in owner or owned side
     *
     * For instance,
     *  A1- P1 -> cross-sell -> P2
     *  A2- P3 -> cross-sell -> P4
     *
     * If P1 is passed in $products, association A1 is refreshed
     * If P4 is passed in $products, association A2 is refreshed
     *
     * @param ProductInterface[] $products
     */
    protected function publishAssociations(array $products)
    {
        $publishedProducts = [];
        foreach ($products as $product) {
            $published = $this->findPublishedProductByOriginal($product);
            foreach ($product->getAssociations() as $association) {
                $copiedAssociation = $this->publisher->publish($association, ['published' => $published]);
                $published->addAssociation($copiedAssociation);
                $this->getObjectManager()->persist($published);
                $publishedProducts[] = $published;
            }
        }

        $this->getObjectManager()->flush();

        if (null !== $this->objectDetacher) {
            $this->objectDetacher->detachAll($publishedProducts);
        }
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * Dispatch a published product event
     *
     * @param string                    $name
     * @param ProductInterface          $product
     * @param PublishedProductInterface $published
     */
    protected function dispatchEvent($name, ProductInterface $product, PublishedProductInterface $published = null)
    {
        $this->eventDispatcher->dispatch($name, new PublishedProductEvent($product, $published));
    }
}
