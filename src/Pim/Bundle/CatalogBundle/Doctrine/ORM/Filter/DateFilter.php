<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\ValueJoin;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\CriteriaCondition;

/**
 * Date filter
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateFilter implements FieldFilterInterface, AttributeFilterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var CatalogContext */
    protected $context;

    /** @var array */
    protected $supportedAttributes;

    /** @var array */
    protected $supportedFields;

    /** @var array */
    protected $supportedOperators;

    /**
     * Instanciate the filter
     *
     * @param CatalogContext $context
     * @param array          $extraSupportedFields
     */
    public function __construct(CatalogContext $context, array $extraSupportedFields = [])
    {
        $this->context = $context;
        $this->supportedAttributes = ['pim_catalog_date'];
        $this->supportedFields = array_merge(
            ['created', 'updated'],
            $extraSupportedFields
        );
        $this->supportedOperators = ['=', '<', '>', 'BETWEEN', 'NOT BETWEEN', 'EMPTY'];
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
    public function supportsAttribute(AbstractAttribute $attribute)
    {
        return in_array(
            $attribute->getAttributeType(),
            $this->supportedAttributes
        );
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
    public function addAttributeFilter(AbstractAttribute $attribute, $operator, $value)
    {
        $joinAlias = 'filter'.$attribute->getCode();
        $backendField = sprintf('%s.%s', $joinAlias, $attribute->getBackendType());

        if ($operator === 'EMPTY') {
            $this->qb->leftJoin(
                $this->qb->getRootAlias().'.values',
                $joinAlias,
                'WITH',
                $this->prepareAttributeJoinCondition($attribute, $joinAlias)
            );
            $this->qb->andWhere($this->prepareCriteriaCondition($backendField, $operator, $value));

        } elseif ($operator === 'NOT BETWEEN') {
            $this->qb->leftJoin(
                $this->qb->getRootAlias().'.values',
                $joinAlias,
                'WITH',
                $this->prepareAttributeJoinCondition($attribute, $joinAlias)
            );
            $this->qb->andWhere(
                $this->qb->expr()->orX(
                    $this->qb->expr()->lt($backendField, $this->getDateLiteralExpr($value[0])),
                    $this->qb->expr()->gt($backendField, $this->getDateLiteralExpr($value[1], true))
                )
            );

        } else {
            $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);
            $condition .= ' AND '.$this->prepareCriteriaCondition($backendField, $operator, $value);
            $this->qb->innerJoin(
                $this->qb->getRootAlias().'.values',
                $joinAlias,
                'WITH',
                $condition
            );
        }

        // TODO missing operators ?! check BaseFilter
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value)
    {
        $field = current($this->qb->getRootAliases()).'.'.$field;

        switch ($operator) {
            case 'BETWEEN':
                $this->qb->andWhere(
                    $this->qb->expr()->andX(
                        $this->qb->expr()->gt($field, $this->getDateLiteralExpr($value[0])),
                        $this->qb->expr()->lt($field, $this->getDateLiteralExpr($value[1], true))
                    )
                );
                break;

            case 'NOT BETWEEN':
                $this->qb->andWhere(
                    $this->qb->expr()->orX(
                        $this->qb->expr()->lt($field, $this->getDateLiteralExpr($value[0])),
                        $this->qb->expr()->gt($field, $this->getDateLiteralExpr($value[1], true))
                    )
                );
                break;

            case '>':
                $this->qb->andWhere($this->qb->expr()->gt($field, $this->getDateLiteralExpr($value, true)));
                break;

            case '<':
                $this->qb->andWhere($this->qb->expr()->lt($field, $this->getDateLiteralExpr($value)));
                break;

            case '=':
                $this->qb->andWhere(
                    $this->qb->expr()->andX(
                        $this->qb->expr()->gt($field, $this->getDateLiteralExpr($value)),
                        $this->qb->expr()->lt($field, $this->getDateLiteralExpr($value, true))
                    )
                );
                break;

            case 'EMPTY':
                $this->qb->andWhere($this->qb->expr()->isNull($field));
                break;
        }

        return $this;
    }

    /**
     * Get the literal expression of the date
     *
     * @param string  $data
     * @param boolean $endOfDay
     *
     * @return Literal
     */
    protected function getDateLiteralExpr($data, $endOfDay = false)
    {
        return $this->qb->expr()->literal($this->getDateValue($data, $endOfDay));
    }

    /**
     * Get the date formatted from data
     *
     * @param \DateTime|string $data
     * @param boolean          $endOfDay
     *
     * @return string
     */
    protected function getDateValue($data, $endOfDay = false)
    {
        if ($data instanceof \DateTime && true === $endOfDay) {
            $data->setTime(23, 59, 59);
        } elseif (!$data instanceof \DateTime && true === $endOfDay) {
            $data = sprintf('%s 23:59:59', $data);
        }

        return $data instanceof \DateTime ? $data->format('Y-m-d H:i:s') : $data;
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
     * @param AbstractAttribute $attribute the attribute
     * @param string            $joinAlias the value join alias
     *
     * @throws ProductQueryException
     *
     * @return string
     */
    protected function prepareAttributeJoinCondition(AbstractAttribute $attribute, $joinAlias)
    {
        $joinHelper = new ValueJoin($this->qb, $this->context);

        return $joinHelper->prepareCondition($attribute, $joinAlias);
    }
}
