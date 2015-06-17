<?php

namespace Pim\Bundle\UIBundle\Entity\Repository;

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
    public function findBySearch($search = '', array $options = [])
    {
        if (method_exists($this, 'getAlias')) {
            $alias = $this->getAlias();
        } else {
            $alias = 'alias';
        }

        $qb = $this->createQueryBuilder($alias)
            ->select("$alias.id, $alias.code");

        if ('' !== $search) {
            $qb->where("$alias.code like :search")
                ->setParameter('search', "%$search%");
        }

        if (isset($options['ids'])) {
            $qb
                ->andWhere(
                    $qb->expr()->in("$alias.id", ':ids')
                )
                ->setParameter('ids', $options['ids']);
        }

        if (isset($options['limit'])) {
            $qb->setMaxResults((int) $options['limit']);
            if (isset($options['page'])) {
                $qb->setFirstResult((int) $options['limit'] * ((int) $options['page'] - 1));
            }
        }

        $results = [];
        $autoSorting = null;
        foreach ($qb->getQuery()->getArrayResult() as $row) {
            if (null === $autoSorting && isset($row['properties']['autoOptionSorting'])) {
                $autoSorting = $row['properties']['autoOptionSorting'];
            }
            $results[] = [
                'id'   => $row['code'],
                'text' => $row['code']
            ];
        }

        if ($autoSorting) {
            usort(
                $results,
                function ($first, $second) {
                    return strcasecmp($first['text'], $second['text']);
                }
            );
        }

        return [
            'results' => $results
        ];
    }
}
