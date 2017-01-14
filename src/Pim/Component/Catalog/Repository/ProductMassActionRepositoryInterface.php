<?php

namespace Pim\Component\Catalog\Repository;

use Pim\Bundle\DataGridBundle\Doctrine\ORM\Repository\MassActionRepositoryInterface;

/**
 * Product mass action repository interface
 * Methods have been extracted from ProductRepository to be specialized for mass actions
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductMassActionRepositoryInterface extends MassActionRepositoryInterface
{
    /**
     * Find all common attribute ids linked to a family or with values from a list of product ids
     *
     * @param array $productIds
     *
     * @return integer[]
     */
    public function findCommonAttributeIds(array $productIds);

    /**
     * Delete a list of product ids
     *
     * @param integer[] $ids
     *
     * @throws \LogicException
     */
    public function deleteFromIds(array $ids);
}
