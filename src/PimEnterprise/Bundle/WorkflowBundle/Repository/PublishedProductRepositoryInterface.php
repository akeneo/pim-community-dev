<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Repository;

use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;

/**
 * Published product repository interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface PublishedProductRepositoryInterface extends ProductRepositoryInterface
{
    /**
     * Fetch a published product by the working copy product id
     *
     * @param mixed $originalId
     *
     * @return PublishedProductInterface
     */
    public function findOneByOriginalProductId($originalId);

    /**
     * Fetch many published products by a list of working copy product ids
     *
     * @param array $originalIds
     *
     * @return PublishedProductInterface[]
     */
    public function findByOriginalProductIds(array $originalIds);


    /**
     * Get the ID's of all published products. * The keys of the array are the ID of the original product.
     *
     * @return array [ original product ID => published product ID ]
     */
    public function getProductIdsMapping();
}
