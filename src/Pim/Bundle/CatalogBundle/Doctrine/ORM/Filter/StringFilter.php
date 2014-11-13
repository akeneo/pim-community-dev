<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Condition\CriteriaCondition;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Join\ValueJoin;
use Pim\Bundle\CatalogBundle\Doctrine\Query\Operators;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Exception\ProductQueryException;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;

/**
 * String filter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class StringFilter implements AttributeFilterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var array */
    protected $supportedAttributes;

    /** @var array */
    protected $supportedFields;

    /** @var array */
    protected $supportedOperators;

    /**
     * Instanciate the base filter
     *
     * @param array $supportedAttributes
     * @param array $supportedFields
     * @param array $supportedOperators
     */
    public function __construct(
        array $supportedAttributes = [],
        array $supportedFields = [],
        array $supportedOperators = []
    ) {
        $this->supportedAttributes = $supportedAttributes;
        $this->supportedFields     = $supportedFields;
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
    public function addAttributeFilter(AttributeInterface $attribute, $operator, $value, $locale = null, $scope = null)
    {
        $joinAlias = 'filter'.$attribute->getCode();
        $backendField = sprintf('%s.%s', $joinAlias, $attribute->getBackendType());

        if ($operator === Operators::IS_EMPTY) {
            $this->qb->leftJoin(
                $this->qb->getRootAlias().'.values',
                $joinAlias,
                'WITH',
                $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope)
            );
            $this->qb->andWhere($this->prepareCriteriaCondition($backendField, $operator, $value));
        } else {
            $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias, $locale, $scope);
            $condition .= ' AND ' . $this->prepareCondition($backendField, $operator, $value);

            $this->qb->innerJoin(
                $this->qb->getRootAlias() . '.values',
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
    public function supportsField($field)
    {
        return in_array($field, $this->supportedFields);
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
     * Prepare conditions of the filter
     * @param string|array $operator
     * @param string|array $value
     *
     * @return string
     */
    protected function prepareCondition($backendField, $operator, $value)
    {
        switch ($operator) {
            case Operators::STARTS_WITH:
                $operator = 'LIKE';
                $value    = $value . '%';
                break;
            case Operators::ENDS_WITH:
                $operator = 'LIKE';
                $value    = '%' . $value;
                break;
            case Operators::CONTAINS:
                $operator = 'LIKE';
                $value    = '%' . $value . '%';
                break;
            case Operators::DOES_NOT_CONTAIN:
                $operator = 'NOT LIKE';
                $value    = '%' . $value . '%';
                break;
            default:
                break;
        }

        return $this->prepareCriteriaCondition($backendField, $operator, $value);
    }

    /**
     * Prepare criteria condition with field, operator and value
     *
     * @param string|array $field    the backend field name
     * @param string|array $operator the operator used to filter
     * @param string|array $value    the value(s) to filter
     *
     * @return string
     * @throws ProductQueryException
     */
    protected function prepareCriteriaCondition($field, $operator, $value)
    {
        $criteriaCondition = new CriteriaCondition($this->qb);

        return $criteriaCondition->prepareCriteriaCondition($field, $operator, $value);
    }

    /**
     * Prepare join to attribute condition with current locale and scope criterias
     *
     * @param AttributeInterface $attribute the attribute
     * @param string             $joinAlias the value join alias
     * @param string             $locale    the locale
     * @param string             $scope     the scope
     *
     * @throws ProductQueryException
     *
     * @return string
     */
    protected function prepareAttributeJoinCondition(AttributeInterface $attribute, $joinAlias, $locale = null, $scope = null)
    {
        $joinHelper = new ValueJoin($this->qb);

        return $joinHelper->prepareCondition($attribute, $joinAlias, $locale, $scope);
    }
}
