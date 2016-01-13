<?php

namespace Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Searchable repository
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SearchableRepository implements SearchableRepositoryInterface
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
        $this->entityName    = $entityName;
    }

    /**
     * {@inheritdoc}
     */
    public function findBySearch($search = null, array $options = [])
    {
        $qb = $this->buildQb($search, $options);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function resolveOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(
            [
                'identifiers'          => [],
                'excluded_identifiers' => [],
                'limit'                => null,
                'page'                 => null,
                'locale'               => null,
                'user_groups_ids'      => null,
                'types'                => null,
            ]
        );
        $resolver->setAllowedTypes('identifiers', 'array');
        $resolver->setAllowedTypes('excluded_identifiers', 'array');
        $resolver->setAllowedTypes('limit', ['int', 'string', 'null']);
        $resolver->setAllowedTypes('page', ['int', 'string', 'null']);
        $resolver->setAllowedTypes('locale', ['string', 'null']);
        $resolver->setAllowedTypes('user_groups_ids', ['string', 'null']);
        $resolver->setAllowedTypes('types', ['array', 'null']);

        $options = $resolver->resolve($options);

        if (null !== $options['page']) {
            $options['page'] = (int) $options['page'];
        }
        if (null !== $options['limit']) {
            $options['limit'] = (int) $options['limit'];
        }

        return $options;
    }

    /**
     * @param string $search
     * @param array  $options
     *
     * @return QueryBuilder
     */
    protected function buildQb($search, array $options)
    {
        $qb = $this->entityManager->createQueryBuilder()->select('entity')->from($this->entityName, 'entity');
        $options = $this->resolveOptions($options);

        if (null !== $search) {
            $qb->leftJoin('entity.translations', 'et');
            $qb->where('entity.code like :search')->setParameter('search', "%$search%");
            if (null !== $localeCode = $options['locale']) {
                $qb->orWhere('et.label like :search AND et.locale like :locale');
                $qb->setParameter('search', "%$search%");
                $qb->setParameter('locale', "$localeCode");
            }
        }

        if (!empty($options['identifiers'])) {
            $qb->andWhere('entity.code in (:codes)');
            $qb->setParameter('codes', $options['identifiers']);
        }

        if (!empty($options['excluded_identifiers'])) {
            $qb->andWhere('entity.code not in (:codes)');
            $qb->setParameter('codes', $options['excluded_identifiers']);
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
