<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\ORM\Repository;

use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleRelationRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Rule relation repository
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class RuleRelationRepository extends EntityRepository implements RuleRelationRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function isResourceImpactedByRule($resourceId, $resourceName)
    {
        $qb = $this->createQueryBuilder('rlr')
            ->select('1')
            ->where('rlr.resourceName = :resource_name AND rlr.resourceId = :resource_id')
            ->setMaxResults(1)
            ->setParameters([':resource_name' => $resourceName, ':resource_id' => $resourceId]);

        return null !== $qb->getQuery()->getOneOrNullResult();
    }
}
