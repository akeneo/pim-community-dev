<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Doctrine\Operators;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface;

/**
 * Entity filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityFilter implements FieldFilterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var array */
    protected $supportedFields;

    /** @var array */
    protected $supportedOperators;

    /**
     * Instanciate the base filter
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
    public function addFieldFilter($field, $operator, $value, array $context = [])
    {
        $rootAlias  = $this->qb->getRootAlias();
        $entityAlias = 'filter'.$field;
        $this->qb->leftJoin($rootAlias.'.'.$field, $entityAlias);

        if ($operator === Operators::NOT_IN_LIST) {
            $this->qb->andWhere(
                $this->qb->expr()->orX(
                    $this->qb->expr()->notIn($entityAlias.'.id', $value),
                    $this->qb->expr()->isNull($entityAlias.'.id')
                )
            );
        } else {
            // TODO: fix this weird support of EMPTY operator
            if (in_array('empty', $value)) {
                unset($value[array_search('empty', $value)]);
                $exprNull = $this->qb->expr()->isNull($entityAlias.'.id');

                if (count($value) > 0) {
                    $exprIn = $this->qb->expr()->in($entityAlias.'.id', $value);
                    $expr = $this->qb->expr()->orX($exprNull, $exprIn);
                } else {
                    $expr = $exprNull;
                }
            } else {
                $expr = $this->qb->expr()->in($entityAlias.'.id', $value);
            }

            $this->qb->andWhere($expr);
        }

        return $this;
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
}
