<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Analytic;

use Akeneo\Tool\Component\Analytics\DataCollectorInterface;
use Akeneo\UserManagement\Component\Model\User;
use Doctrine\ORM\EntityManager;

/**
 * Data collector to return the locale access count
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class LocaleAccessAnalyticProvider implements DataCollectorInterface
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
            ->select('COUNT(DISTINCT l.id)')
            ->from($this->entityName, 'a')
            ->innerJoin('a.userGroup', 'g')
            ->innerJoin('a.locale', 'l')
            ->where('g.name <> :default_group')
            ->andWhere('l.activated = :is_activated')
            ->setParameter('default_group', User::GROUP_DEFAULT)
            ->setParameter('is_activated', true)
            ->getQuery()
            ->getSingleScalarResult();

        return ['nb_custom_locale_accesses' => $result];
    }
}
