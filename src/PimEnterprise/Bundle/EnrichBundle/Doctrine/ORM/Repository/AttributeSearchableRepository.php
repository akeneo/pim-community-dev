<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository\AttributeSearchableRepository as BaseAttributeSearchableRepository;

/**
 * Attribute searchable repository
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AttributeSearchableRepository extends BaseAttributeSearchableRepository
{
    /** @var string */
    protected $attrGpAccessClass;

    /**
     * @param EntityManagerInterface $entityManager
     * @param string                 $attributeClass
     * @param string                 $attrGpAccessClass
     */
    public function __construct(EntityManagerInterface $entityManager, $attributeClass, $attrGpAccessClass)
    {
        parent::__construct($entityManager, $attributeClass);

        $this->attrGpAccessClass = $attrGpAccessClass;
    }

    /**
     * {@inheritdoc}
     */
    public function findBySearchQb($search = null, array $options = [])
    {
        $qb = parent::findBySearchQb($search, $options);
        $options = $this->resolveOptions($options);

        $qb->leftJoin(
            $this->attrGpAccessClass,
            'aga',
            'WITH',
            'ag.id = aga.attributeGroup'
        );
        $qb->groupBy('a.id');
        $qb->andWhere('aga.userGroup IN (:userGroupsIds)')
            ->setParameter('userGroupsIds', $options['user_groups_ids']);
        $qb->andWhere('aga.viewAttributes = 1');

        return $qb;
    }
}
