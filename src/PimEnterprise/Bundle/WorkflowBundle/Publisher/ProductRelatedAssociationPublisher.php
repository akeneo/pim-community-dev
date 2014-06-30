<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Repository\AssociationRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedAssociationRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;

/**
 * Publisher for product related associations
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductRelatedAssociationPublisher implements PublisherInterface
{
    /** @var PublishedProductRepositoryInterface */
    protected $publishedRepository;

    /** @var PublishedAssociationRepositoryInterface */
    protected $publishedAssociationRepository;

    /** @var AssociationRepositoryInterface */
    protected $associationRepository;

    /** @var  ObjectManager */
    protected $objectManager;

    public function __construct(
        PublishedProductRepositoryInterface $publishedRepository,
        PublishedAssociationRepositoryInterface $publishedAssociationRepository,
        AssociationRepositoryInterface $associationRepository,
        ObjectManager $objectManager
    ) {
        $this->publishedRepository = $publishedRepository;
        $this->publishedAssociationRepository = $publishedAssociationRepository;
        $this->associationRepository = $associationRepository;
        $this->objectManager = $objectManager;
    }


    /**
     * {@inheritdoc}
     */
    public function publish($object, array $options = [])
    {
        /** @var PublishedProductInterface $publishedProduct */
        $publishedProduct = $object;

        $productIds = $this->publishedRepository->getProductIdsMapping();
        unset($productIds[$publishedProduct->getOriginalProductId()]);

        $associations = $this->associationRepository->findByProductIdAndOwnerIds(
            $publishedProduct->getOriginalProductId(),
            array_keys($productIds)
        );

        foreach ($associations as $association) {
            $publishedAssociation = $this->publishedAssociationRepository->findOneByTypeAndOwner(
                $association->getAssociationType(),
                $productIds[$association->getOwner()->getId()]
            );

            if (null !== $publishedAssociation) {
                $publishedAssociation->addProduct($publishedProduct);
//                $this->objectManager->persist($publishedAssociation);
            }
        }

//        $this->objectManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof PublishedProductInterface;
    }
}
