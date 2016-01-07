<?php

namespace PimEnterprise\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\AttributeSearchableRepository as BaseAttributeSearchableRepository;

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
     * @return AttributeInterface[]
     */
    public function findBySearch($search = null, array $options = [])
    {
        /** @var QueryBuilder $qb */
        $qb = $this->findBySearchQb($search, $options);

        if ($options['editable']) {
            $qb->leftJoin(
                'PimEnterprise\Bundle\SecurityBundle\Entity\AttributeGroupAccess',
                'aga',
                'WITH',
                'ag.id = aga.attributeGroup'
            );
            $qb->groupBy('a.id');
            $qb->andWhere('aga.userGroup IN (:userGroupsIds)')->setParameter('userGroupsIds', $options['user_groups_ids']);
            $qb->andWhere('aga.editAttributes = 1');
        }

        return $qb->getQuery()->getResult();
    }
}
