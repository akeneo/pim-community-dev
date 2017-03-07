<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Analytic;

use Akeneo\Component\Analytics\DataCollectorInterface;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Returns count of Product Draft
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class ProductDraftAnalyticProvider implements DataCollectorInterface
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
        $result = $this->em->createQueryBuilder('p')
            ->from($this->entityName, 'p')
            ->select('count(p.id)')
            ->getQuery()
            ->getSingleScalarResult();

        return ['nb_product_drafts' => $result];
    }
}
