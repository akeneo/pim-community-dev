<?php

namespace Pim\Bundle\ClassificationBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Component\Classification\Repository\TagRepositoryInterface;

/**
 * Tag repository
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TagRepository extends EntityRepository implements TagRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->findOneBy(['code' => $identifier]);
    }

    /**
     * Get all tags id and code
     *
     * @return string[]
     */
    public function findAllCodes()
    {
        $queryBuilder = $this->createQueryBuilder('t');
        $queryBuilder->select('t.id, t.code');
        $queryBuilder->orderBy('t.code');

        $codes = [];

        foreach ($queryBuilder->getQuery()->getArrayResult() as $result) {
            $codes[$result['id']] = $result['code'];
        }

        return $codes;
    }

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

    /**
     * @return string
     */
    protected function getAlias()
    {
        return 'tag';
    }
}
