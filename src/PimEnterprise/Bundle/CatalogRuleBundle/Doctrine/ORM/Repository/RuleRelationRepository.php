<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use PimEnterprise\Bundle\CatalogRuleBundle\Repository\RuleRelationRepositoryInterface;

/**
 * Rule relation repository
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class RuleRelationRepository extends EntityRepository implements RuleRelationRepositoryInterface
{
    /** @var string */
    protected $ruleRelationClass;

    /**
     * {@inheritdoc}
     */
    public function isResourceImpactedByRule($resourceId, $resourceName)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('1')
            ->from($this->getClassName(), 'rlr')
            ->where('rlr.resourceName = :resource_name AND rlr.resourceId = :resource_id')
            ->setMaxResults(1)
            ->setParameters([
                ':resource_name' => $resourceName,
                ':resource_id'   => $resourceId
            ]);

        return null !== $qb->getQuery()->getOneOrNullResult();
    }
}
