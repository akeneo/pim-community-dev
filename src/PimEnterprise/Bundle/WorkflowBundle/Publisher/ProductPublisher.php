<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Factory\PublishedProductFactory;

/**
 * Publish a product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductPublisher
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var PublishedProductRepositoryInterface*/
    protected $repository;

    /** @var PublishedProductFactory **/
    protected $factory;

    /**
     * @param ObjectManager                       $manager    the object manager
     * @param PublishedProductRepositoryInterface $repository the published repository
     * @param PublishedProductFactory             $factory    the published product factory
     */
    public function __construct(
        ObjectManager $manager,
        PublishedProductRepositoryInterface $repository,
        PublishedProductFactory $factory
    ) {
        $this->objectManager = $manager;
        $this->repository    = $repository;
        $this->factory       = $factory;
    }

    /**
     * Publish a product
     *
     * @param ProductInterface $product
     */
    public function publish($product)
    {
        $published = $this->repository->findOneByOriginalProductId($product->getId());
        if ($published) {
            $this->objectManager->remove($published);
        }

        $published = $this->factory->createPublishedProduct($product);
        $this->objectManager->persist($published);
        $this->objectManager->flush();
    }
}
