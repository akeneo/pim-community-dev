<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\PimDataGridBundle\Doctrine\ORM\Repository\MassActionRepositoryInterface;

/**
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class RuleDefinitionMassEditRepository implements MassActionRepositoryInterface
{
    /** @var string */
    protected $entityName;

    /** @var EntityManager */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     * @param string        $entityName
     */
    public function __construct(EntityManager $entityManager, $entityName)
    {
        $this->entityManager = $entityManager;
        $this->entityName = $entityName;
    }

    /**
     * {@inheritdoc}
     *
     * @param QueryBuilder $qb
     */
    public function applyMassActionParameters($qb, $inset, array $values)
    {
        if (!empty($values)) {
            $rootAlias = $qb->getRootAliases()[0];
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

        return $this->entityManager->createQueryBuilder()
            ->delete($this->entityName, 'r')
            ->where('r.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
}
