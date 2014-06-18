<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\MongoDBODM;

use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductRepository;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;

/**
 * Published products repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PublishedProductRepository extends ProductRepository implements PublishedProductRepositoryInterface
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
}
