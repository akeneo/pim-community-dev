<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Repository;

use Akeneo\Bundle\RuleEngineBundle\Doctrine\ORM\QueryBuilder\RuleQueryBuilder;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Rule repository interface
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
interface RuleDefinitionRepositoryInterface extends IdentifiableObjectRepositoryInterface, ObjectRepository
{
    /**
     * Retrieve all rule ordered by priority
     *
     * @return RuleDefinitionInterface[]
     */
    public function findAllOrderedByPriority();

    /**
     * @return RuleQueryBuilder
     */
    public function createDatagridQueryBuilder();
}
