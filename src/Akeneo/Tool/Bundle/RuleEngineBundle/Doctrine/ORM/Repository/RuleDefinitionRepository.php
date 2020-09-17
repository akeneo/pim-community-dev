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
        $qb->from($this->_entityName, 'r');

        $labelExpr = 'COALESCE(NULLIF(translation.label, \'\'), CONCAT(\'[\', r.code, \']\'))';
        $qb->leftJoin('r.translations', 'translation', 'WITH', 'translation.locale = :localeCode');

        $qb
            ->addSelect('r.id')
            ->addSelect('r.code')
            ->addSelect('r.content')
            ->addSelect('r.impactedSubjectCount')
            ->addSelect('r.priority')
            ->addSelect(sprintf('%s AS label', $labelExpr));

        return $qb;
    }

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
