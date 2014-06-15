<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Factory\PublishedProductFactory;

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

    /**
     * @param ProductManager                      $manager    the product manager
     * @param PublishedProductRepositoryInterface $repository the published repository
     * @param PublishedProductFactory             $factory    the published product factory
     */
    public function __construct(
        ProductManager $manager,
        PublishedProductRepositoryInterface $repository,
        PublishedProductFactory $factory
    ) {
        $this->productManager = $manager;
        $this->repository     = $repository;
        $this->factory        = $factory;
    }

    /**
     * Find the published product
     *
     * @param mixed $publishedId
     *
     * @return PublishedProduct
     */
    public function findPublishedProductById($publishedId)
    {
        return $this->repository->findOneById($id);
    }

    /**
     * Find the published product by its original id
     *
     * @param mixed $productId
     *
     * @return PublishedProduct
     */
    public function findPublishedProductByOriginalId($productId)
    {
        return $this->repository->findOneByOriginalProductId($productId);
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
     * Publish a product
     *
     * @param ProductInterface $product
     */
    public function publish(ProductInterface $product)
    {
        $published = $this->findPublishedProductByOriginalId($product->getId());
        if ($published) {
            $this->getObjectManager()->remove($published);
        }

        $published = $this->factory->createPublishedProduct($product);
        $this->getObjectManager()->persist($published);
        $this->getObjectManager()->flush();
    }

    /**
     * @return ObjectManager
     */
    protected function getObjectManager()
    {
        return $this->productManager->getObjectManager();
    }
}
