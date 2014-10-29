<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Exception\ProductQueryException;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Join\ValueJoin;

/**
 * Filtering by multi option backend type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsFilter implements AttributeFilterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var array */
    protected $supportedAttributes;

    /** @var array */
    protected $supportedOperators;

    /**
     * Instanciate the base filter
     *
     * @param array $supportedAttributes
     * @param array $supportedOperators
     */
    public function __construct(
        array $supportedAttributes = [],
        array $supportedOperators = []
    ) {
        $this->supportedAttributes = $supportedAttributes;
        $this->supportedOperators  = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder($queryBuilder)
    {
        if (!(($queryBuilder instanceof QueryBuilder))) {
            throw new \InvalidArgumentException('Query builder should be an instance of Doctrine\ORM\QueryBuilder');
        }

        $this->qb = $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(AttributeInterface $attribute, $operator, $value, array $context = [])
    {
        $joinAlias    = 'filter'.$attribute->getCode();
        $joinAliasOpt = 'filterO'.$attribute->getCode();
        $backendField = sprintf('%s.%s', $joinAliasOpt, 'id');

        //TODO: the value should not contain empty (comes from the frontend) => it should be in the operator
        if (in_array('empty', $value)) {
            $this->qb->leftJoin(
                $this->qb->getRootAlias().'.values',
                $joinAlias,
                'WITH',
                $this->prepareAttributeJoinCondition($attribute, $joinAlias, $context)
            );

            $condition = $this->prepareEmptyCondition($backendField, $operator, $value);
            $this->qb
                ->leftJoin($joinAlias .'.'. $attribute->getBackendType(), $joinAliasOpt)
                ->andWhere($condition);
        } else {
            $this->qb
                ->innerJoin(
                    $this->qb->getRootAlias().'.values',
                    $joinAlias,
                    'WITH',
                    $this->prepareAttributeJoinCondition($attribute, $joinAlias, $context)
                )
                ->innerJoin(
                    $joinAlias .'.'. $attribute->getBackendType(),
                    $joinAliasOpt,
                    'WITH',
                    $this->qb->expr()->in($backendField, $value)
                );
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AttributeInterface $attribute)
    {
        return in_array($attribute->getAttributeType(), $this->supportedAttributes);
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
     * @param AttributeInterface $attribute the attribute
     * @param string             $joinAlias the value join alias
     * @param array              $context   the context
     *
     * @throws ProductQueryException
     *
     * @return string
     */
    protected function prepareAttributeJoinCondition(AttributeInterface $attribute, $joinAlias, array $context)
    {
        $joinHelper = new ValueJoin($this->qb);

        return $joinHelper->prepareCondition($attribute, $joinAlias, $context);
    }

    /**
     * Prepare empty condition for options
     *
     * @param string $backendField
     * @param string $operator
     * @param string $value
     *
     * @return \Doctrine\ORM\Query\Expr
     */
    protected function prepareEmptyCondition($backendField, $operator, $value)
    {
        unset($value[array_search('empty', $value)]);
        $expr = $this->qb->expr()->isNull($backendField);

        if (count($value) > 0) {
            $exprIn = $this->qb->expr()->in($backendField, $value);
            $expr   = $this->qb->expr()->orX($expr, $exprIn);
        }

        return $expr;
    }
}
