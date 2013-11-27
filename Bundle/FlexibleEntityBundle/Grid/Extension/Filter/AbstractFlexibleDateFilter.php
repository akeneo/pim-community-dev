<?php

namespace Oro\Bundle\FlexibleEntityBundle\Grid\Extension\Filter;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\FilterBundle\Filter\Orm\AbstractDateFilter;
use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;

abstract class AbstractFlexibleDateFilter extends AbstractDateFilter
{
    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $qb, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return false;
        }

        /** @var $dateStartValue \DateTime */
        $dateStartValue = $data['date_start'];
        /** @var $dateEndValue \DateTime */
        $dateEndValue = $data['date_end'];
        $type         = $data['type'];

        $this->applyFlexibleDependingOnType(
            $type,
            $qb,
            $dateStartValue,
            $dateEndValue,
            $this->get(FilterUtility::DATA_NAME_KEY)
        );

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFlexibleDependingOnType($type, $qb, $dateStartValue, $dateEndValue, $fieldName)
    {
        switch ($type) {
            case DateRangeFilterType::TYPE_MORE_THAN:
                $this->applyFlexibleFilterLessMore($qb, $dateStartValue, $fieldName, false);
                break;
            case DateRangeFilterType::TYPE_LESS_THAN:
                $this->applyFlexibleFilterLessMore($qb, $dateEndValue, $fieldName, true);
                break;
            case DateRangeFilterType::TYPE_NOT_BETWEEN:
                $this->applyFlexibleFilterNotBetween($qb, $dateStartValue, $dateEndValue, $fieldName);
                break;
            default:
            case DateRangeFilterType::TYPE_BETWEEN:
                $this->applyFlexibleFilterBetween($qb, $dateStartValue, $dateEndValue, $fieldName);
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFlexibleFilterBetween($qb, $dateStartValue, $dateEndValue, $fieldName)
    {
        if ($dateStartValue) {
            $this->util->applyFlexibleFilter($qb, $this->getFEN(), $fieldName, $dateStartValue, '>=');
        }
        if ($dateEndValue) {
            $this->util->applyFlexibleFilter($qb, $this->getFEN(), $fieldName, $dateEndValue, '<=');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFlexibleFilterLessMore($qb, $dateValue, $fieldName, $isLess)
    {
        $this->util->applyFlexibleFilter($qb, $this->getFEN(), $fieldName, $dateValue, $isLess ? '<' : '>');
    }

    /**
     * {@inheritdoc}
     */
    protected function applyFlexibleFilterNotBetween($qb, $dateStartValue, $dateEndValue, $fieldName)
    {
        $values    = array();
        $operators = array();

        if ($dateStartValue) {
            $values['from']    = $dateStartValue;
            $operators['from'] = '<';
        }

        if ($dateEndValue) {
            $values['to']    = $dateEndValue;
            $operators['to'] = '>';
        }

        if ($values && $operators) {
            $this->util->applyFlexibleFilter($qb, $this->getFEN(), $fieldName, $values, $operators);
        }
    }

    /**
     * Returns flexible entity name
     *
     * @return string
     */
    protected function getFEN()
    {
        return $this->get(FilterUtility::FEN_KEY);
    }
}
