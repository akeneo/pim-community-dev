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

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Event\PublishedProductEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\PublishedProductEvents;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface;
use PimEnterprise\Bundle\WorkflowBundle\Publisher\UnpublisherInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Published product manager
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PublishedProductManager
{
    /** @var ProductManager */
    protected $productManager;

    /** @var PublishedProductRepositoryInterface*/
    protected $repository;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var PublisherInterface */
    protected $publisher;

    /** @var  UnpublisherInterface */
    protected $unpublisher;

    /**
     * @param ProductManager                      $manager         the product manager
     * @param PublishedProductRepositoryInterface $repository      the published repository
     * @param EventDispatcherInterface            $eventDispatcher the event dispatcher
     * @param PublisherInterface                  $publisher       the product publisher
     * @param UnpublisherInterface                $unpublisher     the product unpublisher
     */
    public function __construct(
        ProductManager $manager,
        PublishedProductRepositoryInterface $repository,
        EventDispatcherInterface $eventDispatcher,
        PublisherInterface $publisher,
        UnpublisherInterface $unpublisher
    ) {
        $this->productManager  = $manager;
        $this->repository      = $repository;
        $this->eventDispatcher = $eventDispatcher;
        $this->publisher       = $publisher;
        $this->unpublisher     = $unpublisher;
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
     * @param ProductInterface $product
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
        return $this->productManager->find($productId);
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
        foreach ($products as $product) {
            $this->publish($product, ['with_associations' => false, 'flush' => false]);
        }

        $this->getObjectManager()->flush();

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
        foreach ($products as $product) {
            $published = $this->findPublishedProductByOriginal($product);
            foreach ($product->getAssociations() as $association) {
                $copiedAssociation = $this->publisher->publish($association, ['published' => $published]);
                $published->addAssociation($copiedAssociation);
            }
        }
        $this->getObjectManager()->flush();
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->productManager->getObjectManager();
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
