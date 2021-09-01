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

use Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\ORM\QueryBuilder\RuleQueryBuilder;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
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
    public function createDatagridQueryBuilder()
    {
        $qb = new RuleQueryBuilder($this->_em);
        $qb->select('r.id, r.code, r.content, r.impactedSubjectCount');
        $qb->from($this->_entityName, 'r');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function findAllOrderedByPriority()
    {
        return $this->orderByPriority($this->findBy([]));
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

    /**
     * @param RuleDefinitionInterface[] $ruleDefinitions
     * @return RuleDefinitionInterface[]
     */
    private function orderByPriority(array $ruleDefinitions): array
    {
        usort($ruleDefinitions, static function (RuleDefinitionInterface $ruleA, RuleDefinitionInterface $ruleB) {
            return $ruleA->getPriority() < $ruleB->getPriority();
        });

        return $ruleDefinitions;
    }
}
