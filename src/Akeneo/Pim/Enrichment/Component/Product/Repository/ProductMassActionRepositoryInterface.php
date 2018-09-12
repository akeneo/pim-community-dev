<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Repository;

use Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\MassActionRepositoryInterface;

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
     * Delete a list of product ids
     *
     * @param integer[] $ids
     *
     * @throws \LogicException
     */
    public function deleteFromIds(array $ids);
}
