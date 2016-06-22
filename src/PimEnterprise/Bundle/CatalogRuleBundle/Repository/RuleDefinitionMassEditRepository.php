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

use Doctrine\ORM\EntityManager;
use Pim\Component\Catalog\Repository\MassActionRepositoryInterface;

/**
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class RuleDefinitionMassEditRepository implements MassActionRepositoryInterface
{
    /** @var string */
    protected $entityName;

    /** @var EntityManager */
    protected $em;

    /**
     * @param EntityManager $em
     * @param string        $entityName
     */
    public function __construct(EntityManager $em, $entityName)
    {
        $this->em         = $em;
        $this->entityName = $entityName;
    }

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

    /**
     * Delete a list of rules
     *
     * @param mixed[] $ids
     *
     * @return int number of impacted rows
     */
    public function deleteFromIds(array $ids)
    {
        if (empty($ids)) {
            return 0;
        }

        return $this->em->createQueryBuilder()
            ->delete($this->entityName, 'r')
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
}
