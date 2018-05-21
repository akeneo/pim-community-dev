<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\Analytic;

use Akeneo\Tool\Component\Analytics\DataCollectorInterface;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Data collector to return the attribute Group Access count
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AttributeGroupAccessAnalyticProvider implements DataCollectorInterface
{
    /** @var EntityManager  */
    protected $entityManager;

    /** @var string */
    protected $entityName;

    /**
     * @param EntityManager $entityManager
     * @param string        $entityName
     */
    public function __construct(EntityManager $entityManager, $entityName)
    {
        $this->entityManager = $entityManager;
        $this->entityName = $entityName;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        $result = (int) $this->entityManager->createQueryBuilder('a')
            ->select('COUNT(DISTINCT a.attributeGroup)')
            ->from($this->entityName, 'a')
            ->innerJoin('a.userGroup', 'g')
            ->where('g.name <> :default_group')
            ->setParameter('default_group', User::GROUP_DEFAULT)
            ->getQuery()
            ->getSingleScalarResult();

        return ['nb_custom_attribute_group_accesses' => $result];
    }
}
