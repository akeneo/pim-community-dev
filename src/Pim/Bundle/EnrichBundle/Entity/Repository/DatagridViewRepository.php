<?php

namespace Pim\Bundle\EnrichBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\EnrichBundle\Entity\DatagridView;

/**
 * Datagrid view repository
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridViewRepository extends EntityRepository
{
    /**
     * @param string $alias
     * @param User   $user
     *
     * @return DatagridView[]
     */
    public function findAllForUser($alias, User $user)
    {
        $qb = $this->createQueryBuilder('d');
        $rootAlias = $qb->getRootAlias();

        $qb
            ->where(
                $qb->expr()->eq(sprintf('%s.datagridAlias', $rootAlias), ':alias')
            )
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq(sprintf('%s.owner', $rootAlias), ':user'),
                    $qb->expr()->eq(sprintf('%s.type', $rootAlias), ':type')
                )
            )
            ->orderBy(sprintf('%s.label', $rootAlias))
            ->setParameters(['alias' => $alias, 'user' => $user, 'type' => DatagridView::TYPE_PUBLIC]);

        return $qb->getQuery()->getResult();
    }
}
