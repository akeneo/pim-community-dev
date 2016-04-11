<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Repository;

use Pim\Component\Catalog\Repository\MassActionRepositoryInterface;

/**
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class RuleDefinitionMassEditRepository implements MassActionRepositoryInterface
{
    /**
     * @param mixed $qb
     * @param bool  $inset
     * @param array $values
     */
    public function applyMassActionParameters($qb, $inset, array $values)
    {
        if (!empty($values)) {
            $rootAlias = $qb->getRootAlias();
            $valueWhereCondition =
                $inset
                    ? $qb->expr()->in($rootAlias, $values)
                    : $qb->expr()->notIn($rootAlias, $values);
            $qb->andWhere($valueWhereCondition);
        }

        $qb
            ->resetDQLPart('orderBy')
            ->setMaxResults(null);
    }
}
