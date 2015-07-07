<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Repository;

use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Rule repository interface
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
interface RuleRelationRepositoryInterface extends ObjectRepository
{
    /**
     * Query the database to test if a resource is impacted by at least one rule
     *
     * @param $resourceId
     * @param $resourceName
     *
     * @return bool
     */
    public function isResourceImpactedByRule($resourceId, $resourceName);
}
