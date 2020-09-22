<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\RuleEngineBundle\Repository;

use Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\ORM\QueryBuilder\RuleQueryBuilder;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Rule repository interface
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface RuleDefinitionRepositoryInterface extends IdentifiableObjectRepositoryInterface, ObjectRepository
{
    /**
     * Retrieve all enabled rules ordered by priority
     *
     * @return RuleDefinitionInterface[]
     */
    public function findEnabledOrderedByPriority();

    /**
     * @return RuleQueryBuilder
     */
    public function createDatagridQueryBuilder();
}
