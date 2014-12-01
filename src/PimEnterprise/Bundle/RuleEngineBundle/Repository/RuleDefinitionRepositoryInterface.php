<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Repository;

use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Rule repository interface
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface RuleDefinitionRepositoryInterface extends ObjectRepository
{
    /**
     * Retrieve all rule ordered by priority
     *
     * @return \PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface[]
     */
    public function findAllOrderedByPriority();
}
