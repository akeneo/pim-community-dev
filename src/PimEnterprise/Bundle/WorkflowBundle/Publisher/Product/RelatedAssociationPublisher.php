<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher\Product;

use Pim\Bundle\CatalogBundle\Repository\AssociationRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedAssociationRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;

/**
 * Publisher for product related associations.
 * When a product A is published, this class will update all the associations where A is referred.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RelatedAssociationPublisher implements PublisherInterface
{
    /** @var PublishedProductRepositoryInterface */
    protected $publishedRepository;

    /** @var PublishedAssociationRepositoryInterface */
    protected $publishedAssocRepo;

    /** @var AssociationRepositoryInterface */
    protected $associationRepo;

    /**
     * The constructor
     *
     * @param PublishedProductRepositoryInterface     $publishedRepository
     * @param PublishedAssociationRepositoryInterface $publishedAssocRepo
     * @param AssociationRepositoryInterface          $associationRepo
     */
    public function __construct(
        PublishedProductRepositoryInterface $publishedRepository,
        PublishedAssociationRepositoryInterface $publishedAssocRepo,
        AssociationRepositoryInterface $associationRepo
    ) {
        $this->publishedRepository = $publishedRepository;
        $this->publishedAssocRepo = $publishedAssocRepo;
        $this->associationRepo = $associationRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($object, array $options = [])
    {
        $productIds = $this->publishedRepository->getProductIdsMapping();
        unset($productIds[$object->getOriginalProduct()->getId()]);

        if (0 !== count($productIds)) {
            $associations = $this->associationRepo->findByProductAndOwnerIds(
                $object->getOriginalProduct(),
                array_keys($productIds)
            );

            foreach ($associations as $association) {
                $publishedAssociation = $this->publishedAssocRepo->findOneByTypeAndOwner(
                    $association->getAssociationType(),
                    $productIds[$association->getOwner()->getId()]
                );

                if (null !== $publishedAssociation) {
                    $publishedAssociation->addProduct($object);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof PublishedProductInterface;
    }
}
