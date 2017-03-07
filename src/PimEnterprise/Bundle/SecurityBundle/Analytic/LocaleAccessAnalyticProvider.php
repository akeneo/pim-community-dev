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

use Akeneo\Component\Analytics\DataCollectorInterface;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\UserBundle\Entity\User;

/**
 * Data collector to return the locale access count
 * *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class LocaleAccessAnalyticProvider implements DataCollectorInterface
{
    /** @var EntityManager  */
    protected $em;

    /** @var string */
    protected $entityName;

    /**
     * @param EntityManager $em
     * @param string        $class
     */
    public function __construct(EntityManager $em, $class)
    {
        $this->em = $em;
        $this->entityName = $em->getClassMetadata($class)->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        $associationMappings = $this->em->getClassMetadata($this->entityName)->getAssociationMappings();
        $localeClass = $associationMappings['locale']['targetEntity'];

        $result = (int) $this->em->createQueryBuilder('a')
            ->select('COUNT(DISTINCT l.id)')
            ->from($this->entityName, 'a')
            ->innerJoin('OroUserBundle:Group', 'g', 'WITH', 'a.userGroup = g.id')
            ->innerJoin($localeClass, 'l', 'WITH', 'a.locale = l.id')
            ->where('g.name <> :default_group')
            ->andWhere('l.activated = :is_activated')
            ->setParameter('default_group', User::GROUP_DEFAULT)
            ->setParameter('is_activated', true)
            ->getQuery()
            ->getSingleScalarResult();

        return ['nb_custom_locale_accesses' => $result];
    }
}
