<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Sorter;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Doctrine\AttributeSorterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\FieldSorterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\ValueJoin;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

/**
 * Base sorter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseSorter implements AttributeSorterInterface, FieldSorterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var CatalogContext */
    protected $context;

    /**
     * Alias counter, to avoid duplicate alias name
     * @return integer
     */
    protected $aliasCounter = 1;

    /**
     * Instanciate a sorter
     *
     * @param QueryBuilder   $qb
     * @param CatalogContext $context
     */
    public function __construct(QueryBuilder $qb, CatalogContext $context)
    {
        $this->qb      = $qb;
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeSorter(AbstractAttribute $attribute, $direction)
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

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldSorter($field, $direction)
    {
        $field = current($this->qb->getRootAliases()).'.'.$field;
        $this->qb->addOrderBy($field, $direction);

        return $this;
    }

    /**
     * Prepare join to attribute condition with current locale and scope criterias
     *
     * @param AbstractAttribute $attribute the attribute
     * @param string            $joinAlias the value join alias
     *
     * @return string
     */
    protected function prepareAttributeJoinCondition(AbstractAttribute $attribute, $joinAlias)
    {
        $joinHelper = new ValueJoin($this->qb, $this->context);

        return $joinHelper->prepareCondition($attribute, $joinAlias);
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
