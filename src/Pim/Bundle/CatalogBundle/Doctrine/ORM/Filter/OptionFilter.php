<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Exception\ProductQueryException;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Join\ValueJoin;

/**
 * Filtering by simple option backend type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionFilter implements AttributeFilterInterface
{
    /**
     * @var QueryBuilder
     */
    protected $qb;

    /** @var array */
    protected $supportedOperators;

    /**
     * Instanciate the base filter
     */
    public function __construct()
    {
        $this->supportedOperators = ['IN'];
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder($queryBuilder)
    {
        $this->qb = $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(AbstractAttribute $attribute, $operator, $value, $context = [])
    {
        $joinAlias = 'filter'.$attribute->getCode();

        // prepare join value condition
        $optionAlias = $joinAlias .'.option';
        //TODO: the value should not contain empty  (comes from the frontend) => it should be in the operator
        if (in_array('empty', $value)) {
            unset($value[array_search('empty', $value)]);
            $expr = $this->qb->expr()->isNull($optionAlias);

            if (count($value) > 0) {
                $exprIn = $this->qb->expr()->in($optionAlias, $value);
                $expr = $this->qb->expr()->orX($expr, $exprIn);
            }

            $this->qb->leftJoin(
                $this->qb->getRootAlias().'.values',
                $joinAlias,
                'WITH',
                $this->prepareAttributeJoinCondition($attribute, $joinAlias, $context)
            );
            $this->qb->andWhere($expr);
        } else {
            // inner join to value
            $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $context);
            $condition .= ' AND ( '. $this->qb->expr()->in($optionAlias, $value) .' ) ';

            $this->qb->innerJoin(
                $this->qb->getRootAlias().'.values',
                $joinAlias,
                'WITH',
                $condition
            );
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AbstractAttribute $attribute)
    {
        return $attribute->getAttributeType() === 'pim_catalog_simpleselect';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsOperator($operator)
    {
        return in_array($operator, $this->supportedOperators);
    }

    /**
     * {@inheritdoc}
     */
    public function getOperators()
    {
        return $this->supportedOperators;
    }

    /**
     * Prepare join to attribute condition with current locale and scope criterias
     *
     * @param AbstractAttribute $attribute the attribute
     * @param string            $joinAlias the value join alias
     * @param array             $context   the context
     *
     * @throws ProductQueryException
     *
     * @return string
     */
    protected function prepareAttributeJoinCondition(AbstractAttribute $attribute, $joinAlias, $context)
    {
        $joinHelper = new ValueJoin($this->qb);

        return $joinHelper->prepareCondition($attribute, $joinAlias, $context);
    }
}
