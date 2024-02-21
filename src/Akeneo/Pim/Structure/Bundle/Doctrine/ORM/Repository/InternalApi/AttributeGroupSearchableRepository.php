<?php

namespace Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi;

use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Attribute group searchable repository
 *
 * @author Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AttributeGroupSearchableRepository implements SearchableRepositoryInterface
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
     */
    public function findBySearch($search = null, array $options = [])
    {
        $qb = $this->findBySearchQb($search, $options);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param  array $options
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
            ]
        );
        $resolver->setAllowedTypes('identifiers', 'array');
        $resolver->setAllowedTypes('excluded_identifiers', 'array');
        $resolver->setAllowedTypes('limit', ['int', 'string', 'null']);
        $resolver->setAllowedTypes('page', ['int', 'string', 'null']);
        $resolver->setAllowedTypes('locale', ['string', 'null']);

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
     * @param  string $search
     * @param  array  $options
     *
     * @return QueryBuilder
     */
    protected function findBySearchQb($search, array $options)
    {
        $qb = $this->entityManager
            ->createQueryBuilder()
            ->select('ag')
            ->from($this->entityName, 'ag');

        $options = $this->resolveOptions($options);

        if (null !== $search && strlen($search)) {
            $qb->leftJoin('ag.translations', 'agt');
            $qb->where('ag.code like :search')->setParameter('search', "%{$search}%");
            if (null !== $localeCode = $options['locale']) {
                $qb->orWhere('agt.label like :search AND agt.locale like :locale');
                $qb->setParameter('search', "%{$search}%");
                $qb->setParameter('locale', $localeCode);
            }
        }

        if (!empty($options['identifiers'])) {
            $qb->andWhere('ag.code in (:codes)');
            $qb->setParameter('codes', $options['identifiers']);
        }

        if (!empty($options['excluded_identifiers'])) {
            $qb->andWhere('ag.code not in (:codes)');
            $qb->setParameter('codes', $options['excluded_identifiers']);
        }

        if (null !== $options['limit']) {
            $qb->setMaxResults($options['limit']);
            if (null !== $options['page']) {
                $qb->setFirstResult($options['limit'] * ($options['page'] - 1));
            }
        }

        $qb->orderBy('ag.sortOrder');

        return $qb;
    }
}
