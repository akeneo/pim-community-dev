<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Exception\ProductQueryException;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\ValueJoin;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

/**
 * Filtering by multi option backend type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsFilter implements AttributeFilterInterface
{
    /**
     * @var QueryBuilder
     */
    protected $qb;

    /** @var CatalogContext */
    protected $context;

    /** @var array */
    protected $supportedOperators;

    /**
     * Instanciate the base filter
     *
     * @param CatalogContext $context
     */
    public function __construct(CatalogContext $context)
    {
        $this->context = $context;
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
    public function addAttributeFilter(AttributeInterface $attribute, $operator, $value)
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
                $this->prepareAttributeJoinCondition($attribute, $joinAlias)
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
                    $this->prepareAttributeJoinCondition($attribute, $joinAlias)
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
        return $attribute->getAttributeType() === 'pim_catalog_multiselect';
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
     *
     * @throws ProductQueryException
     *
     * @return string
     */
    protected function prepareAttributeJoinCondition(AttributeInterface $attribute, $joinAlias)
    {
        $joinHelper = new ValueJoin($this->qb, $this->context);

        return $joinHelper->prepareCondition($attribute, $joinAlias);
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
