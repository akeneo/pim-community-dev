<?php

namespace Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository;

/**
 * Mass action repository interface
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface MassActionRepositoryInterface
{
    /**
     * @param mixed $qb
     * @param bool  $inset
     * @param array $values
     *
     * @return mixed
     */
    public function applyMassActionParameters($qb, $inset, array $values);
}
