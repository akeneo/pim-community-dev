<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\TextFilterType;
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
    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected string $entityName,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @return AttributeInterface[]
     */
    public function findBySearch($search = null, array $options = [])
    {
        $qb = $this->findBySearchQb($search, $options);

        $qb->orderBy('ag.sortOrder, a.sortOrder');
        $qb->groupBy('a.id');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array
     */
    protected function resolveOptions(array $options)
    {
        $options = $this->getResolver()->resolve($options);

        if (null !== $options['page']) {
            $options['page'] = (int) $options['page'];
        }
        if (null !== $options['limit']) {
            $options['limit'] = (int) $options['limit'];
        }
        if (null === $options['user_groups_ids']) {
            $options['user_groups_ids'] = [];
        }

        return $options;
    }

    protected function getResolver(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(
            [
                'attribute_groups'       => [],
                'code'                   => null,
                'excluded_identifiers'   => [],
                'families'               => null,
                'identifiers'            => [],
                'is_locale_specific'     => null,
                'limit'                  => null,
                'locale'                 => null,
                'localizable'            => null,
                'page'                   => null,
                'quality'                => null,
                'rights'                 => true,
                'scopable'               => null,
                'smart'                  => null,
                'types'                  => null,
                'useable_as_grid_filter' => null,
                'user_groups_ids'        => null,
            ]
        );
        $resolver->setAllowedTypes('attribute_groups', ['array']);
        $resolver->setAllowedTypes('code', ['array', 'null']);
        $resolver->setAllowedTypes('excluded_identifiers', 'array');
        $resolver->setAllowedTypes('families', ['array', 'null']);
        $resolver->setAllowedTypes('identifiers', 'array');
        $resolver->setAllowedTypes('is_locale_specific', ['bool', 'null']);
        $resolver->setAllowedTypes('limit', ['int', 'string', 'null']);
        $resolver->setAllowedTypes('locale', ['string', 'null']);
        $resolver->setAllowedTypes('localizable', ['bool', 'null']);
        $resolver->setAllowedTypes('page', ['int', 'string', 'null']);
        $resolver->setAllowedTypes('quality', ['string', 'null']);
        $resolver->setAllowedTypes('rights', ['bool']);
        $resolver->setAllowedTypes('scopable', ['bool', 'null']);
        $resolver->setAllowedTypes('smart', ['bool', 'null']);
        $resolver->setAllowedTypes('types', ['array', 'null']);
        $resolver->setAllowedTypes('useable_as_grid_filter', ['bool', 'null']);
        $resolver->setAllowedTypes('user_groups_ids', ['array', 'null']);

        return $resolver;
    }

    /**
     * @param string $search
     * @param array  $options
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function findBySearchQb($search, array $options)
    {
        //TODO: refactor on master because this is exactly the same that FamilySearchableRepository
        //TODO: and should be put in Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository\SearchableRepository
        $qb = $this->entityManager
            ->createQueryBuilder()
            ->select('a')
            ->from($this->entityName, 'a');

        $options = $this->resolveOptions($options);

        if (null !== $search) {
            $qb->leftJoin('a.translations', 'at');
            $qb->where('a.code like :search')->setParameter('search', '%' . $search . '%');
            if (null !== $localeCode = $options['locale']) {
                $qb->orWhere('at.label like :search AND at.locale like :locale');
                $qb->setParameter('search', '%' . $search . '%');
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

        if (null !== $options['localizable']) {
            $qb->andWhere('a.localizable = :localizable');
            $qb->setParameter('localizable', $options['localizable']);
        }

        if (null !== $options['scopable']) {
            $qb->andWhere('a.scopable = :scopable');
            $qb->setParameter('scopable', $options['scopable']);
        }

        if (null !== $options['is_locale_specific']) {
            $qb->leftJoin('a.availableLocales', 'al');
            $qb->andWhere(sprintf('al.id IS %s', $options['is_locale_specific'] ? 'NOT NULL' : 'NULL'));
        }

        if (null !== $options['useable_as_grid_filter']) {
            $qb->andWhere('a.useableAsGridFilter = :useable_as_grid_filter');
            $qb->setParameter('useable_as_grid_filter', $options['useable_as_grid_filter']);
        }

        if (null !== $options['code']) {
            switch ($options['code']['type']) {
                case TextFilterType::TYPE_CONTAINS:
                    $qb->andWhere('a.code LIKE :code');
                    $qb->setParameter('code', '%'.$options['code']['value'].'%');
                    break;
                case TextFilterType::TYPE_NOT_CONTAINS:
                    $qb->andWhere('a.code NOT LIKE :code');
                    $qb->setParameter('code', '%'.$options['code']['value'].'%');
                    break;
                case TextFilterType::TYPE_EQUAL:
                    $qb->andWhere('a.code = :code');
                    $qb->setParameter('code', $options['code']['value']);
                    break;
                case TextFilterType::TYPE_STARTS_WITH:
                    $qb->andWhere('a.code LIKE :code');
                    $qb->setParameter('code', $options['code']['value'].'%');
                    break;
                case TextFilterType::TYPE_EMPTY:
                    $qb->andWhere('a.code = :code');
                    $qb->setParameter('code', null);
                    break;
                default:
                    throw new \Exception('unknown text filter type given, for code filter');
            }
        }

        if (null !== $options['types']) {
            $qb->andWhere('a.type in (:types)');
            $qb->setParameter('types', $options['types']);
        }

        if (null !== $options['families']) {
            $qb->leftJoin('a.families', 'af');
            $qb->andWhere('af.code IN (:families)');
            $qb->setParameter('families', $options['families']);
        }

        $qb->leftJoin('a.group', 'ag');
        if (!empty($options['attribute_groups'])) {
            $qb->andWhere('ag.code in (:groups)');
            $qb->setParameter('groups', $options['attribute_groups']);
        }

        return $qb;
    }

    public function count(?string $search, array $options): int
    {
        $qb = $this->findBySearchQb($search, $options);

        return (int) $qb->select('COUNT(DISTINCT a.id)')->getQuery()->getSingleScalarResult();
    }
}
