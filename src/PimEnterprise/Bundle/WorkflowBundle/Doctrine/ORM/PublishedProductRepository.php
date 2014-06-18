<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Doctrine\ORM;

use Pim\Bundle\CatalogBundle\Doctrine\ORM\ProductRepository;
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
     * Expected by interface but we let ORM entity repository work with its magic here
     */
    public function findOneByOriginalProductId($originalId)
    {
        return parent::findOneByOriginalProductId($originalId);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllByOriginalProductId(array $originalIds)
    {
        return parent::findBy(['originalProductId' => $originalIds]);
    }
}
