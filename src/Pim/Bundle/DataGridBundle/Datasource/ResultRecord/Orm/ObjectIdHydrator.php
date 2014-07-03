<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\Orm;

use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;

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
            ->select($rootIdExpr)
            ->resetDQLPart('from')
            ->from($from->getFrom(), $from->getAlias(), $rootIdExpr)
            ->groupBy($rootIdExpr);

        $results = $qb->getQuery()->getArrayResult();

        return array_keys($results);
    }
}
