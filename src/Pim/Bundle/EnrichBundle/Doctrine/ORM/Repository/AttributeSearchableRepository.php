<?php

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Attribute searchable repository
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Soatware License (OSL 3.0)
 */
class AttributeSearchableRepository implements SearchableRepositoryInterface
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
     * @return AttributeInterface[]
     */
    public function findBySearch($search = null, array $options = [])
    {
        $qb = $this->findBySearchQb($search, $options);

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
                'exclude_unique'       => false,
                'user_groups_ids'      => null,
                'types'                => null,
                'attribute_groups'     => [],
                'rights'               => true
            ]
        );
        $resolver->setAllowedTypes('identifiers', 'array');
        $resolver->setAllowedTypes('excluded_identifiers', 'array');
        $resolver->setAllowedTypes('limit', ['int', 'string', 'null']);
        $resolver->setAllowedTypes('page', ['int', 'string', 'null']);
        $resolver->setAllowedTypes('locale', ['string', 'null']);
        $resolver->setAllowedTypes('exclude_unique', ['string', 'bool']);
        $resolver->setAllowedTypes('user_groups_ids', ['array', 'null']);
        $resolver->setAllowedTypes('types', ['array', 'null']);
        $resolver->setAllowedTypes('attribute_groups', ['array']);
        $resolver->setAllowedTypes('rights', ['bool']);

        $options = $resolver->resolve($options);

        if (null !== $options['page']) {
            $options['page'] = (int) $options['page'];
        }
        if (null !== $options['limit']) {
            $options['limit'] = (int) $options['limit'];
        }
        if (null !== $options['exclude_unique']) {
            $options['exclude_unique'] = in_array($options['exclude_unique'], ['true', true], true);
        }
        if (null === $options['user_groups_ids']) {
            $options['user_groups_ids'] = [];
        }

        return $options;
    }

    /**
     * @param string $search
     * @param array  $options
     *
     * @return QueryBuilder
     */
    protected function findBySearchQb($search, array $options)
    {
        //TODO: refactor on master because this is exactly the same that FamilySearchableRepository
        //TODO: and should be put in Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository\SearchableRepository
        $qb = $this->entityManager
            ->createQueryBuilder()
            ->select('a')
            ->from($this->entityName, 'a');

        $options = $this->resolveOptions($options);

        if (null !== $search) {
            $qb->leftJoin('a.translations', 'at');
            $qb->where('a.code like :search')->setParameter('search', '%'.$search.'%');
            if (null !== $localeCode = $options['locale']) {
                $qb->orWhere('at.label like :search AND at.locale like :locale');
                $qb->setParameter('search', '%'.$search.'%');
                $qb->setParameter('locale', $localeCode);
            }
        }
        if (!empty($options['identifiers'])) {
            $qb->andWhere('a.code in (:codes)');
            $qb->setParameter('codes', $options['identifiers']);
        }

        if (!empty($options['excluded_identifiers'])) {
            $qb->andWhere('a.code not in (:codes)');
            $qb->setParameter('codes', $options['excluded_identifiers']);
        }

        if (null !== $options['limit']) {
            $qb->setMaxResults($options['limit']);
            if (null !== $options['page']) {
                $qb->setFirstResult($options['limit'] * ($options['page'] - 1));
            }
        }

        //TODO: this part is specific to attributes
        if ($options['exclude_unique']) {
            $qb->andWhere('a.unique = 0');
        }

        if (null !== $options['types']) {
            $qb->andWhere('a.type in (:types)');
            $qb->setParameter('types', $options['types']);
        }

        $qb->leftJoin('a.group', 'ag');
        if (!empty($options['attribute_groups'])) {
            $qb->andWhere('ag.code in (:groups)');
            $qb->setParameter('groups', $options['attribute_groups']);
        }

        $qb->orderBy('ag.sortOrder');

        $qb->groupBy('a.id');

        return $qb;
    }
}
