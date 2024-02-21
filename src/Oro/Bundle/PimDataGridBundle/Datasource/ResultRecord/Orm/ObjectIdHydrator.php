<?php

namespace Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\Orm;

use Oro\Bundle\PimDataGridBundle\Datasource\ResultRecord\HydratorInterface;

/**
 * Hydrate results of Doctrine ORM query as array of ids
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ObjectIdHydrator implements HydratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function hydrate($qb, array $options = [])
    {
        $rootAlias = current($qb->getRootAliases());
        $rootIdExpr = sprintf('%s.id', $rootAlias);

        $from = current($qb->getDQLPart('from'));

        $qb
            ->resetDQLPart('from')
            ->from($from->getFrom(), $from->getAlias(), $rootIdExpr)
            ->distinct(true);

        $qb = $this->setOrderByFieldsToSelect($qb);
        $qb->addSelect($rootIdExpr);

        $results = $qb->getQuery()->getArrayResult();

        return array_keys($results);
    }

    /**
     * If the given query $qb has some fields in the "ORDER BY" statement,
     * put those fields in the "SELECT" statement too.
     *
     * This way we retrieve object IDs, and the fields we order by.
     *
     * @param mixed $qb
     *
     * @return mixed
     */
    protected function setOrderByFieldsToSelect($qb)
    {
        $originalSelects = $qb->getDQLPart('select');
        $orders = $qb->getDQLPart('orderBy');
        $newSelects = [];

        $qb->resetDQLPart('select');

        foreach ($originalSelects as $select) {
            foreach ($select->getParts() as $part) {
                $alias = stristr($part, ' as ');
                if (false !== $alias) {
                    $newSelects[str_ireplace(' as ', '', $alias)] = $part;
                }
            }
        }

        foreach ($orders as $order) {
            foreach ($order->getParts() as $part) {
                $alias = explode(' ', $part)[0];
                if (isset($newSelects[$alias])) {
                    $qb->addSelect($newSelects[$alias]);
                }
            }
        }

        return $qb;
    }
}
