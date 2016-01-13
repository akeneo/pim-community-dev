<?php

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository\SearchableRepository;
use Doctrine\DBAL\Query\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Attribute searchable repository
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Soatware License (OSL 3.0)
 */
class AttributeSearchableRepository extends SearchableRepository
{
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
                'editable'             => false,
                'user_groups_ids'      => null,
                'types'                => null,
            ]
        );
        $resolver->setAllowedTypes('identifiers', 'array');
        $resolver->setAllowedTypes('excluded_identifiers', 'array');
        $resolver->setAllowedTypes('limit', ['int', 'string', 'null']);
        $resolver->setAllowedTypes('page', ['int', 'string', 'null']);
        $resolver->setAllowedTypes('locale', ['string', 'null']);
        $resolver->setAllowedTypes('exclude_unique', ['string', 'bool']);
        $resolver->setAllowedTypes('editable', ['string', 'bool']);
        $resolver->setAllowedTypes('user_groups_ids', ['string', 'null']);
        $resolver->setAllowedTypes('types', ['array', 'null']);

        $options = $resolver->resolve($options);

        if (null !== $options['page']) {
            $options['page'] = (int) $options['page'];
        }
        if (null !== $options['limit']) {
            $options['limit'] = (int) $options['limit'];
        }
        if (null !== $options['exclude_unique']) {
            $options['exclude_unique'] = (bool) $options['exclude_unique'];
        }
        if (null !== $options['editable']) {
            $options['editable'] = (int) $options['editable'];
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
        $qb = parent::buildQb($search, $options);
        $options = $this->resolveOptions($options);

        if ($options['exclude_unique']) {
            $qb->andWhere('entity.unique = 0');
        }

        if (null !== $options['types']) {
            $qb->andWhere('entity.attributeType in (:types)');
            $qb->setParameter('types', $options['types']);
        }

        $qb->leftJoin('entity.group', 'ag');
        $qb->orderBy('ag.code');
        $qb->orderBy('ag.sortOrder');

        $qb->groupBy('entity.id');

        return $qb;
    }
}
