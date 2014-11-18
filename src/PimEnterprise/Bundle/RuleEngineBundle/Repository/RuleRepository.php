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

use Doctrine\ORM\EntityRepository;

/**
 * Rule repository
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RuleRepository extends EntityRepository implements RuleRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findAllOrderedByPriority()
    {
        return $this->findBy([], ['priority' => 'DESC']);
    }
}
