<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

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

    /** @var CatalogContext */
    protected $context;

    /** @var array */
    protected $supportedFields;

    /** @var array */
    protected $supportedOperators;

    /**
     * Instanciate the base filter
     *
     * @param CatalogContext $context
     * @param array          $extraSupportedFields
     */
    public function __construct(CatalogContext $context, array $extraSupportedFields = [])
    {
        $this->context = $context;
        $this->supportedFields = array_merge(
            ['family', 'groups'],
            $extraSupportedFields
        );
        $this->supportedOperators = ['IN', 'NOT IN'];
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
    public function addFieldFilter($field, $operator, $value)
    {
        $rootAlias  = $this->qb->getRootAlias();
        $entityAlias = 'filter'.$field;
        $this->qb->leftJoin($rootAlias.'.'.$field, $entityAlias);

        if ($operator === 'NOT IN') {
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
