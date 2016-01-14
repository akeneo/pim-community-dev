<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Doctrine\ORM\QueryBuilder;

use Doctrine\ORM\QueryBuilder;

/**
 * Rule query builder
 *
 * @author Clement Gautier <clement.gautier@akeneo.com>
 */
class RuleQueryBuilder extends QueryBuilder
{
    /**
     * @param string $resourceName
     * @param int    $resourceId
     */
    public function joinResource($resourceName, $resourceId)
    {
        $this->join('r.relations', 'rr');
        $this->andWhere('rr.resourceName = :resourceName');
        $this->andWhere('rr.resourceId = :resourceId');

        $this->setParameter('resourceName', $resourceName);
        $this->setParameter('resourceId', $resourceId);
    }
}
