<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\Product;

use Akeneo\Pim\Structure\Component\Repository\AssociationTypeRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\UnpublisherInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedAssociationRepositoryInterface;

/**
 * Product unpublisher
 *
 * @author Julien Janvier <nicolas@akeneo.com>
 */
class ProductUnpublisher implements UnpublisherInterface
{
    /** @var PublishedAssociationRepositoryInterface */
    protected $publishedAssocRepo;

    /** @var AssociationTypeRepositoryInterface */
    protected $associationTypeRepo;

    /**
     * The constructor
     *
     * @param PublishedAssociationRepositoryInterface $publishedAssocRepo
     * @param AssociationTypeRepositoryInterface      $associationTypeRepo
     */
    public function __construct(
        PublishedAssociationRepositoryInterface $publishedAssocRepo,
        AssociationTypeRepositoryInterface $associationTypeRepo
    ) {
        $this->publishedAssocRepo = $publishedAssocRepo;
        $this->associationTypeRepo = $associationTypeRepo;
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
        $nbAssociationTypes = $this->associationTypeRepo->countAll();
        $this->publishedAssocRepo->removePublishedProduct($published, $nbAssociationTypes);
    }
}
