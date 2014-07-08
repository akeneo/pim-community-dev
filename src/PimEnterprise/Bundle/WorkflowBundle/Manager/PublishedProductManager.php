<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use PimEnterprise\Bundle\WorkflowBundle\Event\PublishedProductEvent;
use PimEnterprise\Bundle\WorkflowBundle\Event\PublishedProductEvents;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Factory\PublishedProductFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Published product manager
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PublishedProductManager
{
    /** @var ProductManager */
    protected $productManager;

    /** @var PublishedProductRepositoryInterface*/
    protected $repository;

    /** @var PublishedProductFactory **/
    protected $factory;

    /** @var  EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param ProductManager                      $manager         the product manager
     * @param PublishedProductRepositoryInterface $repository      the published repository
     * @param PublishedProductFactory             $factory         the published product factory
     * @param EventDispatcherInterface            $eventDispatcher the event dispatcher
     */
    public function __construct(
        ProductManager $manager,
        PublishedProductRepositoryInterface $repository,
        PublishedProductFactory $factory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->productManager  = $manager;
        $this->repository      = $repository;
        $this->factory         = $factory;
        $this->eventDispatcher = $eventDispatcher;
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
        $published = $this->repository->findOneBy(
            [
                [
                    'attribute' => $this->productManager->getIdentifierAttribute(),
                    'value' => $identifier
                ]
            ]
        );

        return $published;
    }

    /**
     * Publish a product
     *
     * @param ProductInterface $product
     *
     * @return PublishedProductInterface
     */
    public function publish(ProductInterface $product)
    {
        $this->dispatchEvent(PublishedProductEvents::PRE_PUBLISH, $product);

        $published = $this->findPublishedProductByOriginal($product);
        if ($published) {
            $this->getObjectManager()->remove($published);
            $this->getObjectManager()->flush();
        }

        $published = $this->factory->createPublishedProduct($product);
        $this->getObjectManager()->persist($published);
        $this->getObjectManager()->flush();

        $this->dispatchEvent(PublishedProductEvents::POST_PUBLISH, $product, $published);

        return $published;
    }

    /**
     * Un publish a product
     *
     * @param PublishedProductInterface $published
     *
     * @return bool
     */
    public function unpublish(PublishedProductInterface $published)
    {
        return true;
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
