<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Doctrine\ORM\Repository;

use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Rule definition repository
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RuleDefinitionRepository extends EntityRepository implements RuleDefinitionRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findAllOrderedByPriority()
    {
        return $this->findBy([], ['priority' => 'DESC']);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->findOneBy(['code' => $code]);
    }
}
