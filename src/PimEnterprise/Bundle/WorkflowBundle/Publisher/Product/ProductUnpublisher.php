<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher\Product;

use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Publisher\UnpublisherInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedAssociationRepositoryInterface;

/**
 * Product unpublisher
 *
 * @author    Julien Janvier <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductUnpublisher implements UnpublisherInterface
{
    /** @var PublishedAssociationRepositoryInterface */
    protected $publishedAssociationRepository;

    /**
     * The constructor
     *
     * @param PublishedAssociationRepositoryInterface $publishedAssociationRepository
     */
    public function __construct(PublishedAssociationRepositoryInterface $publishedAssociationRepository) {
        $this->publishedAssociationRepository = $publishedAssociationRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function unpublish($object, array $options = [])
    {
        $this->updateRelatedAssociations($object);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof PublishedProductInterface;
    }

    /**
     * Update associations where the published product was referenced.
     *
     * @param PublishedProductInterface $published
     */
    protected function updateRelatedAssociations(PublishedProductInterface $published)
    {
        $this->publishedAssociationRepository->removePublishedProduct($published);
    }
}
