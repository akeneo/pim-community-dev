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

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\PublisherInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\FindProductAssociationToPublishByProductQueryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedAssociationRepositoryInterface;

/**
 * Publisher for product related associations.
 * When a product A is published, this class will update all the associations where A is referred.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RelatedAssociationPublisher implements PublisherInterface
{
    protected PublishedAssociationRepositoryInterface $publishedAssocRepo;
    private FindProductAssociationToPublishByProductQueryInterface $findProductAssociationToPublishByProductQuery;

    public function __construct(
        PublishedAssociationRepositoryInterface                $publishedAssocRepo,
        FindProductAssociationToPublishByProductQueryInterface $findProductAssociationToPublishByProductQuery
    ) {
        $this->publishedAssocRepo = $publishedAssocRepo;
        $this->findProductAssociationToPublishByProductQuery = $findProductAssociationToPublishByProductQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($object, array $options = [])
    {
        foreach ($this->findProductAssociationToPublishByProductQuery
                     ->execute($object->getOriginalProduct())
                 as $association) {
            $publishedAssociation = $this->publishedAssocRepo->findOneByTypeAndOwner(
                $association[FindProductAssociationToPublishByProductQueryInterface::ASSOCIATION_TYPE_ID],
                $association[FindProductAssociationToPublishByProductQueryInterface::PRODUCT_ID]
            );

            if (null !== $publishedAssociation) {
                $publishedAssociation->addProduct($object);
            }
        }
        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof PublishedProductInterface;
    }
}
