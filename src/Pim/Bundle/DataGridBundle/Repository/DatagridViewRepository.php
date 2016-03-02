<?php

namespace Pim\Bundle\DataGridBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\UserBundle\Entity\UserInterface;

/**
 * Datagrid view repository
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridViewRepository extends EntityRepository implements DatagridViewRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDatagridViewTypeByUser(UserInterface $user)
    {
        return $this->createQueryBuilder('v')
            ->select('v.datagridAlias')
            ->groupBy('v.datagridAlias')
            ->where('v.owner = :user_id')
            ->setParameter('user_id', $user->getId())
            ->getQuery()
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findDatagridViewByUserAndAlias(UserInterface $user, $alias)
    {
        return $this->createQueryBuilder('v')
            ->where('v.owner = :user_id')
            ->andWhere('v.datagridAlias = :alias')
            ->setParameters([
                'user_id' => $user->getId(),
                'alias'   => $alias
            ]);
    }
}
