<?php

namespace Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\FlexibleEntityBundle\Exception\FlexibleQueryException;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\FlexibleQueryBuilderInterface;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\SorterInterface;

/**
 * Base sorter 
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseSorter implements SorterInterface
{
    /**
     * QueryBuilder
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * TODO : we must use same instance of Filter to ensure the increment
     *
     * Alias counter, to avoid duplicate alias name
     * @return integer
     */
    protected $aliasCounter = 1;

    /**
     * Instanciate a filter
     *
     * @param QueryBuilder $qb
     */
    public function __construct(QueryBuilder $qb)
    {
        $this->qb = $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function add(AbstractAttribute $attribute, $direction)
    {
        $aliasPrefix = 'sorter';
        $joinAlias   = $aliasPrefix.'V'.$attribute->getCode().$this->aliasCounter++;
        $backendType = $attribute->getBackendType();

        // join to value and sort on
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);

        // Remove current join in order to put the orderBy related join
        // at first place in the join queue for performances reasons
        $joinsSet = $this->qb->getDQLPart('join');
        $this->qb->resetDQLPart('join');

        $this->qb->leftJoin(
            $this->qb->getRootAlias().'.'.$attribute->getBackendStorage(),
            $joinAlias,
            'WITH',
            $condition
        );
        $this->qb->addOrderBy($joinAlias.'.'.$backendType, $direction);

        // Reapply previous join after the orderBy related join
        $this->applyJoins($joinsSet);
    }

    /**
     * TODO : move this method in another object cause used by both sorter and filter
     *
     * Prepare join to attribute condition with current locale and scope criterias
     *
     * @param AbstractAttribute $attribute the attribute
     * @param string            $joinAlias the value join alias
     *
     * @throws FlexibleQueryException
     *
     * @return string
     */
    protected function prepareAttributeJoinCondition(AbstractAttribute $attribute, $joinAlias)
    {
        $condition = $joinAlias.'.attribute = '.$attribute->getId();

        if ($attribute->isTranslatable()) {
            if ($this->getLocale() === null) {
                throw new FlexibleQueryException('Locale must be configured');
            }
            $condition .= ' AND '.$joinAlias.'.locale = '.$this->qb->expr()->literal($this->getLocale());
        }
        if ($attribute->isScopable()) {
            if ($this->getScope() === null) {
                throw new FlexibleQueryException('Scope must be configured');
            }
            $condition .= ' AND '.$joinAlias.'.scope = '.$this->qb->expr()->literal($this->getScope());
        }

        return $condition;
    }

    /**
     * Reapply joins from a set of joins got from getDQLPart('join')
     *
     * @param array $joinsSet
     */
    protected function applyJoins($joinsSet)
    {
        foreach ($joinsSet as $joins) {
            foreach ($joins as $join) {
                if ($join->getJoinType() === Join::LEFT_JOIN) {
                    $this->qb->leftJoin(
                        $join->getJoin(),
                        $join->getAlias(),
                        $join->getConditionType(),
                        $join->getCondition(),
                        $join->getIndexBy()
                    );
                } else {
                    $this->qb->join(
                        $join->getJoin(),
                        $join->getAlias(),
                        $join->getConditionType(),
                        $join->getCondition(),
                        $join->getIndexBy()
                    );
                }
            }
        }
    }
}
