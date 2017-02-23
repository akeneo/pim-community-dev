<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamWorkAssistantBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PimEnterprise\Component\TeamWorkAssistant\Repository\AttributePermissionRepositoryInterface;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class AttributePermissionRepository extends EntityRepository implements AttributePermissionRepositoryInterface
{
    /**
     * @param EntityManager $em
     * @param string        $class
     */
    public function __construct(EntityManager $em, $class)
    {
        parent::__construct($em, $em->getClassMetadata($class));
    }

    /**
     * {@inheritdoc}
     */
    public function findContributorsUserGroups(array $attributeGroupIdentifiers)
    {
        $queryBuilder = $this->createQueryBuilder('a');

        $queryBuilder->select('g')
            ->innerJoin('OroUserBundle:Group', 'g', 'WITH', 'a.userGroup = g.id')
            ->leftJoin('a.attributeGroup', 'ag')
            ->where($queryBuilder->expr()->eq('a.editAttributes', true))
            ->andWhere($queryBuilder->expr()->in('ag.code', ':identifiers'))
            ->setParameter('identifiers', $attributeGroupIdentifiers);

        return $queryBuilder->getQuery()->getResult();
    }
}
