<?php

namespace Oro\Bundle\PimDataGridBundle\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;

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
    public function getDatagridViewTypeByUser(UserInterface $user): ArrayCollection
    {
        return $this->createQueryBuilder('v')
            ->select('v.datagridAlias')
            ->distinct(true)
            ->getQuery()
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function findDatagridViewByAlias(string $alias): ArrayCollection
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.type = :type')
            ->andWhere('v.datagridAlias = :alias')
            ->setParameters([
                'type'  => DatagridView::TYPE_PUBLIC,
                'alias' => $alias
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function findDatagridViewBySearch(UserInterface $user, string $alias, string $term = '', array $options = []): ArrayCollection
    {
        $options += ['limit' => 20, 'page' => 1];
        $offset = (int) $options['limit'] * ((int) $options['page'] - 1);

        $identifiers = null;
        if (isset($options['identifiers'])) {
            $identifiers = $options['identifiers'];
        }

        $qb = $this->createQueryBuilder('v')
            ->where('v.type = :type')
                ->setParameter('type', DatagridView::TYPE_PUBLIC)
            ->andWhere('v.datagridAlias = :alias')
                ->setParameter('alias', $alias)
            ->andWhere('v.label LIKE :term')
                ->setParameter('term', sprintf('%%%s%%', $term))
            ->setMaxResults((int) $options['limit'])
            ->setFirstResult($offset);

        if (null !== $identifiers) {
            $qb->andWhere('v.id IN (:ids)');
            $qb->setParameter('ids', $identifiers);
        }

        return $qb->getQuery()->execute();
    }
}
