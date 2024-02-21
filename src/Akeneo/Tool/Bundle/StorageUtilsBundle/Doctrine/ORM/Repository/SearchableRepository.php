<?php

namespace Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository;

use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Searchable repository
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchableRepository extends EntityRepository implements SearchableRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findBySearch($search = null, array $options = [])
    {
        if (method_exists($this, 'getAlias')) {
            $alias = $this->getAlias();
        } else {
            $alias = 'alias';
        }

        $qb = $this->createQueryBuilder($alias);
        $qb->select("$alias.id, $alias.code");

        if (null !== $search) {
            $qb->where("$alias.code like :search")->setParameter('search', "%$search%");
        }

        if (isset($options['limit'])) {
            $qb->setMaxResults((int) $options['limit']);
            if (isset($options['page'])) {
                $qb->setFirstResult((int) $options['limit'] * ((int) $options['page'] - 1));
            }
        }

        $results = [];
        foreach ($qb->getQuery()->getArrayResult() as $row) {
            $results[] = [
                'id'   => $row['code'],
                'text' => $row['code']
            ];
        }

        return $results;
    }
}
