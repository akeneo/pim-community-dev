<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\MongoDBODM;

use Pim\Bundle\CatalogBundle\Entity\AssociationType;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductRepository;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedAssociationRepositoryInterface;

/**
 * Published products repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PublishedProductRepository extends ProductRepository
    implements PublishedProductRepositoryInterface, PublishedAssociationRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findOneByOriginalProductId($originalId)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->field('originalProductId')->equals($originalId);
        $result = $qb->getQuery()->execute();
        $product = $result->getNext();

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function findByOriginalProductIds(array $originalIds)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->field('originalProductId')->in($originalIds);
        $products = $qb->getQuery()->execute();

        return $products->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getProductIdsMapping()
    {
        $qb = $this->createQueryBuilder();
        $qb->select('originalProductId', '_id');
        $qb->hydrate(false);

        $ids = [];
        foreach ($qb->getQuery()->execute() as $row) {
            $ids[$row['originalProductId']] = $row['_id']->{'$id'};
        }

        return $ids;
    }

    /**
     * {@inheritdoc}
     *
     * TODO; find a way to do it efficiently
     */
    public function findOneByTypeAndOwner(AssociationType $type, $ownerId)
    {
        // retrieve the product that owns our published association
        $product = $this->find($ownerId);

        // find the right association
        foreach ($product->getAssociations() as $association) {
            if ($association->getAssociationType() === $type) {
                return $association;
            }
        }

        return null;
    }
}
