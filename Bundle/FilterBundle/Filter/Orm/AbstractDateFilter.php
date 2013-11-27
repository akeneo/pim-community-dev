<?php

namespace Oro\Bundle\FilterBundle\Filter\Orm;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;

abstract class AbstractDateFilter extends AbstractFilter
{
    /**
     * DateTime object as string format
     */
    const DATETIME_FORMAT = 'Y-m-d';

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

        $startDateParameterName = $this->generateQueryParameterName();
        $endDateParameterName   = $this->generateQueryParameterName();

        $this->applyDependingOnType(
            $type,
            $qb,
            $dateStartValue,
            $dateEndValue,
            $startDateParameterName,
            $endDateParameterName,
            $this->get(FilterUtility::DATA_NAME_KEY)
        );

        /** @var $queryBuilder QueryBuilder */
        if ($dateStartValue) {
            $qb->setParameter($startDateParameterName, $dateStartValue);
        }
        if ($dateEndValue) {
            $qb->setParameter($endDateParameterName, $dateEndValue);
        }

        return true;
    }

    /**
     * @param mixed $data
     *
     * @return array|bool
     */
    public function parseData($data)
    {
        if (!$this->isValidData($data)) {
            return false;
        }

        if (isset($data['value']['start'])) {
            /** @var \DateTime $startDate */
            $startDate = $data['value']['start'];
            $startDate->setTimezone(new \DateTimeZone('UTC'));
            $data['value']['start'] = $startDate->format(static::DATETIME_FORMAT);
        } else {
            $data['value']['start'] = null;
        }

        if (isset($data['value']['end'])) {
            /** @var \DateTime $endDate */
            $endDate = $data['value']['end'];
            $endDate->setTimezone(new \DateTimeZone('UTC'));
            $data['value']['end'] = $endDate->format(static::DATETIME_FORMAT);
        } else {
            $data['value']['end'] = null;
        }

        if (!isset($data['type'])) {
            $data['type'] = null;
        }

        if (!in_array(
            $data['type'],
            array(
                DateRangeFilterType::TYPE_BETWEEN,
                DateRangeFilterType::TYPE_NOT_BETWEEN,
                DateRangeFilterType::TYPE_MORE_THAN,
                DateRangeFilterType::TYPE_LESS_THAN
            )
        )
        ) {
            $data['type'] = DateRangeFilterType::TYPE_BETWEEN;
        }

        if ($data['type'] == DateRangeFilterType::TYPE_MORE_THAN) {
            $data['value']['end'] = null;
        } elseif ($data['type'] == DateRangeFilterType::TYPE_LESS_THAN) {
            $data['value']['start'] = null;
        }

        return array(
            'date_start' => $data['value']['start'],
            'date_end'   => $data['value']['end'],
            'type'       => $data['type']
        );
    }

    /**
     * @param $data
     *
     * @return bool
     */
    protected function isValidData($data)
    {
        if (!is_array($data) || !array_key_exists('value', $data) || !is_array($data['value'])) {
            return false;
        }

        if (!isset($data['value']['start']) && !isset($data['value']['end'])) {
            return false;
        }

        // check start date
        if (isset($data['value']['start']) && !$data['value']['start'] instanceof \DateTime) {
            return false;
        }

        // check end date
        if (isset($data['value']['end']) && !$data['value']['end'] instanceof \DateTime) {
            return false;
        }

        return true;
    }

    /**
     * Apply expression using "between" filtering
     *
     * @param QueryBuilder $queryBuilder
     * @param string       $dateStartValue
     * @param string       $dateEndValue
     * @param string       $startDateParameterName
     * @param string       $endDateParameterName
     * @param string       $fieldName ,
     */
    protected function applyFilterBetween(
        $queryBuilder,
        $dateStartValue,
        $dateEndValue,
        $startDateParameterName,
        $endDateParameterName,
        $fieldName
    ) {
        if ($dateStartValue) {
            $this->applyFilterToClause(
                $queryBuilder,
                $this->createComparisonExpression($fieldName, '>=', $startDateParameterName)
            );
        }

        if ($dateEndValue) {
            $this->applyFilterToClause(
                $queryBuilder,
                $this->createComparisonExpression($fieldName, '<=', $endDateParameterName)
            );
        }
    }

    /**
     * Apply expression using one condition (less or more)
     *
     * @param QueryBuilder        $queryBuilder
     * @param                     $dateValue
     * @param                     $dateParameterName
     * @param string              $fieldName
     * @param bool                $isLess less/more mode, true if 'less than', false if 'more than'
     */
    protected function applyFilterLessMore(
        $queryBuilder,
        $dateValue,
        $dateParameterName,
        $fieldName,
        $isLess
    ) {
        if ($dateValue) {
            $this->applyFilterToClause(
                $queryBuilder,
                $this->createComparisonExpression($fieldName, $isLess ? '<' : '>', $dateParameterName)
            );
        }
    }

    /**
     * Apply expression using "not between" filtering
     *
     * @param QueryBuilder $queryBuilder
     * @param string       $dateStartValue
     * @param string       $dateEndValue
     * @param string       $startDateParameterName
     * @param string       $endDateParameterName
     * @param string       $fieldName
     */
    protected function applyFilterNotBetween(
        $queryBuilder,
        $dateStartValue,
        $dateEndValue,
        $startDateParameterName,
        $endDateParameterName,
        $fieldName
    ) {
        $orExpression = $queryBuilder->expr()->orX();

        if ($dateStartValue) {
            $orExpression->add($this->createComparisonExpression($fieldName, '<', $startDateParameterName));
        }

        if ($dateEndValue) {
            $orExpression->add($this->createComparisonExpression($fieldName, '>', $endDateParameterName));
        }

        $this->applyFilterToClause($queryBuilder, $orExpression);
    }

    /**
     * Applies filter depending on it's type
     *
     * @param int                 $type
     * @param QueryBuilder        $queryBuilder
     * @param string              $dateStartValue
     * @param string              $dateEndValue
     * @param string              $startDateParameterName
     * @param string              $endDateParameterName
     * @param                     $fieldName
     *
     */
    protected function applyDependingOnType(
        $type,
        $queryBuilder,
        $dateStartValue,
        $dateEndValue,
        $startDateParameterName,
        $endDateParameterName,
        $fieldName
    ) {
        switch ($type) {
            case DateRangeFilterType::TYPE_MORE_THAN:
                $this->applyFilterLessMore(
                    $queryBuilder,
                    $dateStartValue,
                    $startDateParameterName,
                    $fieldName,
                    false
                );
                break;
            case DateRangeFilterType::TYPE_LESS_THAN:
                $this->applyFilterLessMore(
                    $queryBuilder,
                    $dateEndValue,
                    $endDateParameterName,
                    $fieldName,
                    true
                );
                break;
            case DateRangeFilterType::TYPE_NOT_BETWEEN:
                $this->applyFilterNotBetween(
                    $queryBuilder,
                    $dateStartValue,
                    $dateEndValue,
                    $startDateParameterName,
                    $endDateParameterName,
                    $fieldName
                );
                break;
            default:
            case DateRangeFilterType::TYPE_BETWEEN:
                $this->applyFilterBetween(
                    $queryBuilder,
                    $dateStartValue,
                    $dateEndValue,
                    $startDateParameterName,
                    $endDateParameterName,
                    $fieldName
                );
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        $formView = $this->getForm()->createView();

        $metadata                          = parent::getMetadata();
        $metadata['typeValues']            = $formView->vars['type_values'];
        $metadata['externalWidgetOptions'] = $formView->vars['widget_options'];

        return $metadata;
    }
}
