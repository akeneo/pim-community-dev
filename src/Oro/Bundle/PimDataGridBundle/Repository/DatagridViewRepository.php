<?php

namespace Oro\Bundle\PimDataGridBundle\Repository;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\DBAL\Connection;
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
    public function getDatagridViewAliasesByUser(UserInterface $user): array
    {
        $sql = <<<SQL
SELECT DISTINCT datagrid_alias
FROM pim_datagrid_view
WHERE type = :public_type OR (type = :private_type AND owner_id = :owner_id)
SQL;

        $statement = $this->getConnection()->executeQuery(
            $sql,
            [
                'public_type' => DatagridView::TYPE_PUBLIC,
                'private_type' => DatagridView::TYPE_PRIVATE,
                'owner_id' => $user->getId(),
            ]
        );

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * {@inheritdoc}
     */
    public function findDatagridViewBySearch(
        UserInterface $user,
        string $alias,
        string $term = '',
        array $options = []
    ): array {
        $options += ['limit' => 20, 'page' => 1];
        $offset = (int)$options['limit'] * ((int)$options['page'] - 1);

        $identifiers = null;
        if (isset($options['identifiers'])) {
            $identifiers = $options['identifiers'];
        }

        $qb = $this->createQueryBuilder('v')
                   ->where('v.type = :public_type OR (v.type = :private_type AND v.owner = :owner)')
                   ->setParameter('public_type', DatagridView::TYPE_PUBLIC)
                   ->setParameter('private_type', DatagridView::TYPE_PRIVATE)
                   ->setParameter('owner', $user)
                   ->andWhere('v.datagridAlias = :alias')
                   ->setParameter('alias', $alias)
                   ->andWhere('v.label LIKE :term')
                   ->setParameter('term', sprintf('%%%s%%', $term))
                   ->setMaxResults((int)$options['limit'])
                   ->setFirstResult($offset);

        if (null !== $identifiers) {
            $qb->andWhere('v.id IN (:ids)');
            $qb->setParameter('ids', $identifiers);
        }

        return $qb->getQuery()->execute();
    }

    private function getConnection(): Connection
    {
        return $this->getEntityManager()->getConnection();
    }
}
