<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\Analytic;

use Akeneo\Tool\Component\Analytics\DataCollectorInterface;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Data collector to return the projects count
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class ProjectAnalyticProvider implements DataCollectorInterface
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
        $value = (int) $this->entityManager->createQueryBuilder()
            ->from($this->entityName, 'p')
            ->select('count(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return ['nb_projects' => $value];
    }
}
