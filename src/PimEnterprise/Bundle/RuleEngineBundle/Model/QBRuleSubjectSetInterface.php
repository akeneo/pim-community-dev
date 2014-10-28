<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\RuleEngineBundle\Model;

/**
 * Subjects set that can return a query builder
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
interface QBRuleSubjectSetInterface
{
    /**
     * @return \Doctrine\ORM\QueryBuilder|\Doctrine\ODM\MongoDB\Query\Builder
     */
    public function getQueryBuilder();

    /**
     * @param \Doctrine\ORM\QueryBuilder|\Doctrine\ODM\MongoDB\Query\Builder
     */
    public function setQueryBuilder($queryBuilder);
}
