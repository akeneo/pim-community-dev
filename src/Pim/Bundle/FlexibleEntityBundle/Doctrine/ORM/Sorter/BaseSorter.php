<?php

namespace Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\SorterInterface;
use Pim\Bundle\FlexibleEntityBundle\Doctrine\ORM\ValueJoin;

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
     * Locale code
     * @var string
     */
    protected $locale;

    /**
     * Scope code
     * @var string
     */
    protected $scope;

    /**
     * TODO : we must use same instance of Filter to ensure the increment
     *
     * Alias counter, to avoid duplicate alias name
     * @return integer
     */
    protected $aliasCounter = 1;

    /**
     * Instanciate a sorter
     *
     * @param QueryBuilder $qb
     * @param string       $locale
     * @param scope        $scope
     */
    public function __construct(QueryBuilder $qb, $locale, $scope)
    {
        $this->qb     = $qb;
        $this->locale = $locale;
        $this->scope  = $scope;
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
        $joinHelper = new ValueJoin($this->qb, $this->locale, $this->scope);
        $condition = $joinHelper->prepareCondition($attribute, $joinAlias);

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
