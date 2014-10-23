<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\MongoDB\Query\Expr;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\Doctrine\Operators;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface;

/**
 * Family filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyFilter implements FieldFilterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var array */
    protected $supportedFields;

    /** @var array */
    protected $supportedOperators;

    /**
     * Instanciate the filter
     *
     * @param array $supportedFields
     * @param array $supportedOperators
     */
    public function __construct(
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
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
    public function supportsField($field)
    {
        return in_array($field, $this->supportedFields);
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
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, array $context = [])
    {
        $value = is_array($value) ? $value : [$value];

        if ($operator === Operators::NOT_IN_LIST) {
            $this->qb->field($field)->notIn($value);
        } else {
            // TODO: fix this weird support of EMPTY operator
            if (in_array('empty', $value)) {
                unset($value[array_search('empty', $value)]);

                $expr = new Expr();
                $expr = $expr->field($field)->exists(false);
                $this->qb->addOr($expr);
            }

            if (count($value) > 0) {
                $expr = new Expr();
                $expr->field($field)->in($value);
                $this->qb->addOr($expr);
            }
        }

        return $this;
    }
}
