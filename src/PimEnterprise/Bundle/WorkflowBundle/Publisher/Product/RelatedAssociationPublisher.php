<?php

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
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class RelatedAssociationPublisher implements PublisherInterface
{
    /** @var PublishedProductRepositoryInterface */
    protected $publishedRepository;

    /** @var PublishedAssociationRepositoryInterface */
    protected $publishedAssociationRepository;

    /** @var AssociationRepositoryInterface */
    protected $associationRepository;

    /**
     * The constructor
     *
     * @param PublishedProductRepositoryInterface     $publishedRepository
     * @param PublishedAssociationRepositoryInterface $publishedAssociationRepository
     * @param AssociationRepositoryInterface          $associationRepository
     */
    public function __construct(
        PublishedProductRepositoryInterface $publishedRepository,
        PublishedAssociationRepositoryInterface $publishedAssociationRepository,
        AssociationRepositoryInterface $associationRepository
    ) {
        $this->publishedRepository = $publishedRepository;
        $this->publishedAssociationRepository = $publishedAssociationRepository;
        $this->associationRepository = $associationRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($object, array $options = [])
    {
        $productIds = $this->publishedRepository->getProductIdsMapping();
        unset($productIds[$object->getOriginalProduct()->getId()]);

        if (0 !== count($productIds)) {
            $associations = $this->associationRepository->findByProductAndOwnerIds(
                $object->getOriginalProduct(),
                array_keys($productIds)
            );

            foreach ($associations as $association) {
                $publishedAssociation = $this->publishedAssociationRepository->findOneByTypeAndOwner(
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
