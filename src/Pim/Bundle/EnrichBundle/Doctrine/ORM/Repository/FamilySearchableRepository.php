<?php

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Pim\Component\Catalog\Model\FamilyInterface;

/**
 * Family searchable repository
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilySearchableRepository implements SearchableRepositoryInterface
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var string */
    protected $entityName;

    /**
     * @param EntityManagerInterface $entityManager
     * @param string                 $entityName
     */
    public function __construct(EntityManagerInterface $entityManager, $entityName)
    {
        $this->entityManager = $entityManager;
        $this->entityName = $entityName;
    }

    /**
     * {@inheritdoc}
     *
     * @return FamilyInterface[]
     */
    public function findBySearch($search = null, array $options = [])
    {
        $qb = $this->applySearchParameters($search, $options);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $options
     *
     * @return FamilyInterface[]
     */
    public function findFamiliesWithVariants(array $options = [])
    {
        $qb = $this->applySearchParameters(null, $options);
        $qb->where('f.familyVariants IS NOT EMPTY');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $search
     * @param array $options
     *
     * @return QueryBuilder
     */
    protected function applySearchParameters($search = null, array $options = []) {
        $qb = $this->entityManager->createQueryBuilder()->select('f')->from($this->entityName, 'f');

        if (null !== $search && '' !== $search) {
            $qb->where('f.code like :search')->setParameter('search', '%' . $search . '%');
            if (isset($options['locale'])) {
                $qb->leftJoin('f.translations', 'ft');
                $qb->orWhere('ft.label like :search AND ft.locale = :locale');
                $qb->setParameter('search', '%' . $search . '%');
                $qb->setParameter('locale', $options['locale']);
            }
        }

        $qb = $this->applyQueryOptions($qb, $options);

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param array        $options
     *
     * @return QueryBuilder
     */
    protected function applyQueryOptions(QueryBuilder $qb, array $options)
    {
        if (isset($options['identifiers']) && is_array($options['identifiers']) && !empty($options['identifiers'])) {
            $qb->andWhere('f.code in (:codes)');
            $qb->setParameter('codes', $options['identifiers']);
        }

        if (isset($options['limit'])) {
            $qb->setMaxResults((int) $options['limit']);
            if (isset($options['page'])) {
                $qb->setFirstResult((int) $options['limit'] * ((int) $options['page'] - 1));
            }
        }

        return $qb;
    }
}
