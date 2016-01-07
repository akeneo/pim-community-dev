<?php

namespace PimEnterprise\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\AttributeSearchableRepository as BaseAttributeSearchableRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Attribute searchable repository
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AttributeSearchableRepository extends BaseAttributeSearchableRepository
{
    /**
     * {@inheritdoc}
     *
     * @return QueryBuilder
     */
    public function findBySearchQb($search = null, array $options = [])
    {
        $qb = parent::findBySearchQb($search, $options);
        $options = $this->resolveOptions($options);

        $qb->leftJoin(
            'PimEnterprise\Bundle\SecurityBundle\Entity\AttributeGroupAccess',
            'aga',
            'WITH',
            'ag.id = aga.attributeGroup'
        );
        $qb->groupBy('a.id');
        $qb->andWhere('aga.userGroup IN (:userGroupsIds)')
            ->setParameter('userGroupsIds', $options['user_groups_ids']);
        $qb->andWhere('aga.viewAttributes = 1');

        if ($options['editable']) {
            $qb->andWhere('aga.editAttributes = 1');
        }

        return $qb;
    }
}
