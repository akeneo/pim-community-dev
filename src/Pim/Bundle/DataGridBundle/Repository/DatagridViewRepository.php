<?php

namespace Pim\Bundle\DataGridBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
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

    /**
     * {@inheritdoc}
     */
    public function findDatagridViewBySearch(UserInterface $user, $alias, $term = '', array $options = [])
    {
        $options += ['limit' => 20, 'page' => 1];
        $offset = (int) $options['limit'] * ((int) $options['page'] - 1);

        $qb = $this->createQueryBuilder('v')
            ->where('v.type = :type')
                ->setParameter('type', DatagridView::TYPE_PUBLIC)
            ->andWhere('v.datagridAlias = :alias')
                ->setParameter('alias', $alias)
            ->andWhere('v.label LIKE :term')
                ->setParameter('term', sprintf('%%%s%%', $term))
            ->setMaxResults((int) $options['limit'])
            ->setFirstResult($offset);

        return $qb->getQuery()->execute();
    }
}
