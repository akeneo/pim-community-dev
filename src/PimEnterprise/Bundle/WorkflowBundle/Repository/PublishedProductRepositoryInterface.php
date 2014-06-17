<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Repository;

use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;

/**
 * Published product repository interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface PublishedProductRepositoryInterface extends ProductRepositoryInterface
{
    /**
     * @param mixed $originalId
     *
     * @return PublishedProductInterface
     */
    public function findOneByOriginalProductId($originalId);

    /**
     * @param array $originalIds
     *
     * @return PublishedProductInterface[]
     */
    public function findAllByOriginalProductId(array $originalIds);
}
